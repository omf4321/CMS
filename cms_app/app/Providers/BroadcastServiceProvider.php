<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
        Broadcast::routes(['middleware' => ['web', 'auth:client,web,admin']]);

        // Broadcast::routes(['middleware' => ['web', 'auth:web']]);

        require base_path('routes/channels.php');
    }
}
