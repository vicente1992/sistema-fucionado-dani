<?php

namespace App\Http\Controllers;

use App\db_credit;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\Exports\PaymentExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;

class AdminPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user_current = Auth::user();
        $sql = [];
        if ($user_current->level !== 'admin') {
            $sql = array(
                ['id_supervisor', '=', Auth::id()]
            );
        }
        $data = db_supervisor_has_agent::where($sql)
            ->join('users', 'id_user_agent', '=', 'users.id')
            ->join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->select(
                'users.*',
                'wallet.name as wallet_name'
            )
            ->get();
        $data = array(
            'agents' => $data,
        );

        return view('admin_payments.create', $data);
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

        $id_agent = $request->id_agent;
        Cookie::queue('$id_agent', $id_agent);
        $data_user = db_credit::where('credit.id_agent', $id_agent)
            ->join('users', 'credit.id_user', '=', 'users.id')
            ->select(
                'credit.*',
                'users.id as id_user',
                'users.name',
                'users.last_name'
            )
            ->get();

        foreach ($data_user as $data) {
            if (db_credit::where('id_user', $data->id_user)->where('id_agent',  $id_agent)->exists()) {

                $data->setAttribute('credit_id', $data->id);
                $data->setAttribute('amount_neto', ($data->amount_neto) + ($data->amount_neto * $data->utility));
                $data->setAttribute('positive', $data->amount_neto - (db_summary::where('id_credit', $data->id)
                    ->where('id_agent',  $id_agent)
                    ->sum('amount')));
                $data->setAttribute('payment_current', db_summary::where('id_credit', $data->id)->count());

                // Coutas atrasads
                $amount_summary = db_summary::where('id_credit', $data->id)->sum('amount');
                $days_crea = count_date($data->created_at);
                $data->total = floatval($data->utility_amount + $data->amount_neto);
                $data->days_crea = $days_crea;
                $quote = $data->total  / floatval($data->payment_number);
                $quote = $data->total  / floatval($data->payment_number);
                $pay_res = (floatval($days_crea * $quote)  -  $amount_summary);
                $days_rest = floatval($pay_res / $quote - 1);
                $data->days_rest =  round($days_rest) > 0 ? round($days_rest) : 0;
            }
        }
        $data = array(
            'clients' => $data_user,
            'id_agent' => $id_agent,
        );

        return view('admin_payments.index', $data);
    }

    public function export()
    {
        $id_agent =  Cookie::get('$id_agent');
        return Excel::download(new PaymentExport($id_agent), 'payments.xlsx');
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
