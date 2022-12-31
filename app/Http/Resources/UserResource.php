<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema
 */
class UserResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="data",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/User"),
     * ),
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
