<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class InstagramService
{
    public function scrapeFromPython($facebook_page_id, $access_token){

        $url = "http://python_app:8000";

        try {
            $response = Http::timeout(60)->post($url, [
                'facebook_page_id' => $facebook_page_id,
                'access_token' => $access_token
            ]);

            if ($response-> successful()){
                return $response->json();
            }
            Log::error("Python error : " . $response->status());
            return null;
        }
        catch(\Exception $e){
            Log::error("Koneksi ke Python Gagal: ". $e->getMessage());
            return null;
        }
    }
}
