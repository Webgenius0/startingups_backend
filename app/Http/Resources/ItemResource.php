<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'condition' => $this->condition,
            'is_negotiable' => $this->is_negotiable,
            'location' => $this->location,
            'sub_location' => $this->sub_location,
            'brand' => $this->brand,
            'model' => $this->model,
            'view_count' => $this->view_count,
            'status' => $this->status,
            'is_boosted' => $this->is_boosted,
            'boosted_amount' => $this->boosted_amount,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'subcategory' => $this->whenLoaded('subcategory', function () {
                return [
                    'id' => $this->subcategory->id,
                    'name' => $this->subcategory->name,
                ];
            }),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_url' => url($image->image_url),
                    ];
                });
            }),
            'item_address' => $this->whenLoaded('item_address', function () {
                return [
                    'name' => $this->item_address->name,
                    'phone_number' => $this->item_address->phone_number,
                    'whats_app_number' => $this->item_address->whats_app_number,
                ];
            }),
        ];
    }
}
