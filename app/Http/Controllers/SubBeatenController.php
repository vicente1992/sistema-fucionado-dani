<?php

namespace App\Http\Controllers;

use App\db_credit;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubBeatenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_current = Auth::user();
        $ormSqlWallet = db_supervisor_has_agent::join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->join('users', 'agent_has_supervisor.id_user_agent', '=', 'users.id')
            ->select('wallet.*', 'users.name as user_name', 'users.id as user_id');

        if ($user_current->level !== 'admin') {
            $ormSqlWallet = $ormSqlWallet->where('agent_has_supervisor.id_supervisor', Auth::id());
        }

        $sql = [];
        if ($user_current->level !== 'admin') {
            $sql = array(
                ['id_supervisor', '=', Auth::id()]
            );
        }
        $data =   db_supervisor_has_agent::where($sql)
            ->join('users', 'id_user_agent', '=', 'users.id')->get();

        $data = array(
            // 'wallet' => $ormSqlWallet->get(),
            'agents' =>  $data

        );
        return view('supervisor_beaten.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_agent = $request->agent;
        $data = db_credit::where('id_agent', $id_agent)
            ->join('users', 'credit.id_user', '=', 'users.id')
            ->where('credit.status', 'inprogress')
            ->select('credit.*')
            ->orderBy('credit.created_at', 'asc')
            ->get();
        $data_filter = [];
        foreach ($data as $k => $d) {
            $tmp_amount = db_summary::where('id_credit', $d->id)
                ->where('id_agent', Auth::id())
                ->sum('amount');
            $amount_total = ($d->amount_neto) + ($d->amount_neto * $d->utility);
            $tmp_quote = round(floatval(($amount_total / $d->payment_number)), 2);
            $d->positive = $tmp_amount;
            $d->payment_quote =  $tmp_quote;
            $d->rest = round(floatval($amount_total - $tmp_amount), 2);
            $count_summary = db_summary::where('id_credit', $d->id)->count();
            $d->payment_done = $count_summary;
            $d->user = User::find($d->id_user);
            $d->amount_total = $amount_total;
            $d->days_summ = $count_summary;

            $amount_summary = db_summary::where('id_credit', $d->id)->sum('amount');
            $d->saldo = $d->amount_total - $amount_summary;
            $d->quote = (floatval($d->amount_neto * $d->utility) + floatval($d->amount_neto)) / floatval($d->payment_number);
            $d->setAttribute('last_pay', db_summary::where('id_credit', $d->id)->orderBy('id', 'desc')->first());

            $days_crea = count_date($d->created_at);
            $d->days_crea = $days_crea;

            $pay_res = (floatval($days_crea * $d->quote)  -  $amount_summary);

            $days_rest = floatval($pay_res / $d->quote - 1);
            $d->days_rest =  round($days_rest) > 0 ? round($days_rest) : 0;

            $d->vencio = $d->created_at->addDays(34)->format('Y-m-d');
            if ($d->days_crea > 30) {
                $data_filter[] = $d;
            }
        }

        $data_all = array(
            'clients' => $data_filter,

        );
        return view('supervisor_beaten.index', $data_all);
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
