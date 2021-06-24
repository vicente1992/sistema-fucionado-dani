<?php

namespace App\Http\Controllers;

use App\db_credit;
use App\db_summary;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_credit = $request->id_credit;
        $id_agent = $request->id_agent;
        if (!isset($id_credit)) {
            return 'ID Credito Vacio';
        } else {
            if (!db_credit::where('id', $id_credit)->exists()) {
                return 'ID No existe';
            }
        }
        $sql[] = ['id_agent', '=', $id_agent];
        if (isset($id_credit)) {
            $sql[] = ['id_credit', '=', $id_credit];
        }
        $data_credit = db_credit::find($id_credit);

        $tmp = db_summary::where($sql)->get();
        $amount = floatval(db_credit::find($id_credit)->amount_neto) + floatval(db_credit::find($id_credit)->amount_neto * db_credit::find($id_credit)->utility);
        foreach ($tmp as $t) {
            $amount -= $t->amount;
            $t->rest = $amount;
        }
        $data_credit->utility_amount = floatval($data_credit->utility * $data_credit->amount_neto);
        $data_credit->utility = floatval($data_credit->utility * 100);
        $data_credit->payment_amount = (floatval($data_credit->amount_neto + $data_credit->utility_amount) / floatval($data_credit->payment_number));

        $data_credit->total = floatval($data_credit->utility_amount + $data_credit->amount_neto);
        $amount_last = 0;
        if (db_summary::where($sql)->exists()) {
            $amount_last = db_summary::where($sql)->orderBy('id', 'desc')->first()->amount;
        }
        $amount_summary = db_summary::where($sql)->sum('amount');
        $last = array(
            'recent' => $amount_last,
            'rest' => ($data_credit->total) - ($amount_summary)
        );

        //Coutas atrasadas
        $days_crea = count_date($data_credit->created_at);
        $data_credit->days_crea = $days_crea;
        $quote = $data_credit->total  / floatval($data_credit->payment_number);
        $pay_res = (floatval($days_crea * $quote)  -  $amount_summary);
        $days_rest = floatval($pay_res / $quote - 1);
        $data_credit->days_rest =  round($days_rest) > 0 ? round($days_rest) : 0;

        $data = array(
            'clients' => $tmp,
            'user' => User::find(db_credit::find($id_credit)->id_user),
            'credit_data' => $data_credit,
            'other_credit' => db_credit::where('id_user', $data_credit->id_user)->get(),
            'last' => $last,
        );

        return view('summary.index', $data);
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
