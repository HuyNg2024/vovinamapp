<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
use Kreait\Firebase\ServiceAccount;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        // $this->app->singleton(Storage::class, function ($app) {
        //     $factory = (new Factory)->withServiceAccount(config('firebase.projects.app.credentials'));
        //     return $factory->createStorage();

        // });

        $this->app->singleton(Storage::class, function ($app) {
            $credentials = $this->getFirebaseCredentials();
            $factory = (new Factory)->withServiceAccount($credentials);
            return $factory->createStorage();
        });
    }

    // private function getFirebaseCredentials()
    // {
    //     $credentials = config('firebase.projects.app.credentials');
        
    //     if (is_string($credentials) && str_starts_with($credentials, '{')) {
    //         // Nếu FIREBASE_CREDENTIALS là một JSON string
    //         return json_decode($credentials, true);
    //     } elseif (is_string($credentials) && file_exists($credentials)) {
    //         // Nếu FIREBASE_CREDENTIALS là đường dẫn đến file
    //         return $credentials;
    //     } else {
    //         throw new \Exception('Invalid Firebase credentials configuration');
    //     }
    // }

    private function getFirebaseCredentials()
    {
        $credentials = env('FIREBASE_CREDENTIALS');
        
        if (empty($credentials)) {
            throw new \Exception('Firebase credentials not configured');
        }

        if (is_string($credentials) && file_exists($credentials)) {
            // Đường dẫn đến file (cho môi trường local)
            return $credentials;
        }

        if (is_string($credentials) && $this->isJson($credentials)) {
            // JSON string (cho Heroku)
            return json_decode($credentials, true);
        }

        throw new \Exception('Invalid Firebase credentials configuration');
    }

    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

