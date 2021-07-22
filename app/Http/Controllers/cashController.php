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
use Illuminate\Support\Facades\Auth;

class cashController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = db_supervisor_has_agent::where('id_supervisor', Auth::id())
        //     ->join('wallet', 'id_wallet', '=', 'wallet.id')
        //     ->get();
        // $sum = db_supervisor_has_agent::where('id_supervisor', Auth::id())
        //     ->join('wallet', 'id_wallet', '=', 'wallet.id')
        //     ->sum('agent_has_supervisor.base');
        $user_current = Auth::user();
        $sql_sup = [];
        if ($user_current->level !== 'admin') {
            $sql_sup = array(
                ['id_supervisor', '=', Auth::id()]
            );
        }
        $report = db_close_day::where($sql_sup)
            ->join('wallet', 'wallet.id', '=', 'id_wallet')
            ->select('close_day.*', 'wallet.name as wallet_name')
            ->orderBy('id', 'desc')->get();


        $agents_for_supervisor = db_supervisor_has_agent::where($sql_sup)->get();

        foreach ($agents_for_supervisor as $item) {
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
            $now = Carbon::now();
            $current_date = $now->format('d-m-Y');
            $close_day = db_close_day::whereDate('created_at', Carbon::now()->toDateString())
                ->where(
                    'id_agent',
                    $item->id_user_agent
                )
                ->first();

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

            $wallet = db_supervisor_has_agent::where($sql_sup)
                ->where('id_user_agent', '=', $item->id_user_agent)
                ->join('wallet', 'id_wallet', '=', 'wallet.id')
                ->select(
                    'wallet.name',
                    'wallet.created_at',
                )
                ->first();

            $dataBox[] = (object) [
                'name' => $wallet->name,
                'created_at' => $wallet->created_at,
                'base_agent' => $base,
                'base_final' => $base_final,
                'total_bill' => $bill->sum('amount'),
                'total_summary' => $total_summary,
                'base_credit' => $base_credit,
                'current_date' => $current_date,
                'close_day' => $close_day,
                'box' =>  $base - $bill->sum('amount') + $total_summary

            ];
        }

        $sum = 0;
        foreach ($dataBox as $data) {
            $sum += $data->box;
        }
        $data = array(
            'clients' => $dataBox,
            'report' => $report,
            'sum' => $sum
        );
        return view('supervisor_cash.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = db_supervisor_has_agent::where('id_supervisor', Auth::id())
            ->join('wallet', 'id_wallet', '=', 'wallet.id')
            ->get();

        $data = array(
            'wallet' => $data
        );

        return view('supervisor_cash.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
