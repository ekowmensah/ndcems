<?php

namespace App\Http\Controllers\National;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Model\UserType;
use App\User;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;
use App\Model\Country;
use App\Model\Region;
use App\Model\Constituency;
use App\Model\ElectoralArea;
use App\Model\PollingStation;
use DataTables;
use App\Model\ElectionType;

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
        $UserTypes = UserType::all();
        return view('admin.user.NewUserTypes',compact('UserTypes'));
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
        $ut->parent  = $data['parent'];

        $ut->save();
        $request->session()->flash('message', ' User Type created successfully!');
        return redirect(route("SuperAdmin.UserTypes"));
    }
    public function Users()
    {
        /* $Users = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->get();
        $UserTypes = UserType::orderBy('id','desc')->get(); */

        $UserTypes = UserType::latest()->first();
        $UserTypes = UserType::where('id','!=',$UserTypes->id)->get();
        /* $Users = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->where('user_type.id', $UserType->id)
            ->get(); */
        //$UserTypes = UserType::orderBy('id','desc')->get();
        //return view('admin.user.Users',compact('Users','UserTypes'));
        $electionTypes = Region::all();

        $Users = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->get();

        return view('national.agent.Users',compact('electionTypes','Users','UserTypes'));
    }
    public function newUser($type_id){
        $Type = UserType::find($type_id);
        $under = UserType::where('id',$Type->parent);

        $countries = Country::all();
        $UserTypes = UserType::where('id','<=',$Type->id)->get();
        $NewUserTypes = [];
        foreach ($UserTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewUserTypes[] = array_merge($value,array_merge($value,$data));
        }
       // $belongTo = UserType::where('id',$Type->parent)->get();
        $belongTo = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->where('user_type.id',$Type->parent)
            ->get();
        //dd($belongTo->toArray());

        return view('national.agent.User',compact('countries','UserTypes','Type','belongTo','under','NewUserTypes'));
    }
    public function UserTypesEdit($id){
        $UserTypes = UserType::where('id',$id)->first();
        $AllUserTypes = UserType::all();
        return view('admin.user.EditUserTypes',compact('UserTypes','AllUserTypes'));
    }
    public function UserTypesEditPost(Request $request){
        $data = $request->all();
        $UserTypes = UserType::where('name',$data['oldName'])->where('id',$data['id'])->first();
        if(!$UserTypes){
            $request->session()->flash('error', ' Somthing is wrong!');
        }else{
            $UserTypes->name = $data['name'];
            $UserTypes->parent  = $data['parent'];
            $UserTypes->save();
            $request->session()->flash('message', ' Updated Successfully!');
        }


        return redirect()->back();
    }

    public function UserTypesDelete($id,Request $request){
        try{
            $UserTypes = UserType::findOrFail($id);
            $UserTypes->delete();
        }catch(\Exception $e){
            $request->session()->flash('error', 'Type already in use.!');
            return redirect()->back();

        }

            $request->session()->flash('message', ' Deleted Successfully!');
            return redirect()->back();
    }
    public function VerifyUsername(Request $request){
            $data = $request->all();
       $username =  User::where('username',$data['username'])->first();
       if($username)
            return "faund";
        else
            return "not_found";
    }
    public function newUserPost(Request $request){
        $validation =  Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'min:10', 'max:10', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],

            'user_type_id' => ['required'],
            'phoneno' => ['required', 'string', 'min:7'],
            //'constituency' => ['required'],
            'gender' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()
                        ->withErrors($validation)
                        ->withInput();
        }else{
            $data = $request->all();
            $data['password'] = Hash::make($data['password']);
            $UserType = UserType::latest()->first();
            if($data['user_type_id'] == $UserType->id){
                $data['secret'] = $request->input('password');
            }


            User::create($data);
            $request->session()->flash('message', ' User Created Successfully!');
            return redirect(route('National.Users'));
        }


    }
    public function UsersEdit($id){
        $User = User::find($id);

        $Type = UserType::find($User->user_type_id);
        //user type config

        $countries = Country::all();
        $UserTypes = UserType::where('id','<=',$Type->id)->get();
        $NewUserTypes = [];
        foreach ($UserTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewUserTypes[] = array_merge($value,array_merge($value,$data));

          }

       // $belongTo = UserType::where('id',$Type->parent)->get();
        $belongTo = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->where('user_type.id',$Type->parent)
            ->get();

        $regions = Region::where('country_id',$User->country_id)->get();
        $constituency = Constituency::where('region_id',$User->region_id)->get();
        $electoralarea = ElectoralArea::where('constituency_id',$User->constituency_id)->get();
        $pollingstation = PollingStation::where('electoralarea_id',$User->electoralarea_id)->get();

        return view('admin.user.EditUser',compact('pollingstation','electoralarea','constituency','regions','countries','UserTypes','Type','belongTo','User','NewUserTypes'));
    }

    public function EditUserPost(Request $request){
        $validation =  Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'username' => ['required', 'string', 'min:10', 'max:10'],
            // /'password' => ['string', 'min:6'],

            'user_type_id' => ['required'],
            'phoneno' => ['required', 'string', 'min:7'],
            //'constituency' => ['required'],
            'gender' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()
                        ->withErrors($validation)
                        ->withInput();
        }else{
            $data = $request->all();
            $user = User::find($data['id']);
            $user->name =  $data['name'];
            $user->email =  $data['email'];
            $user->username =  $data['username'];
            $user->phoneno =  $data['phoneno'];
            $user->country_id =  $data['country_id'];
            $user->gender =  $data['gender'];

            if(isset($data['region_id']))
                $user->region_id =  $data['region_id'];
            if(isset($data['constituency_id']))
                $user->constituency_id =  $data['constituency_id'];
            if(isset($data['electoralarea_id']))
                 $user->electoralarea_id =  $data['electoralarea_id'];
            if(isset($data['polling_station_id']))
                $user->polling_station_id =  $data['polling_station_id'];

            if($data['password'])
            {
                $user->password =   Hash::make($data['password']);
                $UserType = UserType::latest()->first();
                if($data['user_type_id'] == $UserType->id){
                    $user->secret = $request->input('password');
                }
            }
            $user->save();



            $request->session()->flash('message', ' User Updated Successfully!');
            return redirect()->back();
        }

    }
    public function pollingAgent(){
        $UserType = UserType::latest()->first();
        /* $Users = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->where('user_type.id', $UserType->id)
            ->get(); */
        //$UserTypes = UserType::orderBy('id','desc')->get();
        //return view('admin.user.Users',compact('Users','UserTypes'));
        $electionTypes = Region::all();
        return view("admin.polling_agent.polling_agent",compact('electionTypes','UserType'));
    }
    public function pollingAgentAjax(Request $request){
        $UserType = UserType::latest()->first();
        $Users = User::select(
                'users.username',
                'users.secret',
                'users.created_at',
                'users.name as user_name',
                'users.id as user_id',
                'user_type.id as user_type_id',
                'user_type.name as user_type_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "PollingStation.name as PollingStation_name",
                "ElectoralArea.name as ElectoralArea_name"
            )
            ->where('user_type.id', $UserType->id)
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->join('region','region.id','=','users.region_id')
            ->join('constituency','constituency.id','=','users.constituency_id')
            ->join('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
            ->join('PollingStation','PollingStation.id','=','users.polling_station_id');

        //->leftJoin('PollingStation','PollingStation.electoralarea_id','=','ElectoralArea.id');
            if($request->input('electoralarea_id') != "all")
                $Users = $Users->where('users.electoralarea_id',$request->input('electoralarea_id'));
            if($request->input('constituency_id') != "all")
                $Users = $Users->where('users.constituency_id',$request->input('constituency_id'));
            if($request->input('region_id') != "all")
                $Users = $Users->where('users.region_id',$request->input('region_id'));
            if($request->input('polling_station_id') != "all")
                $Users = $Users->where('users.polling_station_id',$request->input('polling_station_id'));



        return DataTables::of($Users)->make(true);
    }
    public function UsersDelete($id,Request $request){
        $user = User::find($id);
        $user->delete();
        $request->session()->flash('message', ' Agent Deleted Successfully!');
        return redirect()->back();
    }



    public function managementAgentAjax(Request $request){
        $UserType = UserType::latest()->first();
        $Users = User::select(
                'users.created_at',
                'users.name as user_name',
                'users.id as user_id',
                'user_type.id as user_type_id',
                'user_type.name as user_type_name',
                'users.username',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "PollingStation.name as PollingStation_name",
                "ElectoralArea.name as ElectoralArea_name"
            )
            ->where('user_type.id','!=', $UserType->id)
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->leftJoin('region','region.id','=','users.region_id')
            ->leftJoin('constituency','constituency.id','=','users.constituency_id')
            ->leftJoin('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
            ->leftJoin('PollingStation','PollingStation.id','=','users.polling_station_id');

        //->leftJoin('PollingStation','PollingStation.electoralarea_id','=','ElectoralArea.id');
            if($request->input('electoralarea_id') != "all")
                $Users = $Users->where('users.electoralarea_id',$request->input('electoralarea_id'));
            if($request->input('constituency_id') != "all")
                $Users = $Users->where('users.constituency_id',$request->input('constituency_id'));
            if($request->input('region_id') != "all")
                $Users = $Users->where('users.region_id',$request->input('region_id'));
            if($request->input('polling_station_id') != "all")
                $Users = $Users->where('users.polling_station_id',$request->input('polling_station_id'));



        return DataTables::of($Users)->make(true);
    }
}
