<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = DB::transaction(function () use ($request) {
            $items = collect($request->items);

            $productIds = $items->pluck('product_id');
            $products   = Product::active()->whereIn('id', $productIds)->get()->keyBy('id');

            // Ensure all products are active and exist
            foreach ($items as $item) {
                if (! $products->has($item['product_id'])) {
                    abort(422, "Product ID {$item['product_id']} is not available.");
                }
            }

            $orderItems = $items->map(function ($item) use ($products) {
                $product  = $products[$item['product_id']];
                $subtotal = $product->price * $item['qty'];

                return [
                    'product_id' => $product->id,
                    'qty'        => $item['qty'],
                    'price'      => $product->price,
                    'subtotal'   => $subtotal,
                ];
            });

            $totalPrice = $orderItems->sum('subtotal');

            $order = Order::create([
                'customer_name'  => $request->customer_name,
                'customer_email' => $request->customer_email,
                'status'         => 'pending',
                'total_price'    => $totalPrice,
            ]);

            $order->items()->createMany($orderItems->toArray());

            return $order->load('items.product');
        });

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function index(): AnonymousResourceCollection
    {
        return OrderResource::collection(Order::with('items.product')->get());
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load('items.product'));
    }
}
