<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    public function showConfirmPassword()
    {
        return view('admin.security.confirm-password');
    }

    public function confirmPassword(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);
        $administrator = Auth::guard('superAdmin')->user();

        if (!$administrator || !Hash::check($request->password, $administrator->password)) {
            return redirect()->back()->withErrors(['password' => 'The password is incorrect.']);
        }

        $request->session()->put('admin_password_confirmed_at', now()->getTimestamp());

        return redirect()->intended(route('SuperAdmin.home'))->with('message', 'Identity confirmed. Repeat the sensitive action to proceed.');
    }
}
