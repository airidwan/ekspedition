<?php

namespace App\Modules\Accountreceivables\Providers;

use App;
use Config;
use Lang;
use View;
use Caffeinated\Modules\Support\ServiceProvider;

class AccountreceivablesServiceProvider extends ServiceProvider
{
    /**
     * Register the Accountreceivables module service provider.
     *
     * This service provider is a convenient place to register your modules
     * services in the IoC container. If you wish, you may make additional
     * methods or service providers to keep the code more focused and granular.
     *
     * @return void
     */
    public function register()
    {
        App::register('App\Modules\Accountreceivables\Providers\RouteServiceProvider');

        Lang::addNamespace('accountreceivables', realpath(__DIR__.'/../Resources/Lang'));
        View::addNamespace('accountreceivables', base_path('resources/views/vendor/accountreceivables'));
        View::addNamespace('accountreceivables', realpath(__DIR__.'/../Resources/Views'));
    }

    /**
     * Bootstrap the application events.
     *
     * Here you may register any additional middleware provided with your
     * module with the following addMiddleware() method. You may pass in
     * either an array or a string.
     *
     * @return void
     */
    public function boot()
    {
        // $this->addMiddleware('');
    }

    /**
     * Additional Compiled Module Classes
     *
     * Here you may specify additional classes to include in the compiled file
     * generated by the `artisan optimize` command. These should be classes
     * that are included on basically every request into the application.
     *
     * @return array
     */
    public static function compiles()
    {
        $basePath = realpath(__DIR__.'/../');

        return [
            // $basePath.'/example.php',
        ];
    }
}
