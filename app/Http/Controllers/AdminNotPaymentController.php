<?php

namespace App\Http\Controllers;

use App\db_credit;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\Exports\NotPayExport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;

class AdminNotPaymentController extends Controller
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

        return view('admin_not_payments.create', $data);
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
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $id_agent = $request->id_agent;

        if (!isset($date_start) || !isset($date_end)) {
            return back()->with('error', 'Todos los campos son obligatorios');
        };

        $startDate = Carbon::createFromFormat('d/m/Y', $date_start);
        $endDate = Carbon::createFromFormat('d/m/Y', $date_end);
        $day_start = Carbon::parse($startDate)->Format('l');
        $day_end = Carbon::parse($endDate)->Format('l');

        if ($day_start !== "Monday") {
            return back()->with('error', 'La fecha inicial debe ser un lunes');
        }
        if ($day_end !== "Sunday") {
            return back()->with('error', 'La fecha final debe ser un domingo');
        }

        $dateRanges = CarbonPeriod::create($startDate, $endDate);
        Cookie::queue('date_start', $request->date_start);
        Cookie::queue('date_end', $request->date_end);
        Cookie::queue('$id_agent', $request->$id_agent);

        $data_credit  = db_credit::where('credit.id_agent', $id_agent)
            ->where('credit.status', 'inprogress')
            ->join('users', 'users.id', '=', 'credit.id_user')
            ->orderBy('credit.created_at', 'asc')
            ->select(
                'credit.id as id_credit',
                'users.id as id_user',
                'users.name',
                'users.last_name'
            )
            ->get();
        $daysOfWeek = [];

        foreach ($data_credit as $data) {
            if (db_credit::where('id_user', $data->id_user)->where('id_agent', $id_agent)->exists()) {
                foreach ($dateRanges->toArray() as $dateRange) {
                    $day = Carbon::parse($dateRange)->Format('l');
                    $daysOfWeek[$day] =  db_summary::where('id_credit', $data->id_credit)
                        ->whereDate('summary.created_at', '=', $dateRange)
                        ->sum('amount');
                }
                $data->summary_day = $daysOfWeek;
            }
        }
        $data_credit = $this->parse_not_payments($data_credit);

        $data = array(
            'clients' => $data_credit
        );

        return view('admin_not_payments.index', $data);
    }

    public function export()
    {
        $date_start =  Cookie::get('date_start');
        $date_end =  Cookie::get('date_end');
        $id_agent =  Cookie::get('$id_agent');
        return Excel::download(new NotPayExport($date_start, $date_end, $id_agent), 'not_payments.xlsx');
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
    private function parse_not_payments($data_credit): array
    {
        $listaFinal = [];
        foreach ($data_credit as $data) {
            if (isset($listaFinal[$data->id_user])) {
                foreach ($data->summary_day as $key => $item) {
                    $listaFinal[$data->id_user]->summary_day[$key] += $item;
                }
            } else {
                $listaFinal[$data->id_user] = (object) array(
                    'id_credit' => $data->id_credit,
                    'id_user' => $data->id_user,
                    'name' => $data->name,
                    'last_name' => $data->last_name,
                    'summary_day' => $data->summary_day
                );
            }
        }
        return $listaFinal;
    }
}
