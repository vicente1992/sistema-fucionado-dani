<?php

namespace App\Http\Controllers;

use App\db_credit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $clients = db_credit::where('credit.id_agent', Auth::id())
            ->join('users', 'credit.id_user', '=', 'users.id')
            ->where('credit.status', 'inprogress')
            ->select(
                'credit.*',
                'users.name',
                'users.last_name',
                'users.province',
                'users.status'
            )
            ->orderBy('credit.order_list', 'asc')
            ->get();

        $data = array(
            'clients' => $clients
        );
        // dd($data);
        return view('order_route.index', $data);
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
