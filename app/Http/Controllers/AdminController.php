<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductUpdateRequest;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendPriceChangeNotification;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(protected ProductService $productService){
        //
    }

    public function loginPage(): View
    {
        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::attempt($request->except('_token'))) {
            return redirect()->route('admin.products');
        }

        return redirect()->back()->with('error', 'Invalid login credentials');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function products(): View
    {
        $products = $this->productService->getAllProducts();
        return view('admin.products', compact('products'));
    }

    public function editProduct($id): View
    {
        $product = $this->productService->findProductById($id);
        return view('admin.edit_product', compact('product'));
    }

    public function updateProduct(ProductUpdateRequest $request, $id)
    {
        $product = $this->productService->findProductById($id);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        // Store the old price before updating
        $oldPrice = $product->price;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $product->image = 'uploads/' . $filename;
        }

        $this->productService->updateProduct($id, $request->validated());

        // Check if price has changed
        if ($oldPrice != $product->price) {
            dispatch(new SendPriceChangeNotification(
                $product,
                $oldPrice,
                $request->price
            ));
        }

        return redirect()->route('admin.products')->with('success', 'Product updated successfully');
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully');
    }

    public function addProductForm()
    {
        return view('admin.add_product');
    }

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $product->image = 'uploads/' . $filename;
        } else {
            $product->image = 'product-placeholder.jpg';
        }

        $product->save();

        return redirect()->route('admin.products')->with('success', 'Product added successfully');
    }
}
