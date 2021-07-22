<?php

namespace App\Http\Controllers;

use App\db_bills;
use App\db_close_day;
use App\db_credit;
use App\db_income_history;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\db_wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class subCloseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('submenu.close.create');
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {

        $id_wallet = $id;
        $date = $request->date_start;
        $data_agent = db_supervisor_has_agent::where('id_wallet', $id_wallet)->first();
        $base_raw = db_close_day::where('id_supervisor', $data_agent->id_supervisor)
            ->whereDate('created_at', '=', Carbon::createFromFormat('d/m/Y', $date))
            ->first();

        if (!$base_raw) {
            die('No existe cierre');
        }
        $base_amount_before = $base_raw->base_before;
        $base_amount_total = $base_raw->total;

        if (!db_supervisor_has_agent::where('id_wallet', $id_wallet)->exists()) {
            return 'No existe agente con esta ruta';
        }

        if (!isset($id_wallet)) {
            return 'ID wallet vacio';
        };

        $today_amount = db_summary::whereDate('created_at', Carbon::createFromFormat('d/m/Y', $date)
            ->toDateString())
            ->where('id_agent', $data_agent->id_user_agent)
            ->sum('amount');

        $today_sell = db_credit::whereDate('created_at', Carbon::createFromFormat('d/m/Y', $date)
            ->toDateString())
            ->where('id_agent', $data_agent->id_user_agent)
            ->sum('amount_neto');

        $bills = db_bills::whereDate('created_at', Carbon::createFromFormat('d/m/Y', $date)
            ->toDateString())
            ->where('id_wallet', $id)
            ->sum('amount');

        $base_amount = false;
        if (db_close_day::whereDate('created_at', '=', Carbon::createFromFormat('d/m/Y', $date)->toDateString())
            ->where('id_supervisor', Auth::id())
            ->exists()
        ) {
            $base_amount = db_close_day::whereDate('created_at', '=', Carbon::createFromFormat('d/m/Y', $date)->toDateString())->first()->base_before;
        }
        $today_income = db_income_history::whereDate('created_at', '=', Carbon::createFromFormat('d/m/Y', $date)->toDateString())
            ->where('id_wallet', $id)
            ->sum('base');

        $total = floatval($base_amount_before + $today_amount) - floatval($today_sell + $bills);
        $average = 1000;


        $data = array(
            'base' => $base_amount_before,
            'today_amount' => $today_amount,
            'today_sell' => $today_sell,
            'bills' => $bills,
            'total' => $total,
            'average' => $average,
            'id_wallet' => $id,
            'today_income' => $today_income
        );

        return view('submenu.close.show', $data);
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
