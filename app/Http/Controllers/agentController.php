<?php

namespace App\Http\Controllers;

use App\db_audit;
use App\db_income_history;
use App\db_supervisor_has_agent;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class agentController extends Controller
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
                'wallet.name as wallet_name',
                'agent_has_supervisor.base as base_total'
            )
            ->get();
        $data = array(
            'clients' => $data,
            'today' => Carbon::now()->toDateString(),

        );
        return view('supervisor_agent.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
        $data = User::where('users.id', $id)->join('agent_has_supervisor', 'users.id', '=', 'agent_has_supervisor.id_user_agent')
            ->join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->select(
                'users.name',
                'users.last_name',
                'users.country',
                'users.address',
                'wallet.name as wallet_name',
                'users.id',
                'agent_has_supervisor.base as base_current'
            )
            ->first();

        return view('supervisor_agent.edit', $data);
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
        $base = $request->base_number;
        if (!isset($base)) {
            return 'Base Vacia';
        };
        $user_current = Auth::user();

        $sql = [];
        if ($user_current->level === 'supervisor') {
            $sql = array(
                ['id_supervisor', '=', Auth::id()]
            );
        }
        $base_current = db_supervisor_has_agent::where('id_user_agent', $id)
            ->where($sql)->first()->base;
        $supervisor_has_agent = db_supervisor_has_agent::where('id_user_agent', $id)
            ->where($sql)->first();
        $base = $base_current + $base;
        db_supervisor_has_agent::where('id_user_agent', $id)
            ->where($sql)
            ->update(['base' => $base]);

        $user_audit = User::where('users.id', $id)->select(
            'name',
            'last_name'
        )->first();
        $valuesIncome = array(
            'id_user_agent' => $id,
            'id_supervisor' => $supervisor_has_agent->id_supervisor,
            'base' => $request->base_number,
            'base_current' => $base_current,
            'base_total' => $base,
            'id_wallet' => $supervisor_has_agent->id_wallet,
            'created_at' => Carbon::now()
        );
        db_income_history::insert($valuesIncome);
        $audit = array(
            'created_at' => Carbon::now(),
            'id_user' => Auth::id(),
            'data' => json_encode(array(
                'base' => $base,
                'agent_id' => $id,
                'agent' => $user_audit->name . ' ' . $user_audit->last_name
            )),
            'event' => 'update',
            'device' => $request->device,
            'type' => 'Asignar Caja'
        );
        db_audit::insert($audit);

        return redirect('supervisor/agent');
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
