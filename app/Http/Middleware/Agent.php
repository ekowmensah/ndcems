<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Model\UserType as UserTypeModel;
use App;

class Agent
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

        if(isset($NewUserTypes) && $NewUserTypes && end($NewUserTypes)['index'] != 3 ){
            App::abort(404);
        }
        return $next($request);
    }
}
