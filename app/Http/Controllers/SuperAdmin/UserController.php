<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Model\UserType;
use App\User;
class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function UserTypes()
    {
        $UserTypes = UserType::all();
        return view('admin.user.UserTypes',compact('UserTypes'));
    }
    public function newUserTypes()
    {
        return view('admin.user.NewUserTypes');
    }
    public function newUserTypesPost(Request $request)
    {
        $data = $request->all();
        $UserType  = UserType::where('name',$data['name'])->first();
        if($UserType){
            $request->session()->flash('error', ' Same type of user already exist!');
            return redirect()->back();
        }
        $ut = new UserType;
        $ut->name  = $data['name'];
        $ut->save();
        $request->session()->flash('message', ' User Type created successfully!');
        return redirect(route("SuperAdmin.UserTypes"));
    }
    public function Users()
    {
        $Users = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->get();
        return view('admin.user.Users',compact('Users'));
    }
    public function newUser(){
        $UserTypes = UserType::all();
        return view('admin.user.User',compact('UserTypes'));
    }
}
