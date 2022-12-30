<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;
use \Illuminate\Http\Response;

/**
 * @OA\Paths(
 *      path="/events",
 * )
 */
class EventController extends Controller
{
    /**
     * @OA\Get(
     *      path="/events",
     *      tags={"Events"},
     *      summary="index",
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/EventResource")
     *       ),
     *     )
     */
    public function index()
    {
        $events = Event::with('users')->paginate();
        return (new EventResource($events))->response();
    }

    /**
     * @OA\Post(
     *      path="/events",
     *      tags={"Events"},
     *      summary="store",
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/EventResource")
     *       ),
     *     )
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
     *      tags={"Events"},
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
     *          @OA\JsonContent(ref="#/components/schemas/EventResource")
     *       ),
     *     )
     */
    public function show(Event $event)
    {
        return (new EventResource($event->load('users')))->response();
    }

    /**
     * @OA\Put(
     *      path="/events/{id}",
     *      tags={"Events"},
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
     *          @OA\JsonContent(ref="#/components/schemas/EventResource")
     *       ),
     *     )
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
     *      tags={"Events"},
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
    public function destroy(Event $event)
    {
        $event->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
