<?php

namespace App\Http\Controllers;

use App\db_bills;
use App\db_close_day;
use App\db_credit;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\db_wallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminWalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()

    {
        $data = db_wallet::join('countrys', 'country', '=', 'countrys.id')
            ->join('agent_has_supervisor', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->join('users', 'users.id', '=', 'agent_has_supervisor.id_supervisor')
            ->select(
                'wallet.*',
                'countrys.name as name_country',
                'users.name as name_supervisor'
            )
            ->get();

        foreach ($data as $d) {
            $agent =   db_supervisor_has_agent::where('id_wallet', '=', $d->id)
                ->join('users', 'users.id', '=', 'agent_has_supervisor.id_user_agent')
                ->select(
                    'users.name',
                    'users.id',
                )
                ->first();
            $d->agent_name = $agent->name;
            $d->id_user_agent = $agent->id;
        }

        foreach ($data as $item) {
            $data_summary = db_summary::whereDate(
                'summary.created_at',
                Carbon::now()->toDateString()
            )
                ->where('credit.id_agent', $item->id_user_agent)
                ->join('credit', 'summary.id_credit', '=', 'credit.id')
                ->join('users', 'credit.id_user', '=', 'users.id')
                ->select(
                    'users.name as user_name',
                    'users.last_name as user_last_name',
                    'credit.payment_number',
                    'credit.utility',
                    'credit.amount_neto',
                    'credit.id as id_credit',
                    'summary.number_index',
                    'summary.amount',
                    'summary.created_at'
                )
                ->groupBy('summary.id')
                ->get();

            $base = db_supervisor_has_agent::where('id_user_agent', $item->id_user_agent)->first()->base ?? 0;
            $base_final = db_supervisor_has_agent::where('id_user_agent', $item->id_user_agent)->first()->base ?? 0;
            $base_credit = db_credit::whereDate('created_at', Carbon::now()->toDateString())
                ->where('id_agent', $item->id_user_agent)
                ->sum('amount_neto');
            $base -= $base_credit;
            $base_final = $base_final;
            $base_credit = $base_credit;

            $total_summary = $data_summary->sum('amount');

            $sql = array(
                ['id_agent', '=', $item->id_user_agent]
            );
            $sql[] = ['bills.created_at', '>=', Carbon::now()->startOfDay()];
            $sql[] = ['bills.created_at', '<=', Carbon::now()->endOfDay()];


            $bill = db_bills::where($sql)
                ->join('wallet', 'bills.id_wallet', '=', 'wallet.id')
                ->select('bills.*', 'wallet.name as wallet_name')
                ->get();

            $item->box = $base - $bill->sum('amount') + $total_summary;
        }
        // dd($data);

        return view('admin_wallet.index', array(
            'data' => $data
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
