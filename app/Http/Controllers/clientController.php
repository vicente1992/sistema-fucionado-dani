<?php

namespace App\Http\Controllers;

use App\db_agent_has_user;
use App\db_audit;
use App\db_countries;
use App\db_credit;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\db_wallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class clientController extends Controller
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
            'wallet' => db_supervisor_has_agent::where($sql)
                ->join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
                ->get(),
            'agents' => db_supervisor_has_agent::where($sql)
                ->join('users', 'id_user_agent', '=', 'users.id')->get(),
            'countries' => db_countries::all(),
        );
        return view('supervisor_client.create', $data);
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_wallet = $request->wallet;
        $id_agent = $request->agent;
        $country = $request->country;
        $user_current = Auth::user();
        Cookie::queue('id_agent', $id_agent);
        if (!isset($id_wallet)) {
            return 'ID wallet vacio';
        };
        if (!isset($id_agent)) {
            return 'ID agente vacio';
        };
        if (!isset($country)) {
            return 'Pais vacio';
        };
        if ($user_current->level !== 'admin') {
            $id_supervisor = Auth::id();
            db_supervisor_has_agent::where('id_user_agent', $id_agent)->where('id_supervisor', $id_supervisor)
                ->update(['id_wallet' => $id_wallet]);
        }

        $audit = array(
            'created_at' => Carbon::now(),
            'id_user' => $id_supervisor ?? Auth::id(),
            'data' => json_encode(['id_wallet' => $id_wallet]),
            'event' => 'update',
            'type' => 'Cliente'
        );
        db_audit::insert($audit);

        $data_credit = $this->getDataCredit($id_agent);

        $data = array(
            'clients' => $data_credit
        );

        return view('supervisor_client.index', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $data = db_agent_has_user::where('agent_has_client.id_wallet', $id)
            ->join('users', 'agent_has_client.id_client', '=', 'users.id')
            ->join('credit', 'users.id', '=', 'credit.id_user')
            ->select(
                'users.name',
                'users.last_name',
                'users.province',
                'users.status',
                'users.id as id_user',
                DB::raw('COUNT(*) as total_credit')
            )
            ->groupBy('users.id')
            ->get();


        foreach ($data as $datum) {
            $datum->credit_inprogress = db_credit::where('status', 'inprogress')->where('id_user', $datum->id_user)->count();
            $datum->credit_close = db_credit::where('status', 'close')->where('id_user', $datum->id_user)->count();
        }
        $data = array(
            'clients' => $data
        );

        return view('supervisor_client.index', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = User::find($id);
        $data = array(
            'user' => $data
        );
        return view('supervisor_client.unique', $data);
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
        $name = $request->name;
        $last_name = $request->last_name;
        $nit = $request->nit_number;
        $address = $request->address;
        $province = $request->province;
        $phone = $request->phone;
        // $status = $request->status;

        $values = array(
            'name' => $name,
            'last_name' => $last_name,
            'nit' => $nit,
            'address' => $address,
            'province' => $province,
            'phone' => $phone,
            // 'status' => $status
        );

        User::where('id', $id)->update($values);

        $audit = array(
            'created_at' => Carbon::now(),
            'id_user' => Auth::id(),
            'data' => json_encode($values),
            'event' => 'update',
            'device' => $request->device,
            'type' => 'Cliente'
        );
        db_audit::insert($audit);
        $id_agent =  Cookie::get('id_agent');

        if ($id_agent) {
            $data_credit = $this->getDataCredit($id_agent);
            $data = array(
                'clients' => $data_credit
            );
            return view('supervisor_client.index', $data);
        } else {
            return redirect('supervisor/client/');
        }
        // if (db_agent_has_user::where('id_client', $id)->exists()) {
        //     $wallet = db_agent_has_user::where('id_client', $id)->first();
        //     // dd($wallet);
        //     return redirect('supervisor/client/' . $wallet->id_agent);
        // } else {
        //     return redirect('supervisor/client/');
        // }
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

    public function getDataCredit($id_agent)
    {
        $data_credit = db_credit::where('credit.id_agent', $id_agent)
            ->join('users', 'credit.id_user', '=', 'users.id')
            ->where('credit.status', 'inprogress')
            ->select('credit.*')
            ->orderBy('credit.order_list', 'asc')
            ->get();
        foreach ($data_credit as $k => $d) {
            $tmp_amount = db_summary::where('id_credit', $d->id)
                ->where('id_agent', $id_agent)
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

            $d->credit_inprogress = db_credit::where('status', 'inprogress')->where('id_user', $d->id_user)->count();
            $d->credit_close = db_credit::where('status', 'close')->where('id_user', $d->id_user)->count();
            $d->total_credit = $d->credit_inprogress + $d->credit_close;
        }

        return $data_credit;

        // return array(
        //     'clients' => $data_credit,
        // );
    }
}
