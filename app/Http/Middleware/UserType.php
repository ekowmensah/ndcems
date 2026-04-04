<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Model\UserType as UserTypeModel;

class UserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $UserTypes = UserTypeModel::where('id','<=',Auth::user()->user_type_id)->get();
        $NewUserTypes = [];
        foreach ($UserTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewUserTypes[] = array_merge($value,array_merge($value,$data));
        }
        /* if(isset($NewUserTypes) && $NewUserTypes && end($NewUserTypes)['index'] == 0 ){
            dd("National Director");
        }
        if(isset($NewUserTypes) && $NewUserTypes && end($NewUserTypes)['index'] == 1 ){
            dd("Regional Director");
        }
        if(isset($NewUserTypes) && $NewUserTypes && end($NewUserTypes)['index'] == 2 ){
            dd("Constituency Election Directors ");
        } */
        if(isset($NewUserTypes) && $NewUserTypes && end($NewUserTypes)['index'] == 0 ){
            return redirect(route("National.dashboard"));//National Director
        }
        if(isset($NewUserTypes) && end($NewUserTypes)['index'] == 1 )
        {
             return redirect(route("Region.dashboard"));//region director
        }
        if(isset($NewUserTypes) && ( end($NewUserTypes)['index'] == 2 ))
        {
            return redirect(route("Director.home"));//constituency director
        }
        if(isset($NewUserTypes) && $NewUserTypes && end($NewUserTypes)['index'] == 3 ){

            return redirect(route("Agent.election"));
        }
        return $next($request);
    }
}
