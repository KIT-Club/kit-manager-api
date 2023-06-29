<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Utils\CrawlUtil;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class UserController extends Controller
{
    protected $crawlUtil;

    public function __construct(CrawlUtil $crawlUtil)
    {
        $this->crawlUtil = $crawlUtil;
    }

    private function paginate($query, $request)
    {
        return $query->paginate(request()->query('limit', 10));
    }

    private function withIncludes($query, $request)
    {
        $includes = explode(",", request()->get("includes"));
        $with = [];
        if (in_array("committees", $includes)) array_push($with, "committees");
        if (in_array("events", $includes)) array_push($with, "events");
        if (in_array("roles", $includes)) array_push($with, "roles");
        if (count($with)) {
            if ($query instanceof User)
                $query->load(...$with);
            else if ($query instanceof Builder)
                $query->with(...$with);
        }
        return $query;
    }

    /**
     * @OA\Post(
     *      path="/login",
     *      tags={"users"},
     *      summary="login",
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/LoginUserRequest"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string")
     *          ),
     *       ),
     * ),
     */
    public function login(\App\Http\Requests\LoginUserRequest $request)
    {
        // get form
        $result = Http::withOptions(['verify' => false])->get('https://actvn-schedule.cors-ngosangns.workers.dev/login');
        if (!$result->ok()) {
            throw new Exception("ACTVN connect error!");
        }
        $result = $result->body();

        // get form info
        $viewState = $this->crawlUtil->getFieldFromHtml($result, "__VIEWSTATE");
        $eventValidation = $this->crawlUtil->getFieldFromHtml($result, "__EVENTVALIDATION");
        $username = strtoupper($request->username);
        $password = md5($request->password);

        // login actvn
        $result = Http::withOptions(['verify' => false])->asForm()->post('https://actvn-schedule.cors-ngosangns.workers.dev/login', [
            "__VIEWSTATE" => $viewState,
            "__EVENTVALIDATION" => $eventValidation,
            "txtUserName" => $username,
            "txtPassword" => $password,
            "btnSubmit" => 'Đăng nhập',
        ]);
        if (!$result->ok()) {
            throw new Exception("ACTVN connect error!");
        }
        $result = $result->body();

        // get token
        if (!preg_match("/^(SignIn)=(.+?)$/", $result, $signInToken)) {
            throw new Exception("Authenticate failed!");
        }
        $signInToken = $signInToken[0];

        // get account
        $user = User::where('username', $request->username)->first();

        // register
        if (!$user) {
            // get user info
            $name = "";
            $class = "";
            $major = "";
            $birthday = "";
            $result = Http::withOptions(['verify' => false])->withHeaders([
                "x-cors-headers" => json_encode(["Cookie" => $signInToken])
            ])->get('https://actvn-schedule.cors-ngosangns.workers.dev/CMCSoft.IU.Web.info/StudentMark.aspx');
            $ok = $result->ok();
            $result = $result->body();
            if (!$ok) throw new Exception("ACTVN connect error!");
            if (preg_match("/lblStudentName\" class=\"form-control-lable-value\">(.+?)</", $result, $matched)) {
                $name = $matched[1];
            }
            if (preg_match("/lblAdminClass\" class=\"form-control-lable-value\">(.+?)</", $result, $matched)) {
                $class = $matched[1];
            }
            if (preg_match("/drpField\".+?\n.+?selected.+?>(.+?)</", $result, $matched)) {
                $major = html_entity_decode($matched[1]);
            }
            if ($name == "" || $class == "" || $major == "") {
                throw new Exception("Get info failed!");
            }
            $result = Http::withOptions(['verify' => false])->withHeaders([
                "x-cors-headers" => json_encode(["Cookie" => $signInToken])
            ])->get('https://actvn-schedule.cors-ngosangns.workers.dev/CMCSoft.IU.Web.Info/StudentProfileNew/HoSoSinhVien.aspx');
            $ok = $result->ok();
            $result = $result->body();
            if (!$ok) throw new Exception("ACTVN connect error!");
            if (preg_match("/value=\"(.+?)\" id=\"txtNgaySinh\"/", $result, $matched)) {
                $birthday = $matched[1];
            }
            if ($birthday == "") {
                throw new Exception("Get info failed!");
            }
            $birthday = implode("-", array_reverse(explode("/", $birthday)));

            // create user
            $user = User::withTrashed()->where('username', $username)->first();
            if ($user)
                $user->update([
                    "birthday" => $birthday,
                    "class" => $class,
                    "major" => $major,
                    "name" => $name,
                    "username" => $username,
                    "deleted_at" => null,
                ]);
            else $user = User::create([
                "birthday" => $birthday,
                "class" => $class,
                "major" => $major,
                "name" => $name,
                "username" => $username,
            ]);
        }

        return ['token' => $user->createToken('login')->plainTextToken];
    }

    /**
     * @OA\Post(
     *      path="/users",
     *      tags={"users"},
     *      summary="store",
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/StoreUserRequest"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/User"),
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function store(StoreUserRequest $request)
    {
        $username = strtoupper($request->input('username'));
        $user = User::withTrashed()->where('username', $username)->first();

        if ($user) $user->restore();
        else $user = User::create(["username" => $username]);

        $user->committees()->sync($request->input('committee_ids'));
        $user->roles()->sync($request->input('role_ids'));
        return (new UserResource($user))->response()->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/users",
     *      tags={"users"},
     *      summary="index",
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/User"),
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function index(Request $request)
    {
        $query = User::query();
        $query = $this->withIncludes($query, $request);

        return (new UserResource($this->paginate($query, $request)))->response();
    }

    /**
     * @OA\Get(
     *      path="/users/{id}",
     *      tags={"users"},
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
     *                   ref="#/components/schemas/User",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function show(Request $request, User $user)
    {
        $user = $this->withIncludes($user, $request);
        return (new UserResource($user))->response();
    }

    /**
     * @OA\Put(
     *      path="/users/{id}",
     *      tags={"users"},
     *      summary="update",
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   ref="#/components/schemas/User",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->all());
        return (new UserResource($user))->response()->setStatusCode(JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/users/{id}",
     *      tags={"users"},
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
     *      ),
     * ),
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
