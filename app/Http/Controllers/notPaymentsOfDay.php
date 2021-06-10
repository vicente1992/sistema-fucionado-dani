<?php

namespace App\Http\Controllers;

use App\db_credit;
use App\db_not_pay;
use App\db_summary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class notPaymentsOfDay extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $data_credit  = db_credit::where('credit.id_agent', Auth::id())
            ->where('credit.status', 'inprogress')
            ->get();

        // $clients = [];
        foreach ($data_credit as $d) {
            if (db_not_pay::where('id_credit', $d->id)->whereDate('created_at', '=', Carbon::now()->toDateString())->exists()) {
                $clients =  db_not_pay::where('id_credit', $d->id)
                    ->whereDate('not_pay.created_at', '=', Carbon::now()->toDateString())
                    ->join('users', 'not_pay.id_user', '=', 'users.id')
                    ->select(
                        'not_pay.*',
                        'users.name',
                        'users.last_name',
                        'users.province'
                    )
                    ->orderBy('not_pay.id_user', 'asc')
                    ->get();
                $data_credits[] = $this->parse_not_payments($clients);
            }
        }

        $data = array(
            'clients' => $data_credits ?? []
        );
        return view('not-payments-day.index', $data);
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
    private function parse_not_payments($data_credit)
    {
        $listaFinal = [];
        foreach ($data_credit as $data) {

            $listaFinal = (object) [
                "id" => $data->id,
                "created_at" => $data->created_at,
                "id_credit" => $data->id_credit,
                "id_user" => $data->id_user,
                "name" =>    $data->name,
                "last_name" => $data->last_name,
                "province" => $data->province,
            ];
        }
        return $listaFinal;
    }
}
