<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Illuminate\Support\Facades\Log;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Messaging::class, function ($app) {
            $credentialsPath = env('FIREBASE_CREDENTIALS');
            if (! $credentialsPath || ! file_exists(base_path($credentialsPath))) {
                Log::error('Firebase credentials file not found or path is incorrect: ' . $credentialsPath);

            }
            $factory = (new Factory())->withServiceAccount(base_path($credentialsPath));
            return $factory->createMessaging();
        });
    }
    public function boot(): void
    {

    }
}
