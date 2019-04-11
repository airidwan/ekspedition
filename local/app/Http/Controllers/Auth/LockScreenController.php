<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use App\Http\Requests;

class LockScreenController extends Controller
{
    public function index(){
    
    // only if user is logged in
        if(\Auth::check()){
            \Session::put('locked', true);
            return view('auth.lock-screen');
        }

        return redirect('/login');
    }

    public function post(Request $request)
    {
    // if user in not logged in 
        if(!\Auth::check()){
            return redirect('/login');
        }

        $password = $request->get('password');
        if(\Hash::check($password,\Auth::user()->password)){
            \Session::forget('locked');
            return redirect('/');
        }
        return view('auth.lock-screen', ['error' => "Pasword anda salah !!"]);
    }
    public function notUser(){
        \Session::forget('locked');
        return redirect('logout');
    }
}