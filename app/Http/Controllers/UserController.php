<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use App\Utils\CrawlUtil;
use Exception;

class UserController extends Controller
{
    protected $crawlUtil;

    public function __construct(CrawlUtil $crawlUtil)
    {
        $this->crawlUtil = $crawlUtil;
    }

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
