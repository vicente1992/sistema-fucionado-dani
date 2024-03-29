@extends('layouts.app')

@section('content')
<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
    <div class="wrap">
        <section class="app-content">
            <div class="row">
                <div class="col-md-12">
                    @if (in_array(Auth::user()->level,['agent']))

                    <div class=" p-lg text-right">
                        <a type="button" href="{{url('client')}}" class="btn btn-inverse"><i class="fa fa-users"></i>
                            Mostar clientes</a>
                        <a type="button" href="{{url('route')}}" class="btn btn-deepOrange"><i class="fa fa-car"></i>
                            Continuar ruta</a>
                    </div><!-- .widget -->
                    @endif
                </div>
                @if(app('request')->input('show')=='last')
                <div class="col-md-12 col-sm-12">
                    <div class="widget stats-widget">
                        <div class="widget-body clearfix">
                            <div class="pull-left">
                                <small class="text-color">Pago realizado</small>
                                <h3 class="widget-title text-success">{{$last['recent']}}</h3>
                                <small class="text-color">Saldo</small>
                                <h3 class="widget-title text-primary">{{$last['rest']}}</h3>
                            </div>
                            <span class="pull-right big-icon watermark"><i class="fa fa-money"></i></span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="col-md-12 col-sm-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h4 class="panel-title">{{$user->name}} {{$user->last_name}}</h4>
                        </div>
                        <ul class="list-group">
                            <li class="list-group-item"># Credito: <span class="text-purple">{{$credit_data->id}}</span>
                            </li>
                            <li class="list-group-item">Fecha de Prestamo: <span
                                    class="text-purple">{{$credit_data->created_at}}</span></li>
                            <li class="list-group-item">Tasa de interés: <span
                                    class="text-purple">{{$credit_data->utility}}%</span></li>
                            <li class="list-group-item">Numero de Cuotas: <span
                                    class="text-purple">{{$credit_data->payment_number}}</span></li>
                            <li class="list-group-item">Valor cuota: <span
                                    class="text-purple">{{$credit_data->payment_amount}}</span></li>
                            <li class="list-group-item">Cuotas atrasadas: <span
                                    class="text-purple">{{$credit_data->days_rest}}</span></li>
                            <li class="list-group-item">Estado:
                                @if($credit_data->days_rest <12 ) <span class="badge-success badge">BUENO</span>
                                    @elseif($credit_data->days_rest >= 12 && $credit_data->days_rest <30) <span
                                        class="badge-warning badge">REGULAR</span>
                                        @elseif($credit_data->days_rest >= 30)
                                        <span class="badge-danger badge">MALO</span>
                                        @endif
                            </li>
                            <li class="list-group-item">Capital: <span
                                    class="text-purple">{{$credit_data->amount_neto}}</span></li>
                            <li class="list-group-item">Intereses: <span
                                    class="text-purple">{{$credit_data->utility_amount}}</span></li>
                            <li class="list-group-item">Total: <span class="text-purple">{{$credit_data->total}}</span>
                            </li>

                        </ul>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="widget p-lg">
                        <h4 class="m-b-lg">Historial</h4>

                        <div class="d-none d-lg-block d-xl-block d-md-block overflow-auto">
                            <table class="table agente-paymentsH-table">
                                <tbody>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>No</th>
                                        <th>Valor</th>
                                        <th>Saldo</th>
                                        <th></th>
                                    </tr>
                                    @foreach($clients as $client)
                                    <tr>
                                        <td>{{$client->created_at}}</td>
                                        <td>{{$client->number_index}}</td>
                                        <td>{{$client->amount}}</td>
                                        <td>{{$client->rest}}</td>
                                        <td></td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>


                        <!-- FOR MOBILE -->
                        <div class=" d-lg-none d-xl-none d-md-none">
                            <table class="table agente-paymentsH-table">
                                <tbody>
                                    <!-- <tr>
                                        <th>Fecha</th>
                                        <th>No</th>
                                        <th>Valor</th>
                                        <th>Saldo</th>
                                        <th></th>
                                    </tr> -->
                                    @foreach($clients as $client)
                                    <tr>
                                        <td>{{$client->created_at}}</td>
                                        <td>{{$client->number_index}}</td>
                                        <td>{{$client->amount}}</td>
                                        <td>{{$client->rest}}</td>
                                        <td></td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div><!-- .widget -->
                </div>
            </div><!-- .row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="widget p-lg">
                        <h4 class="m-b-lg">Ultimos Prestamos</h4>
                        @foreach($other_credit as $c)
                        @if((app('request')->input('id_credit'))!=$c->id)
                        <a href="{{url('summary')}}/?id_credit={{$c->id}}" class="btn btn-info">{{$c->id}}</a>
                        @endif
                        @endforeach
                    </div><!-- .widget -->
                </div>
            </div><!-- .row -->
        </section>
    </div>
</main>
@endsection