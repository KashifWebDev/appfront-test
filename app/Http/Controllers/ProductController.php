<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{

    public function __construct(
        protected ProductService $productService,
        protected ExchangeRateService $exchangeRateService){
        //
    }

    public function index(): View
    {
        $products = $this->productService->getAllProducts();
        $exchangeRate = $this->exchangeRateService->getUsdExchangeRate();

        return view('products.list', compact('products', 'exchangeRate'));
    }

    public function show(Request $request): View
    {
        $id = (int) $request->route('product_id');
        $product = $this->productService->findProductById($id);

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $exchangeRate = $this->exchangeRateService->getUsdExchangeRate();

        return view('products.show', compact('product', 'exchangeRate'));
    }

}
