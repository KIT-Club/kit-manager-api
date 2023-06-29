<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * @OA\Get(
     *      path="/roles",
     *      tags={"roles"},
     *      summary="index",
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",type="array",
     *                  @OA\Items(ref="#/components/schemas/Role")
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function index()
    {
        $roles = Role::paginate();
        return (new RoleResource($roles))->response();
    }

    /**
     * @OA\Post(
     *      path="/roles",
     *      tags={"roles"},
     *      summary="store",
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/StoreRoleRequest"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   ref="#/components/schemas/Role",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function store(StoreRoleRequest $request, $permissions)
    {
        $data = $request->all();
        $role = Role::create($data);
        $role->users()->sync($request->input('user_ids'));
        return (new RoleResource($role))->response()->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/roles/{id}",
     *      tags={"roles"},
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
     *                   ref="#/components/schemas/Role",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function show(Role $role)
    {
        return (new RoleResource($role->load('users')))->response();
    }

    /**
     * @OA\Put(
     *      path="/roles/{id}",
     *      tags={"roles"},
     *      summary="update",
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/StoreRoleRequest"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   ref="#/components/schemas/Role",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->all();
        $role->update($data);
        $role->users()->sync($request->input('user_ids'));
        return (new RoleResource($role))->response()->setStatusCode(JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/roles/{id}",
     *      tags={"roles"},
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
    public function destroy(Role $role)
    {
        $role->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
