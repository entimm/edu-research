<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    public function loginView(Request $request, $code = 0)
    {
        $skey = session('skey');
        if (!$skey) {
            $skey = md5($request->userAgent().'||'.$request->ip());
            session(['skey' => $skey]);
            Redis::hmset('skey_list', $skey, $request->userAgent().'||'.$request->ip());
        }

        $historyLogin = array_map(function ($item) {
            $item = json_decode($item, true);
            return $item;
        }, Redis::smembers($skey));
        if (88 == $code) {
            session(['admin' => 1]);
        }

        return view('login', ['history_login' => $historyLogin]);
    }

    public function login(LoginRequest $request)
    {
        $key = md5($request->userAgent().'||'.$request->ip());
        $data = [
            'school' => $request->school,
            'class' => $request->class,
            'name' => $request->name,
            'grade' => $request->grade,
            'age' => $request->age,
            'sex' => $request->sex,
            'student_no' => $request->student_no,
            'user_agent' => $request->userAgent(),
        ];
        Redis::srem($key, json_encode([
            'school' => $request->school,
            'class' => $request->class,
            'name' => $request->name,
            'grade' => $request->grade,
            'age' => $request->age,
            'student_no' => $request->student_no,
        ]));
        Redis::sadd($key, json_encode($data));
        session($data);
        DB::table('login_log')->insert($data);
    }

    public function quit(Request $request)
    {
        // $request->session()->flush();
        session([
            'school' => null,
            'class' => null,
            'name' => null,
            'grade' => null,
            'age' => null,
            'sex' => null,
            'student_no' => null,
        ]);

        return response()->redirectTo('/login');
    }

    public function all()
    {

        $loginList = [];

        $keys = Redis::keys('*');
        foreach ($keys as $key) {
            $key = substr($key, strlen(config('app.name').'_database_'));
            if (strlen($key) == 32) {
                $loginList[$key] = Redis::smembers($key);
            }
        }

        return [
            'loginList' => $loginList,
            'skey_list' => Redis::hgetall('skey_list'),
        ];
    }
}
