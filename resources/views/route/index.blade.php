@extends('layouts.app')

@section('content')

<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
    <div class="wrap">
        <section class="app-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="widget p-lg overflow-auto">
                        <h4 class="m-b-lg">Clientes y Creditos</h4>
                        {{-- <h5>Clientes que faltan por visitar: ({{ count($clients) }}) </h5> --}}
                        <a href="{{url('blacklists')}}" class="btn btn-danger btn hidden"><i class="fa fa-history"></i>
                            Ver Clavos</a>
                        {{-- <button class="btn btn-primary float-left" id="changeList">Ordenar lista</button>
                        <button class="btn btn-primary float-left d-none  mb-4" id="seeList">Ver lista</button> --}}
                        @if(app('request')->input('hide'))
                        <div class="alert alert-warning alert-custom alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">×</span></button>
                            <h4 class="alert-title">Informacion</h4>
                            <p>Orden cambiado por encima/debajo de un usuario que saltaste el dia de hoy.</p>
                        </div>
                        @endif
                        <div class="route-table">
                            <div class="input-router">
                                <form action="{{url('route')}}" method="GET" autocomplete="off">
                                    <div class="input-group">
                                        <input type="text" style="   border-color: #6c757d !important"
                                            class="form-control input-src-route" name="src"
                                            placeholder="Buscar por nombre, apellido, provincia">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <table class="table agente-route-table">
                                <thead class="d-none-edit">
                                    <tr>
                                        <th class="hidden">Orden</th>
                                        <th># Credito</th>
                                        <th>Nombres</th>
                                        <th>Fecha de prestamo</th>
                                        <th>Estado del credito</th>
                                        <th>Cuotas Atrasadas</th>
                                        <th>Cuota diaria</th>
                                        <th>Valor</th>
                                        <th>Saldo</th>
                                        <th>Ultimo pago</th>
                                        <th>Tipo de Negocio</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody class="connectedSortable" id="complete-item-drop">
                                    @foreach($clients as $client)
                                    <tr id="td_{{$client->id}}"
                                        class="{{   $client->days_crea > 30 ? 'beaten': ''  }} {{   $client->days_crea === 30 ? 'expires-today': ''  }}   item"
                                        item-id="{{$client->id }}">
                                        <td class="hidden">{{$client->order_list}}</td>
                                        <td>{{$client->id}}</td>
                                        <td>{{$client->users_name}} {{$client->users_last_name}}</td>
                                        <td>{{$client->credit_date}}</td>
                                        <td>
                                            @if($client->days_crea < 30 ) <span>Vigente</span>
                                                @elseif($client->days_crea === 30) <span>Vence hoy</span>
                                                @elseif($client->days_crea >30)
                                                <span>Vencido + {{$client->days_rest}} cuotas atrasadas </span>
                                                @endif
                                        </td>
                                        <td>{{$client->days_rest}}</td>
                                        <td>{{$client->quote}}</td>
                                        <td>{{$client->amount_total}}</td>
                                        <td id="saldo">{{$client->saldo}}</td>
                                        @if($client->last_pay)
                                        <td>{{$client->last_pay}}</td>
                                        @else
                                        <td>No hay pagos</td>
                                        @endif
                                        <td>{{$client->users_province}}</td>
                                        <td>
                                            @if($client->days_rest <12 ) <span class="badge-success badge">BUENO</span>
                                                @elseif($client->days_rest >= 12 && $client->days_rest <30) <span
                                                    class="badge-warning badge">REGULAR</span>
                                                    @elseif($client->days_rest >= 30)
                                                    <span class="badge-danger badge">MALO</span>
                                                    @endif

                                        </td>
                                        <td>
                                            {{-- <a href="{{url('route')}}/{{$client->order_list}}/edit?id_credit={{$client->id}}&direction=up"
                                            class="btn btn-default btn-xs arw-up btn-center-arrow"><i
                                                class="fa fa-arrow-up"></i></a> --}}
                                            <form action="{{url('pending-pay')}}" method="POST" class="pull-left px-1">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="id_credit" value="{{$client->id}}">
                                                <button type="submit" class="btn btn-dark btn-xs">
                                                    Pendiente
                                                </button>
                                            </form>
                                            <form action="{{url('blacklists')}}" method="POST" class="pull-left px-1">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="id_credit" value="{{$client->id}}">
                                                <button type="submit" class="btn btn-danger btn-xs">
                                                    Clavo
                                                </button>
                                            </form>

                                            <a href="#openModal{{$client->id}}" class="btn btn-success btn-xs"
                                                id="btnPay">
                                                Pagar</a>

                                            @include('route.modal')

                                            <a href="javascript:void(0)" id_user="{{$client->id_user}}"
                                                id_credit="{{$client->id}}"
                                                class="btn btn-warning btn-xs ajax-btn btn-pagar"><i
                                                    class="fa fa-archive "></i> Saltar</a>

                                            <a href="{{url('summary')}}?id_credit={{$client->id}}"
                                                class="btn btn-info btn-xs hidden"><i class="fa fa-history"></i> Ver</a>

                                            {{-- <a href="{{url('route')}}/{{$client->order_list}}/edit?id_credit={{$client->id}}&direction=down"
                                            class="btn btn-default btn-xs arw-down btn-center-arrow"><i
                                                class="fa fa-arrow-down"></i></a> --}}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div>
                                @if($paginate['prevPage'])
                                <a class="btn btn-outline-dark" href="{{$paginate['prevPage']}}">Anterior</a>
                                @endif

                                @if($paginate['nextPage'])
                                <a class="btn btn-outline-dark" href="{{$paginate['nextPage']}}">Siguiente</a>
                                @endif
                            </div>
                        </div>
                        <div class="d-none d-lg-block d-xl-block">
                            <table class="table agente-route-table-pending">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>#Credito</th>
                                        <th>Nombres</th>
                                        <th>Abono</th>

                                    </tr>
                                </thead>
                                <h4 class="m-b-lg">Clientes Pendientes</h4>
                                <tbody>
                                    @foreach($pending as $clienta)
                                    <tr id="td_{{$clienta->id_credit}}">
                                        <td>{{$clienta->id}}</td>
                                        <td>{{$clienta->id_credit}}</td>
                                        <td>{{$clienta->user_name}} {{$clienta->user_last_name}}</td>
                                        <td>
                                            <a href="/payment/{{$clienta->id_credit}}?rev=true"
                                                class="btn btn-success btn-xs"> Pagar</a>
                                            <a href="javascript:void(0)" id_user="{{$clienta->id_credit}}"
                                                id_credit="{{$clienta->id_credit}}"
                                                class="btn btn-warning btn-xs ajax-btn btn-pagar">
                                                <i class="fa fa-archive "></i> Saltar
                                            </a>
                                        </td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{--                        MOBILE--}}
                        <div class="d-sm-block d-lg-none">
                            <table class="table agente-route-table-pending">
                                <thead class="d-none">
                                    <tr>
                                        <th>Id</th>
                                        <th>#Credito</th>
                                        <th>Nombres</th>
                                        <th>Abono</th>

                                    </tr>
                                </thead>
                                <h4 class="m-b-lg">Clientes Pendientes</h4>
                                <tbody>

                                    @foreach($pending as $clienta)
                                    <tr id="td_{{$clienta->id_credit}}">
                                        <td>{{$clienta->id}}</td>
                                        <td>{{$clienta->id_credit}}</td>
                                        <td>{{$clienta->user_name}} {{$clienta->user_last_name}}</td>
                                        <td>
                                            <a href="/payment/{{$clienta->id_credit}}?rev=true"
                                                class="btn btn-success btn-xs"> Pagar</a>
                                            <a href="javascript:void(0)" id_user="{{$clienta->id_credit}}"
                                                id_credit="{{$clienta->id_credit}}"
                                                class="btn btn-warning btn-xs ajax-btn btn-pagar">
                                                <i class="fa fa-archive "></i> Saltar
                                            </a>
                                        </td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div><!-- .widget -->
                </div>
            </div><!-- .row -->
        </section>
    </div>
</main>
@endsection