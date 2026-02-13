<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\InstagramService;

class AccountController extends Controller
{

    protected $instagramService;

    public function __construct(InstagramService  $instagramService){
        $this -> instagramService = $instagramService ;
    }

    public function syncData(){
        
        //Mengambil semua data akun
        $accounts = Account::all();

        foreach($accounts as $account){
            $result = $this ->instagramService ->scrapeFromPython(
                $account->fb_pageId,
                $account-> access_token
            );

            if($result){

            $account-> insights()->create([
                'reach' => $result['reach'],
                'impressions' => $result['impressions'],
                'report_date' => now()
            ]);
            }
        
            return response()->json(['message' => 'Otomasi Sinkronisasi selesai!']);
        }

    }

    public function index(){
        
    $accounts = Account::all();

    return view('accounts.index', compact('accounts'));
    }

    public function startAutomation(){
        
    }
}
