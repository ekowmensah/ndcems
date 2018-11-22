<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Model\SiteSetting;
use Illuminate\Support\Facades\Schema;

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
                    "from_email"=>"",
                    "from_email_title"=>" ",
                    "logo" => " ",
                    //"logo2" => " "
                ];
            }
        }catch(\Exception $e){
            $result = [
                "name"=>"Ghana Electoral System",
                "url"=> " ",
                "from_email"=>"",
                "from_email_title"=>" ",
                "logo" => " ",
                //"logo2" => " "
            ];
        }
        view()->composer('*', function ($view) use($result) {
                $view->with('config', $result);
        });
        config(['config' => $result]);
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
