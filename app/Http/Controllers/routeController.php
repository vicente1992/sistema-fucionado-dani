<?php

namespace App\Http\Controllers;

use App\db_blacklists;
use App\db_credit;
use App\db_not_pay;
use App\db_pending_pay;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class routeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->id = Auth::user()->id;
            if (!db_supervisor_has_agent::where('id_user_agent', Auth::id())->exists()) {
                die('No existe relacion Usuario y Agente');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $data = db_credit::where('credit.id_agent', Auth::id())
        //     ->join('users', 'credit.id_user', '=', 'users.id')
        //     ->where('credit.status', 'inprogress')
        //     ->select('credit.*')
        //     ->orderBy('credit.order_list', 'asc')
        //     ->get();

        $paginate = array();
        $src = $request->input('src');
        $data = db_credit::join('users', 'credit.id_user', '=', 'users.id')
            ->where('credit.id_agent', Auth::id())
            ->where('credit.status', 'inprogress')
            ->where(function ($query) use ($src) {
                $query->where('users.name', 'like', '%' . $src . '%')
                    ->orWhere('users.nit', 'like', '%' . $src . '%')
                    ->orWhere('users.province', 'like', '%' . $src . '%')
                    ->orWhere(
                        'users.email',
                        'like',
                        '%' . $src . '%'
                    )
                    ->orWhere('users.last_name', 'like', '%' . $src . '%');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('summary')
                    ->whereRaw('id_credit = credit.id and DATE(created_at) = CURDATE()');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('not_pay')
                    ->whereRaw('id_credit = credit.id and DATE(created_at) = CURDATE()');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pending_pays')
                    ->whereRaw('id_credit = credit.id and DATE(created_at) = CURDATE()');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('blacklists')
                    ->whereRaw('id_credit = credit.id and DATE(created_at) = CURDATE()');
            })
            ->select(
                'credit.*',
                'users.name as users_name',
                'users.last_name as users_last_name',
                'users.province as users_province',
                'users.status as users_status',
                'credit.created_at as credit_date',
                DB::raw('(SELECT COALESCE(SUM(summary.amount) ,0) FROM summary where id_credit = credit.id) as positive'),
                DB::raw('(SELECT COUNT(summary.amount) as s FROM summary where id_credit = credit.id) as payment_done'),
                DB::raw('(credit.amount_neto + (credit.amount_neto * credit.utility)) as amount_total'), // BIEN!
                DB::raw('ROUND(((credit.amount_neto + (credit.amount_neto * credit.utility)) / credit.payment_number),2) as payment_quote'),
                DB::raw('((credit.amount_neto + (credit.amount_neto * credit.utility)) - (SELECT COALESCE(SUM(summary.amount) ,0) FROM summary where id_credit = credit.id)) as saldo'), // BIEN!
                DB::raw('(((credit.amount_neto * credit.utility) + (credit.amount_neto))/credit.payment_number) as quote'),
                DB::raw('(SELECT created_at FROM summary where id_credit = credit.id ORDER BY id DESC LIMIT 1) as last_pay'),
                DB::raw('ROUND((credit.amount_neto + (credit.amount_neto * credit.utility)) - ((credit.amount_neto + (credit.amount_neto * credit.utility))/credit.payment_number),2) as rest'),
            )
            ->orderBy('credit.order_list', 'asc')
            ->paginate(15);

        $paginate = array(
            'prevPage' => $data->previousPageUrl(),
            'hasMore' => $data->hasMorePages(),
            'nextPage' => $data->nextPageUrl()
        );
        $data_filter = array();
        foreach ($data as $k => $d) {
            $amount_summary = db_summary::where('id_credit', $d->id)->sum('amount');
            $days_crea = count_date($d->created_at);
            $d->days_crea = $days_crea;
            $pay_res = (floatval($days_crea * $d->quote)  -  $amount_summary);
            $days_rest = floatval($pay_res / $d->quote - 1);
            $d->days_rest =  round($days_rest) > 0 ? round($days_rest) : 0;

            if (!db_summary::where('id_credit', $d->id)->whereDate('created_at', '=', Carbon::now()->toDateString())->exists()) {
                $findBlacklists = !db_blacklists::where('id_credit', $d->id)->exists();
                if ($findBlacklists) {
                    $data_filter[] = $d;
                }
            }
        }
        $pending = db_pending_pay::where('id_agent', Auth::id())
            ->whereDate('pending_pays.created_at', '=', Carbon::now()->toDateString())
            ->join('credit', 'credit.id', '=', 'pending_pays.id_credit')
            ->join('users', 'credit.id_user', '=', 'users.id')
            ->select(
                'pending_pays.*',
                'users.name as user_name',
                'users.last_name as user_last_name'
            )
            ->orderBy('pending_pays.id', 'ASC')
            ->get();
        $data_filter_pending = array();
        foreach ($pending as $da) {
            if (!db_summary::where('id_credit', $da->id_credit)->whereDate('created_at', '=', Carbon::now()->toDateString())->exists()) {
                $findSaltar = !db_not_pay::whereDate('created_at', '=', Carbon::now()->toDateString())->where('id_credit', $da->id_credit)->exists();
                $findExist = db_pending_pay::whereDate('created_at', '=', Carbon::now()->toDateString())->where('id_credit', $da->id_credit)->exists();
                if ($findExist && $findSaltar) {
                    $data_filter_pending[] = $da;
                }
            }
        }
        $data_all = array(
            'clients' => $data_filter,
            'pending' => $data_filter_pending,
            'paginate' => $paginate,
            'src' => $src
        );

        return view('route.index', $data_all);
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

        $input = $request->all();

        foreach ($input['completeArr'] as $key => $value) {
            $key = $key + 1;
            db_credit::where('id', $value)->update([
                'order_list' => ($key)
            ]);
        }
        return response()->json(['status' => 'success']);
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
    public function edit(Request $request, $id)
    {


        $id_credit = $request->id_credit;
        $direction = $request->direction;

        if (!isset($direction)) {
            return 'Direction';
        };
        if ($direction == 'up') {
            $direction = '<';
            $order = 'DESC';
        };
        if ($direction == 'down') {
            $direction = '>';
            $order = 'ASC';
        };


        $data = db_credit::where('id_agent', Auth::id())
            ->orderBy('order_list', $order)
            ->where('order_list', $direction, $id)
            ->where('status', 'inprogress')
            ->first();

        $no_pay = db_not_pay::whereDate('created_at', Carbon::now()->toDateString())
            ->where('id_credit', $data->id)
            ->exists();

        db_credit::where('id', $id_credit)->update([
            'order_list' => ($data->order_list)
        ]);

        db_credit::where('id', $data->id)->update([
            'order_list' => ($id)
        ]);

        if ($no_pay) {
            return redirect('/route?hide=true');
        } else {
            return redirect('/route');
        }
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
        $input = $request->completeArr;
        // $arr = [];
        // foreach ($input as $key => $value) {
        //     $k = $value->order;
        //     $arr[$k] = $value;
        // }


        return $input;
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
