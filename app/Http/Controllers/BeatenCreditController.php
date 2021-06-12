<?php

namespace App\Http\Controllers;

use App\db_credit;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BeatenCreditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // dd($request->agent);
        $id_agent = $request->agent;


        $sql = [];
        if (isset($id_agent)) {
            $agent =  db_supervisor_has_agent::where('id_user_agent', $id_agent)
                ->where('id_supervisor', Auth::id())->pluck('id_user_agent');
            if (count($agent) === 0) return 'No esta autorizado para esta operación';
            $sql[] = ['id_agent', $id_agent];
        } else {
            $sql[] = ['id_agent', Auth::id()];
        }
        $data = db_credit::where($sql)
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
        return view('beaten.index', $data_all);
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
