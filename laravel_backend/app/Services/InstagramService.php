<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class InstagramService
{
    public function scrapeFromPython($fbPageId, $access_token){

        $url = "http://localhost:8000/scrape";

        try {
            $response = Http::timeout(60)->post($url, [
                'fb_pageId' => $fbPageId,
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
