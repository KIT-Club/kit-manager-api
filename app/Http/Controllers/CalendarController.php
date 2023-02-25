<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;
use Exception;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use DateTime;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\Cookie;

class CalendarController extends Controller
{
    private $loginUrl = 'http://qldt.actvn.edu.vn/CMCSoft.IU.Web.Info/Login.aspx';
    private $calendarUrl = 'http://qldt.actvn.edu.vn/CMCSoft.IU.Web.Info/Reports/Form/StudentTimeTable.aspx';

    public function __construct()
    {
    }

    /**
     * @OA\Post(
     *      path="/calendar-excel",
     *      tags={"calendar"},
     *      description="Parse calendar from excel file which is exported with option 'Hiển thị theo ngày học'",
     *      summary="Parse calendar from excel file",
     *      @OA\RequestBody(
     *         required=true,
     *         description="Excel file",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     description="Excel file",
     *                     type="file",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 description="Response code",
     *                 type="integer",
     *                 example=200,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 description="Response message",
     *                 type="string",
     *                 example="OK",
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 description="Parsed data",
     *                 type="array",
     *                 @OA\Items(),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 description="Response code",
     *                 type="integer",
     *                 example=500,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 description="Error message",
     *                 type="string",
     *                 example="Error: ...",
     *             ),
     *         ),
     *     ),
     * ),
     */
    function excel(Request $request)
    {
        try {
            $file = $request->file('file');
            $parseData = $this->parseExcelFile($file->getRealPath());
            return response()->json([
                'code' => 200,
                'message' => 'OK',
                'data' => $parseData
            ])->header('Content-Type', 'application/json');
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error: ' . $e,
            ])->header('Content-Type', 'application/json');
        }
    }

    function validateWorkSheet($workSheet)
    {
        try {
            return $workSheet[0][0] == "BAN CƠ YẾU CHÍNH PHỦ"
                && $workSheet[1][0] == "HỌC VIỆN KỸ THUẬT MẬT MÃ"
                && !empty($workSheet[5][5])
                && !empty($workSheet[5][2]);
        } catch (Exception $e) {
            return false;
        }
    }

    function getStudentCode($workSheet)
    {
        return $workSheet[5][5];
    }

    function getStudentName($workSheet)
    {
        return $workSheet[5][2];
    }

    function dateStringToTimeStamp($dateString)
    {
        return DateTime::createFromFormat("d/m/Y", $dateString)->getTimestamp();
    }

    function mutiplyFindIndex($parseData, $arrayValue)
    {
        return array_map(function ($e) use ($parseData) {
            return array_search(strtolower($e), array_map('strtolower', reset($parseData)));
        }, $arrayValue);
    }

    function findAllDate($dayOfWeek, $timeStart, $timeEnd)
    {
        $timeStampStart = $this->dateStringToTimeStamp($timeStart);
        $timeStampEnd = $this->dateStringToTimeStamp($timeEnd);
        $timeLine = $timeStampStart;
        $allDate = array();
        $aDayToTimeStamp = 86400;
        while ($timeLine <= $timeStampEnd) {
            if ($this->findDayOfWeek($timeLine) == $dayOfWeek) {
                array_push($allDate, $timeLine);
                $timeLine += 7 * $aDayToTimeStamp;
            } else {
                $timeLine += 1 * $aDayToTimeStamp;
            }
        }
        return $allDate;
    }

    function findDayOfWeek($timeStamp)
    {
        return date('N', $timeStamp) + 1;
    }

    function timeStampToDateString($timeStamp)
    {
        return date('d/m/Y', $timeStamp);
    }

    function parseExcelFile($path)
    {
        $reader = IOFactory::createReader('Xls');
        $spreadsheet = $reader->load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $workSheetsFromFile = $worksheet->toArray();

        if (!$this->validateWorkSheet($workSheetsFromFile))
            throw new Exception("Không phải thời khóa biểu học viện mật mã");

        $studentCode = $this->getStudentCode($workSheetsFromFile);
        $studentName = $this->getStudentName($workSheetsFromFile);
        $parseData = array_filter($workSheetsFromFile, function ($e, $i) {
            return $e[0] && (is_numeric($e[0]) || strtolower($e[0]) == "thứ");
        }, ARRAY_FILTER_USE_BOTH);
        list($dateIndex, $subjectCodeIndex, $subjectNameIndex, $classNameIndex, $teacherIndex, $lessonIndex, $roomIndex, $timeIndex)
            = $this->mutiplyFindIndex($parseData, array("thứ", "mã học phần", "tên học phần", "lớp học phần", "cbgd", "tiết học", "phòng học", "thời gian học"));
        $schedule = array();
        array_filter(array_values($parseData), function ($e, $i) use ($dateIndex, $timeIndex, $lessonIndex, $subjectCodeIndex, $subjectNameIndex, $classNameIndex, $teacherIndex, $roomIndex, &$schedule) {
            if ($i < 1) {
                return;
            }
            list($timeStart, $timeEnd) = explode("-", $e[$timeIndex]);
            $dates = $this->findAllDate($e[$dateIndex], $timeStart, $timeEnd);
            foreach ($dates as $element) {
                $schedule[] = array(
                    "day" => $this->timeStampToDateString($element),
                    "subjectCode" => $e[$subjectCodeIndex],
                    "subjectName" => $e[$subjectNameIndex],
                    "className" => $e[$classNameIndex],
                    "teacher" => $e[$teacherIndex],
                    "lesson" => $e[$lessonIndex],
                    "room" => $e[$roomIndex]
                );
            }
        }, ARRAY_FILTER_USE_BOTH);
        usort($schedule, function ($a, $b) {
            return $this->dateStringToTimeStamp($a["day"]) - $this->dateStringToTimeStamp($b["day"]);
        });
        return array("studentCode" => $studentCode, "studentName" => $studentName, "scheduleData" => $schedule);
    }

    function parseSelector($crawler)
    {
        $selectors = [];

        $crawler->filter('select')->each(function ($node) use (&$selectors) {
            $name = $node->attr('name');
            $selected = $node->filter('option[selected]')->attr('value');
            $selectors[$name] = $selected;
        });

        return $selectors;
    }

    /**
     * @OA\Post(
     *     path="/calendar-login",
     *     summary="Get calendar by student account",
     *     description="Get calendar by student account",
     *     tags={"calendar"},
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="Username",
     *         required=true,
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Password",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=200,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="OK",
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing Item",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=400,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Missing Item",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Wrong Password",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=401,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Wrong Password",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error logging in",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=403,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Có lỗi xảy ra khi đăng nhập. Vui lòng đăng nhập lại!",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=500,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error",
     *             ),
     *         ),
     *     ),
     * ),
     */
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (!$username || !$password) {
            return response()->json([
                'code' => '400',
                'message' => 'Missing Item',
            ])->header('Content-Type', 'application/json');
        }

        $client = new Client();

        try {
            $crawler = $client->request('GET', $this->loginUrl);

            $formData = [
                'txtUserName' => strtoupper($username),
                'txtPassword' => md5($password),
                '__VIEWSTATE' => $crawler->filter('#__VIEWSTATE')->attr('value'),
                '__VIEWSTATEGENERATOR' => $crawler->filter('#__VIEWSTATEGENERATOR')->attr('value'),
                '__EVENTVALIDATION' => $crawler->filter('#__EVENTVALIDATION')->attr('value'),
                'btnSubmit' => 'Đăng nhập',
            ];

            $crawler = $client->request('POST', $this->loginUrl, $formData);
            $cookieJar = $client->getCookieJar();

            if (
                strpos($crawler->html(), 'Bạn đã nhập sai tên hoặc mật khẩu!') !== false
                || strpos($crawler->html(), 'Tên đăng nhập không đúng!') !== false
            ) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Wrong Password',
                ])->header('Content-Type', 'application/json');
            }

            $userFullName = $crawler->filter('#PageHeader1_lblUserFullName')->text();

            if (strtolower($userFullName) == 'khách') {
                return response()->json([
                    'code' => 403,
                    'message' => 'Có lỗi xảy ra khi đăng nhập. Vui lòng đăng nhập lại!',
                ])->header('Content-Type', 'application/json');
            }

            return response()->json([
                'code' => 200,
                'message' => 'OK',
                'data' => $this->listSchedule($cookieJar)
            ])->header('Content-Type', 'application/json');
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error: ' . $e,
            ])->header('Content-Type', 'application/json');
        }
    }

    /**
     * @OA\Post(
     *     path="/calendar-token",
     *     summary="Get calendar by SignIn token",
     *     description="Get calendar by SignIn token",
     *     tags={"calendar"},
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="SignIn token",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=200,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="OK",
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing Item",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=400,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Missing Item",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=500,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error",
     *             ),
     *         ),
     *     ),
     * ),
     */
    public function token(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json([
                'code' => '400',
                'message' => 'Missing Item',
            ])->header('Content-Type', 'application/json');
        }

        try {
            $cookieJar = new CookieJar();
            $cookieJar->set(new Cookie('SignIn', $token));

            return response()->json([
                'code' => 200,
                'message' => 'OK',
                'data' => $this->listSchedule($cookieJar)
            ])->header('Content-Type', 'application/json');
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error: ' . $e,
            ])->header('Content-Type', 'application/json');
        }
    }

    public function listSchedule($cookieJar, $drpSemester = null)
    {
        $client = new Client(null, null, $cookieJar);

        $response = $client->request('GET', $this->calendarUrl);
        $html = $response->html();

        $crawler = new Crawler($html);

        $selectorData = $this->parseSelector($crawler);

        $selectorData['drpTerm'] = 1;
        $selectorData['drpSemester'] = $drpSemester ?? $selectorData['drpSemester'];
        $selectorData['drpType'] = 'B';
        $selectorData['btnView'] = 'Xuất file Excel';

        $formData = array_merge([
            '__VIEWSTATE' => $crawler->filter('#__VIEWSTATE')->attr('value'),
            '__VIEWSTATEGENERATOR' => $crawler->filter('#__VIEWSTATEGENERATOR')->attr('value'),
            '__EVENTVALIDATION' => $crawler->filter('#__EVENTVALIDATION')->attr('value'),
            "hidTuitionFactorMode" => $crawler->filter('input[name="hidTuitionFactorMode"]')->attr('value'),
            "hidLoaiUuTienHeSoHocPhi" => $crawler->filter('input[name="hidLoaiUuTienHeSoHocPhi"]')->attr('value'),
            "hidStudentId" => $crawler->filter('input[name="hidStudentId"]')->attr('value'),
        ], $selectorData);

        $client->request('POST', $this->calendarUrl, $formData);

        $buffer = $client->getInternalResponse()->getContent();

        $file = tmpfile();
        fwrite($file, $buffer);
        $metaData = stream_get_meta_data($file);

        $scheduleData = $this->parseExcelFile($metaData['uri']);

        return $scheduleData;
    }
}
