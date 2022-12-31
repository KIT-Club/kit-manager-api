<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema
 */
class CommitteeResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="data",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/Committee"),
     * ),
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
