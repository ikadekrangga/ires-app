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
     * Menampilkan semua akun untuk Dashboard Frontend
     */
    public function index()
    {
        // Menggunakan kolom baru: name, ig_account_id, status, dan me-load status token aktif
        $accounts = Account::select('id', 'ig_account_id', 'fb_page_id', 'name', 'status')
            ->with(['activeCredential' => function ($query) {
                $query->select('account_id', 'expires_at'); // Hanya tampilkan expires_at untuk keamanan
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $accounts
        ]);
    }

    /**
     * 📌 POST /accounts
     * Create account + exchange short-lived token to long-lived token
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'instagram_business_id' => 'required|string', 
            'fb_page_id' => 'nullable|string',
            'access_token' => 'required|string', // short-lived token dari Meta Login
        ]);

        try {
            // 🔥 Exchange ke long-lived token
            $response = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.meta.client_id'), 
                'client_secret' => config('services.meta.client_secret'),
                'fb_exchange_token' => $request->access_token,
            ]);

            if (!$response->ok()) {
                return response()->json([
                    'error' => 'Token exchange failed',
                    'detail' => $response->body()
                ], 500);
            }

            $data = $response->json();

            // 1. Create Identity (Account)
            $account = Account::create([
                'name' => $request->name,
                'ig_account_id' => $request->instagram_business_id,
                'fb_page_id' => $request->fb_page_id ?? '',
                'status' => 'active'
            ]);

            // 2. Create Credential (Token)
            $account->credentials()->create([
                'access_token' => $data['access_token'],
                'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : now()->addDays(60),
                'is_valid' => true,
            ]);

            return response()->json([
                'message' => 'Account created successfully',
                'data' => $account->load('activeCredential')
            ]);

        } catch (\Throwable $e) {
            Log::error("Account store error", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'error' => 'Gagal menyimpan akun. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * 📌 OPTIONAL: manual trigger (debug only)
     */
    public function syncData()
    {
        $accounts = Account::where('status', 'active')->get();
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

    /**
     * 📌 PUT /accounts/{id}
     * Update akun (misal: reconnect token)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'instagram_business_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string',
        ]);

        $account = Account::findOrFail($id);

        $account->update([
            'name' => $request->name ?? $account->name,
            'ig_account_id' => $request->instagram_business_id ?? $account->ig_account_id,
        ]);

        // Jika user melakukan RE-AUTH (mengirim token baru)
        if ($request->filled('access_token')) {
            // Matikan semua token lama
            $account->credentials()->update(['is_valid' => false]);
            
            // Simpan token baru
            $account->credentials()->create([
                'access_token' => $request->access_token,
                'expires_at' => now()->addDays(60), // Asumsi token yang dikirim sudah long-lived
                'is_valid' => true,
            ]);

            // Reset status kembali aktif
            $account->update(['status' => 'active']);
        }

        return response()->json($account->load('activeCredential'));
    }

    /**
     * 📌 DELETE /accounts/{id}
     */
    public function destroy($id)
    {
        // Karena kita set onDelete Cascade di migration, 
        // hapus Account akan otomatis menghapus Credential, Job, dan Insight
        Account::findOrFail($id)->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Endpoint alternatif yang sepertinya duplikat dengan store()
     * Disarankan menggunakan store() untuk flow utama.
     */
    public function create(Request $request)
    {
        $account = Account::create([
            'name' => $request->account_name,
            'ig_account_id' => $request->instagram_business_id,
            'fb_page_id' => '',
            'status' => 'active'
        ]);

        return response()->json($account);
    }
}