<?php

namespace App\Http\Controllers;

use App\db_agent_has_user;
use App\db_audit;
use App\db_credit;
use App\db_not_pay;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\Exports\PaymentExport;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class paymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

    public function index(Request $request)
    {
        $paginate = array();
        $src = $request->input('src');
        $data_user = db_credit::where('credit.id_agent', Auth::id())
            ->where('credit.status', 'inprogress')
            ->join('users', 'credit.id_user', '=', 'users.id')
            ->where(function ($query) use ($src) {
                $query->where('users.name', 'like', '%' . $src . '%')
                    ->orWhere('users.last_name', 'like', '%' . $src . '%');
            })
            ->select(
                'credit.*',
                'users.id as id_user',
                'users.name',
                'users.last_name'
            )
            ->paginate(15);
        //
        $paginate = array(
            'prevPage' => $data_user->previousPageUrl(),
            'hasMore' => $data_user->hasMorePages(),
            'nextPage' => $data_user->nextPageUrl()
        );

        // dd($data_user);

        foreach ($data_user as $data) {
            if (db_credit::where('id_user', $data->id_user)->where('id_agent', Auth::id())->exists()) {
                $data->setAttribute('credit_id', $data->id);
                $data->setAttribute('amount_neto', ($data->amount_neto) + ($data->amount_neto * $data->utility));
                $data->setAttribute('positive', $data->amount_neto - (db_summary::where('id_credit', $data->id)
                    ->where('id_agent', Auth::id())
                    ->sum('amount')));
                $data->setAttribute('payment_current', db_summary::where('id_credit', $data->id)->count());

                // Coutas atrasads
                $amount_summary = db_summary::where('id_credit', $data->id)->sum('amount');
                $days_crea = count_date($data->created_at);
                $data->total = floatval($data->utility_amount + $data->amount_neto);
                $data->days_crea = $days_crea;
                $quote = $data->total  / floatval($data->payment_number);
                $quote = $data->total  / floatval($data->payment_number);
                $pay_res = (floatval($days_crea * $quote)  -  $amount_summary);
                $days_rest = floatval($pay_res / $quote - 1);
                $data->days_rest =  round($days_rest) > 0 ? round($days_rest) : 0;
            }
        }
        // dd($data_user);
        $data = array(
            'clients' => $data_user,
            'paginate' => $paginate,
        );
        // dd($data); 

        return view('payment.index', $data);
    }

    public function export()
    {
        ob_end_clean(); // este 
        ob_start(); // y este 
        return Excel::download(new PaymentExport(Auth::id()), 'payments.xlsx');
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $amount = $request->amount;
        $credit_id = $request->credit_id;

        $redirect_error = '/payment?msg=Fields_Null&status=error';
        if (!isset($credit_id)) {
            return redirect($redirect_error);
        };
        if (!isset($amount)) {
            return redirect($redirect_error);
        };

        $values = array(
            'created_at' => Carbon::now(),
            'amount' => $amount,
            'id_agent' => Auth::id(),
            'id_credit' => $credit_id,
        );

        db_summary::insert($values);

        $audit = array(
            'created_at' => Carbon::now(),
            'id_user' => Auth::id(),
            'data' => json_encode($values),
            'event' => 'create',
            'device' => $request->device,
            'type' => 'Pago'
        );
        db_audit::insert($audit);

        return redirect('');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if (!db_credit::where('id', $id)->exists()) {
            return 'No existe creido';
        } else {
            $data_tmp = db_credit::where('id', $id)->first();
            if (Auth::id() != $data_tmp->id_agent) {
                return 'No tienes permisos';
            }
        }

        $data = db_credit::find($id);
        $data->user = User::find($data->id_user);
        $tmp_amount = db_summary::where('id_credit', $id)
            ->where('id_agent', Auth::id())
            ->sum('amount');
        $amount_neto = $data->amount_neto;
        $amount_neto += floatval($amount_neto * $data->utility);
        $data->amount_neto = $amount_neto;


        //        dd([$amount_neto,$tmp_amount]);

        $tmp_quote = round(floatval(($amount_neto / $data->payment_number)), 2);
        $tmp_rest = round(floatval($amount_neto - $tmp_amount), 2);

        $data->credit_data = array(
            'positive' => $tmp_amount,
            'rest' => round(floatval($amount_neto - $tmp_amount), 2),
            'payment_done' => db_summary::where('id_credit', $id)->count(),
            'payment_quote' => $tmp_quote
        );


        if ($request->input('format') === 'json') {
            $response = array(
                'status' => 'success',
                'data' => $data,
                'code' => 0
            );
            return response()->json($response);
        } else {
            return view('payment.create', $data);
        }
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
        //        dd(array(
        //            'id_credit' => $request->id_credit,
        //            'id_user' => $id,
        //            'ajax' => $request->ajax
        //        ));
        if (!isset($id_credit)) {
            return 'ID cretido';
        };

        $values = array(
            'created_at' => Carbon::now(),
            'id_credit' => $id_credit,
            'id_user' => $id
        );

        db_not_pay::insert($values);

        $user_audit = User::find($id);
        $audit = array(
            'created_at' => Carbon::now(),
            'id_user' => Auth::id(),
            'data' => json_encode(array(
                'created_at' => Carbon::now(),
                'id_credit' => $id_credit,
                'id_user' => $id,
                'user' => $user_audit->name . ' ' . $user_audit->last_name
            )),
            'event' => 'create',
            'device' => $request->device,
            'type' => 'Pago saltado'
        );
        db_audit::insert($audit);

        if ($request->ajax) {
            $response = array(
                'status' => 'success'
            );
            return response()->json($response);
        } else {
            return redirect('route');
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
