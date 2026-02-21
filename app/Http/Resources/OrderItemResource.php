<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'product'    => $this->whenLoaded('product', fn () => $this->product->name),
            'qty'        => $this->qty,
            'price'      => $this->price,
            'subtotal'   => $this->subtotal,
        ];
    }
}
