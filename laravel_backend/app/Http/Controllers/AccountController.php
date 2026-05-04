<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    /**
     * 📌 GET /accounts
     */
    public function index()
    {
        $accounts = Account::select('id', 'instagram_business_id', 'account_name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $accounts
        ]);
    }

    /**
     * 📌 POST /accounts
     * create account + exchange token
     */
    public function store(Request $request)
    {
        $request->validate([
            'instagram_business_id' => 'required|string',
            'access_token' => 'required|string', // short-lived
        ]);

        try {
            // 🔥 exchange ke long-lived token
            $response = Http::get('https://graph.facebook.com/v24.0/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.meta.app_id'),
                'client_secret' => config('services.meta.app_secret'),
                'fb_exchange_token' => $request->access_token,
            ]);

            if (!$response->ok()) {
                return response()->json([
                    'error' => 'Token exchange failed',
                    'detail' => $response->body()
                ], 500);
            }

            $data = $response->json();

            $account = Account::create([
                'instagram_business_id' => $request->instagram_business_id,
                'access_token' => $data['access_token'],
                'expires_at' => now()->addSeconds($data['expires_in']),
            ]);

            return response()->json([
                'message' => 'Account created successfully',
                'data' => $account
            ]);

        } catch (\Throwable $e) {
            Log::error("Account store error", ['error' => $e->getMessage()]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📌 OPTIONAL: manual trigger (debug only)
     */
    public function syncData()
    {
        $accounts = Account::all();

        $results = [];

        foreach ($accounts as $account) {
            $results[] = [
                'account_id' => $account->id,
                'status' => 'triggered'
            ];
        }

        return response()->json([
            'message' => 'Jobs triggered',
            'data' => $results
        ]);
    }

    public function update(Request $request, $id)
{
    $account = Account::findOrFail($id);

    $account->update([
        'account_name' => $request->account_name,
        'access_token' => $request->access_token
    ]);

    return response()->json($account);
}

    public function destroy($id){
        Account::findOrFail($id)->delete();

        return response()->json(['status' => 'deleted']);
    }

    public function create(Request $request){
        $account = Account::create([
            'account_name' => $request->account_name,
            'instagram_business_id' => $request->instagram_business_id,

        ]);

        return response()->json($account);
    }
}