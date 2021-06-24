<?php

namespace App\Http\Controllers;

use App\db_list_bills;
use App\db_supervisor_has_agent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class SubBillConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $date_star = $request->date_start;
        $date_end = $request->date_end;
        $category = $request->category;

        $list_categories = db_list_bills::all();

        $user_current = Auth::user();
        $id_agent =  Cookie::get('id_agent');
        $sql = [];
        if ($user_current->level !== 'admin') {
            $sql = array(
                ['id_supervisor', '=', Auth::id()]
            );
        }

        if (isset($id_agent)) {
            $sql = array(
                [
                    'bills.id_agent', '=',
                    $id_agent
                ]
            );
        }

        //Login Agent
        $ormQry = db_supervisor_has_agent::where($sql)
            ->join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->join('bills', 'wallet.id', '=', 'bills.id_wallet')
            ->join('list_bill', 'bills.type', '=', 'list_bill.id')
            ->join('users', 'bills.id_agent', '=', 'users.id')
            ->select(
                'bills.created_at as created_at',
                'wallet.name as wallet_name',
                'bills.type as type',
                'bills.description',
                'list_bill.name as category_name',
                'users.name as user_name',
                DB::raw('SUM(bills.amount) as amount')
            );


        $ormSum = db_supervisor_has_agent::where($sql)
            ->join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->join('bills', 'wallet.id', '=', 'bills.id_wallet');




        if (isset($date_star)) {
            $ormQry = $ormQry->where(
                'bills.created_at',
                '>=',
                Carbon::createFromFormat('d/m/Y', $date_star)->toDateString()
            );
            $ormSum = $ormSum
                ->where('bills.created_at', '>=', Carbon::createFromFormat('d/m/Y', $date_star)->toDateString());
        }

        if (isset($date_end)) {
            $ormQry = $ormQry->where(
                'bills.created_at',
                '<=',
                Carbon::createFromFormat('d/m/Y', $date_end)->toDateString()
            );
            $ormSum = $ormSum
                ->where('bills.created_at', '<=', Carbon::createFromFormat('d/m/Y', $date_end)->toDateString());
        }

        if (isset($category)) {
            $ormQry = $ormQry->where('bills.type', $category);
        }
        $sum = $ormSum->sum('bills.amount');

        $data = $ormQry->groupBy('bills.id')->orderBy('bills.created_at', 'desc')->get();

        $data = array(
            'clients' => $data,
            'sum' => $sum,
            'list_categories' => $list_categories,
        );


        return view('supervisor_bill.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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

        return view('consult_bills.create', $data);
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
        $id_agent = $request->id_agent;
        $user_current = Auth::user();
        $sql = [];
        if ($user_current->level !== 'admin') {
            $sql = array(
                ['id_supervisor', '=', Auth::id()]
            );
        }

        if ($id_agent !== 'todos') {
            $sql = array(
                [
                    'bills.id_agent', '=',
                    $id_agent
                ]
            );
            Cookie::queue('id_agent', $request->id_agent);
        } else {
            Cookie::queue(Cookie::forget('id_agent'));
        }
        //Login Agent
        $ormQry = db_supervisor_has_agent::where($sql)
            ->join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->join('bills', 'wallet.id', '=', 'bills.id_wallet')
            ->join('list_bill', 'bills.type', '=', 'list_bill.id')
            ->join('users', 'bills.id_agent', '=', 'users.id')
            ->select(
                'bills.created_at as created_at',
                'wallet.name as wallet_name',
                'bills.type as type',
                'bills.description',
                'list_bill.name as category_name',
                'users.name as user_name',
                DB::raw('SUM(bills.amount) as amount')
            );

        $ormSum = db_supervisor_has_agent::where($sql)
            ->join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->join('bills', 'wallet.id', '=', 'bills.id_wallet');




        if (isset($date_star)) {
            $ormQry = $ormQry->where(
                'bills.created_at',
                '>=',
                Carbon::createFromFormat('d/m/Y', $date_star)->toDateString()
            );
            $ormSum = $ormSum
                ->where('bills.created_at', '>=', Carbon::createFromFormat('d/m/Y', $date_star)->toDateString());
        }

        if (isset($date_end)) {
            $ormQry = $ormQry->where(
                'bills.created_at',
                '<=',
                Carbon::createFromFormat('d/m/Y', $date_end)->toDateString()
            );
            $ormSum = $ormSum
                ->where('bills.created_at', '<=', Carbon::createFromFormat('d/m/Y', $date_end)->toDateString());
        }

        if (isset($category)) {
            $ormQry = $ormQry->where('bills.type', $category);
        }
        $sum = $ormSum->sum('bills.amount');

        $data = $ormQry->groupBy('bills.id')->orderBy('bills.created_at', 'desc')->get();
        $list_categories = db_list_bills::all();

        $data = array(
            'clients' => $data,
            'sum' => $sum,
            'list_categories' => $list_categories,
        );

        return view('supervisor_bill.index', $data);
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
