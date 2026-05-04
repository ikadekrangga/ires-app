<?php
use App\Services\MetaServices;

public function refreshToken($id,MetaService $metaService ){
    $account = Account::findOrFail($id);
    
    try{
        $result = $metaService->refreshLongLivedToken($account->access_token);

        $newToken = $result['access_token'];
        $expiresIn = $result['expires_in'];

        $account->update([
            'access_token' => $newToken,
            'token_expires_at' => now()->addSecond($expiresIn)
        ]);

        return response->json([
            'access_token' => $newToken,
            'token_expires_at' => $account->token_expires_at
        ]);
    } catch(\Exception $e){

    return response->json([
        'error' => 'refresh_failed',
        'message' => $e.getMessage()
    ], 500);
    }
}