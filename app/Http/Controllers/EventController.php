<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EventController extends Controller
{
    /**
     * @OA\Get(
     *      path="/events",
     *      tags={"events"},
     *      summary="index",
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/Event"),
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function index()
    {
        $events = Event::with('users')->paginate();
        return (new EventResource($events))->response();
    }

    /**
     * @OA\Post(
     *      path="/events",
     *      tags={"events"},
     *      summary="store",
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/StoreEventRequest"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   ref="#/components/schemas/Event",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->all());
        $event->users()->sync($request->input('user_ids'));
        return (new EventResource($event->load('users')))->response()->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/events/{id}",
     *      tags={"events"},
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
     *                   ref="#/components/schemas/Event",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function show(Event $event)
    {
        return (new EventResource($event->load('users')))->response();
    }

    /**
     * @OA\Put(
     *      path="/events/{id}",
     *      tags={"events"},
     *      summary="update",
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/StoreEventRequest"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   ref="#/components/schemas/Event",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $event->update($request->all());
        $event->users()->sync($request->input('user_ids'));
        return (new EventResource($event->load('users')))->response()->setStatusCode(JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/events/{id}",
     *      tags={"events"},
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
     *     ),
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
