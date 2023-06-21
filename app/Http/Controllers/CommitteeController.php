<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommitteeRequest;
use App\Http\Requests\UpdateCommitteeRequest;
use App\Http\Resources\CommitteeResource;
use App\Models\Committee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/Committee"),
     *              ),
     *          ),
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
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/StoreCommitteeRequest"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   ref="#/components/schemas/Committee",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function store(StoreCommitteeRequest $request)
    {
        $committee = Committee::create($request->all());
        $committee->users()->sync($request->input('user_ids'));
        return (new CommitteeResource($committee))->response()->setStatusCode(JsonResponse::HTTP_CREATED);
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
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   ref="#/components/schemas/Committee",
     *              ),
     *          ),
     *      ),
     * )
     */
    public function show(Committee $committee)
    {
        return (new CommitteeResource($committee->load('users')))->response();
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
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/StoreCommitteeRequest"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   ref="#/components/schemas/Committee",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function update(UpdateCommitteeRequest $request, Committee $committee)
    {
        $committee->update($request->all());
        $committee->users()->sync($request->input('user_ids'));
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
