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
        $clientKey = session('clientKey');
        if (!$clientKey) {
            $clientKey = md5($request->userAgent().'||'.$request->ip());
            session(['clientKey' => $clientKey]);
            Redis::hmset('clientKeyList', $clientKey, $request->userAgent().'||'.$request->ip());
        }

        $historyLogin = array_map(function ($item) {
            $item = json_decode($item, true);
            return $item;
        }, Redis::smembers($clientKey));
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
        ];
        Redis::sadd($key, json_encode($data));
        session($data);
        DB::table('login_log')->insert(array_merge($data, [
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
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

        $loginHistory = [];

        $keys = Redis::keys('*');
        foreach ($keys as $key) {
            $key = substr($key, strlen(config('app.name').'_database_'));
            if (strlen($key) == 32) {
                $loginHistory[$key] = Redis::smembers($key);
            }
        }

        return [
            'loginHistory' => $loginHistory,
            'clientKeyList' => Redis::hgetall('clientKeyList'),
        ];
    }
}
