<?php

namespace App\Http\Controllers;

use App\db_audit;
use App\db_bills;
use App\db_list_bills;
use App\db_supervisor_has_agent;
use App\db_wallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class billController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $category = $request->category;
        $user_current = Auth::user();
        $list_categories = db_list_bills::all();

        // $sql = [];
        // if ($user_current->level !== 'admin') {
        //     $sql = array(
        //         ['id_agent', '=', Auth::id()]
        //     );
        // }

        // if (isset($date_start) && isset($date_end)) {
        //     $sql[] = ['bills.created_at', '>=', Carbon::createFromFormat('d/m/Y', $date_start)];
        //     $sql[] = ['bills.created_at', '<=', Carbon::createFromFormat('d/m/Y', $date_end)];
        // } else {
        //     $sql[] = ['bills.created_at', '>=', Carbon::now()->startOfDay()];
        //     $sql[] = ['bills.created_at', '<=', Carbon::now()->endOfDay()];
        // }

        // $data = db_bills::where($sql)
        //     ->join('wallet', 'bills.id_wallet', '=', 'wallet.id')
        //     ->join('list_bill', 'bills.type', '=', 'list_bill.id')
        //     ->join('users', 'bills.id_agent', '=', 'users.id')
        //     ->select(
        //         'bills.*',
        //         'wallet.name as wallet_name',
        //         'list_bill.name as category_name',
        //         'users.name as user_name'
        //     );

        // if (isset($category)) {
        //     $data = $data->where('bills.type', $category);
        // }

        // $data = $data->get();

        $sql = [];

        if ($user_current->level !== 'admin') {
            $sql = array(
                ['id_user_agent', '=', Auth::id()]
            );
        }

        if (isset($date_start) && isset($date_end)) {
            $sql[] = ['bills.created_at', '>=', Carbon::createFromFormat('d/m/Y', $date_start)];
            $sql[] = ['bills.created_at', '<=', Carbon::createFromFormat('d/m/Y', $date_end)];
        } else {
            $sql[] = ['bills.created_at', '>=', Carbon::now()->startOfDay()];
            $sql[] = ['bills.created_at', '<=', Carbon::now()->endOfDay()];
        }


        $data = db_supervisor_has_agent::where($sql)
            ->join('wallet', 'agent_has_supervisor.id_wallet', '=', 'wallet.id')
            ->join('bills', 'wallet.id', '=', 'bills.id_wallet')
            ->join('users', 'bills.id_agent', '=', 'users.id')
            ->join('list_bill', 'bills.type', '=', 'list_bill.id')
            ->select(
                'bills.*',
                'wallet.name as wallet_name',
                'users.name as user_name',
                'list_bill.name as category_name',
            )->get();

        if (isset($category)) {
            $data = $data->where('type', $category);
        }

        $data = array(
            'clients' => $data,
            'total' => $data->sum('amount'),
            'list_categories' => $list_categories
        );


        return view('bill.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = db_list_bills::all();
        return view('bill.create', array('bills' => $data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $amount = $request->amount;
        $description = $request->description;
        $type = $request->type_bill;
        $wallet = db_supervisor_has_agent::where('id_user_agent', Auth::id())->first();
        $values = array(
            'description' => $description,
            'id_agent' => Auth::id(),
            'amount' => $amount,
            'created_at' => Carbon::now(),
            'type' => $type,
            'id_wallet' => $wallet->id_wallet
        );

        db_bills::insert($values);

        $user_audit = User::where('users.id', Auth::id())->select(
            'name',
            'last_name'
        )->first();
        $type_audit = db_list_bills::find($type);
        $wallet_audit = db_wallet::find($wallet->id_wallet);
        $audit = array(
            'created_at' => Carbon::now(),
            'id_user' => Auth::id(),
            'data' => json_encode(array(
                'description' => $description,
                'agent_id' => Auth::id(),
                'agent' => $user_audit->name . ' ' . $user_audit->last_name,
                'amount' => $amount,
                'created_at' => Carbon::now(),
                'type' => $type_audit->name,
                'id_wallet' => $wallet->id_wallet,
                'wallet' => $wallet_audit->name
            )),
            'event' => 'create',
            'device' => $request->device,
            'type' => 'Gasto'
        );
        db_audit::insert($audit);

        return redirect('bill');
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
