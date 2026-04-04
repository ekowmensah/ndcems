<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Model\SiteSetting;
use Illuminate\Support\Facades\Schema;
use App\Model\UserType;
use App\Model\ElectionType;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        try{
            $siteSettings = SiteSetting::all();
            if($siteSettings && is_array($siteSettings->toArray()) && count($siteSettings->toArray())){
                $result = array();
                    foreach ($siteSettings->toArray() as $key => $value) {
                    if (is_array($value)) {
                        $result[$value['key']] = $value['value'];
                        }
                    }
            }else{
                $result = [
                    "name"=>"Ghana Electoral System",
                    "url"=> " ",
                    "from_email"=>"support@ghanaelectro.com",
                    "from_email_title"=>" ",
                    "logo" => "img/logo.png",
                    "SuperAdminUrlPrefix" => "admin",
                    //"logo2" => " "
                ];
            }
        }catch(\Exception $e){
            $result = [
                "name"=>"Ghana Electoral System",
                "url"=> " ",
                "from_email"=>"support@ghanaelectro.com",
                "from_email_title"=>" ",
                "logo" => "img/logo.png",
                "SuperAdminUrlPrefix" => "admin",
                //"logo2" => " "
            ];
        }
        $Type = UserType::all();
        $Type = $Type->toArray();

        view()->composer('*', function ($view) use($Type) {
            $view->with('UTypes', $Type);
        });

        view()->composer('*', function ($view) use($result) {
                $view->with('config', $result);
        });

        config(['config' => $result]);

        $ElectionType = ElectionType::all();
        $__electionTypes = $ElectionType->toArray();

        view()->composer('*', function ($view) use($__electionTypes) {
            $view->with('__electionTypes', $__electionTypes);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
