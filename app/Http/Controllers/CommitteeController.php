<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommitteeRequest;
use App\Http\Requests\UpdateCommitteeRequest;
use App\Http\Resources\CommitteeResource;
use App\Models\Committee;
use \Illuminate\Http\JsonResponse;
use \Illuminate\Http\Response;

class CommitteeController extends Controller
{
    /**
     * @OA\Get(
     *      path="/committees",
     *      tags={"committees"},
     *      summary="index",
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/CommitteeResource")
     *       ),
     *     )
     */
    public function index()
    {
        $committees = Committee::paginate();
        return (new CommitteeResource($committees))->response();
    }

    /**
     * @OA\Post(
     *      path="/committees",
     *      tags={"committees"},
     *      summary="store",
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/CommitteeResource")
     *       ),
     *     )
     */
    public function store(StoreCommitteeRequest $request)
    {
        $event = Committee::create($request->all());
        return (new CommitteeResource($event))->response()->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/committees/{id}",
     *      tags={"committees"},
     *      summary="show",
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/CommitteeResource")
     *       ),
     *     )
     */
    public function show(Committee $committee)
    {
        return (new CommitteeResource($committee))->response();
    }

    /**
     * @OA\Put(
     *      path="/committees/{id}",
     *      tags={"committees"},
     *      summary="update",
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/CommitteeResource")
     *       ),
     *     )
     */
    public function update(UpdateCommitteeRequest $request, Committee $committee)
    {
        $committee->update($request->all());
        return (new CommitteeResource($committee))->response()->setStatusCode(JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/committees/{id}",
     *      tags={"committees"},
     *      summary="destroy",
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="response",
     *       ),
     *     )
     */
    public function destroy(Committee $committee)
    {
        $committee->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
