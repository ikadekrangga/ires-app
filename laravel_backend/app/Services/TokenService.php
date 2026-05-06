<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokenService
{
    /**
     * Memperbarui token Meta yang hampir expired.
     */
    public function refreshToken(AccountCredential $credential): bool
    {
        $appId = config('services.meta.client_id');
        $appSecret = config('services.meta.client_secret');

        try {
            $response = Http::timeout(10)->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => $appId,
                'client_secret'     => $appSecret,
                'fb_exchange_token' => $credential->access_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Matikan token yang lama
                $credential->update(['is_valid' => false]);
                
                // Buat token baru di tabel account_credentials
                $credential->account->credentials()->create([
                    'access_token' => $data['access_token'],
                    'expires_at'   => isset($data['expires_in']) 
                                        ? now()->addSeconds($data['expires_in']) 
                                        : now()->addDays(60),
                    'is_valid'     => true,
                ]);
                
                return true;
            }

            // Jika gagal (Token mati, error 190, dll)
            Log::error("Refresh token failed for Account ID: {$credential->account_id}", $response->json());
            $this->markAccountAsNeedsReauth($credential->account);
            
            return false;

        } catch (\Exception $e) {
            Log::error("Network error refreshing token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set status akun menjadi butuh autentikasi ulang
     */
    public function markAccountAsNeedsReauth(Account $account): void
    {
        // 1. Matikan semua token
        $account->credentials()->update(['is_valid' => false]);
        
        // 2. Update status akun menjadi need_reauth
        $account->update(['status' => 'need_reauth']);
        
        // 3. Batalkan job yang menggantung (daripada worker mencoba terus)
        $account->jobs()->whereIn('status', ['pending', 'processing'])
            ->update([
                'status'       => 'permanent_fail',
                'error_reason' => 'Token Expired/Invalid (Error 190)'
            ]);
    }
}
