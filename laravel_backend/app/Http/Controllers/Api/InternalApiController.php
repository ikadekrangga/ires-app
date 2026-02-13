<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Insight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InternalApiController extends Controller
{
    public function updateAccount(Request $request){
        $data = $request->validate([
            'fb_pageId' => 'required',
            'ig_pageId' => 'required',
            'ig_username' => 'nullable'
        ]);

        $account = Account::where('fb_pageId', $data['fb_pageId']) -> first();

        if($account){
            $account-> update([
                'ig_pageId' => $data['ig_pageId'],
                'ig_username' => $data['ig_username']
            ]);

            return response()->json(['status' => 'success', 'message' => 'Account Id Updated!' ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Account not found!' ], 404);
    }


    public function storeInsights(Request $request){
        $data = $request->validate([
            'ig_pageId' => 'required',
            'metrics' => 'required|array'
        ]);

        $account = Account::where('ig_pageId', $data['ig_pageId'])-> first();

        if($account){
            //Menyimpan data

            $account->insights()->create([
                'reach' => $data['metrics']['reach']?? 0,
                'impressions' => $data['metrics']['impressions']??0,
                'profile_views' => $data['metrics']['profile_views']??0,
                'report_date' => now()->toDateString()
            ]);

            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error', 'message' => 'Account Not Found! '], 404);
    }
}
