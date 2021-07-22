<?php

namespace App\Http\Controllers;

use App\db_income_history;
use App\db_supervisor_has_agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IcomeHistoryController extends Controller
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
            'agents' =>  $data
        );

        return view('income_history.create', $data);
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
        $data_incomes = db_income_history::where('id_user_agent', $id_agent)
            ->join('users', 'income_history.id_user_agent', '=', 'users.id')
            ->join('wallet', 'income_history.id_wallet', '=', 'wallet.id')
            ->select(
                'income_history.*',
                'users.name as user_name',
                // 'users.name as last_name',
                'wallet.name as wallet_name'
            )
            ->orderBy('income_history.created_at', 'asc')
            ->get();
        $total_incomes = db_income_history::where('id_user_agent', $id_agent)->sum('base');
        $data = array(
            'incomes' =>  $data_incomes,
            'total' =>  $total_incomes
        );
        return  view('income_history.index', $data);
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
