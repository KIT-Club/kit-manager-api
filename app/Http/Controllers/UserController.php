<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utils\CrawlUtil;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Exception;

class UserController extends Controller
{
    protected $crawlUtil;

    public function __construct(CrawlUtil $crawlUtil)
    {
        $this->crawlUtil = $crawlUtil;
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
        $result = Http::get('https://actvn-schedule.cors-ngosangns.workers.dev/login');
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
        $result = Http::asForm()->post('https://actvn-schedule.cors-ngosangns.workers.dev/login', [
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
            $result = Http::withHeaders([
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
            $result = Http::withHeaders([
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
            $user = User::create([
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
    public function index()
    {
        $users = User::paginate();
        return (new UserResource($users))->response();
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
    public function show(User $user)
    {
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
}
