@extends('layouts.app')

@section('content')
<!-- MODAL ========-->
<!-- Modal -->
<div id="modal_pay" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <form class="modal-pay" action="{{url('summary')}}" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Abono de cuota</h4>
                </div>
                <div class="modal-body main-body">
                    <div class="form-group">
                        <label for="name">Nombres:</label>
                        <input type="text" readonly class="form-control" id="name">
                    </div>
                    <div class="form-group">
                        <label for="credit_id">Número de prestamo:</label>
                        <input type="text" readonly class="form-control" id="credit_id">
                    </div>
                    <div class="form-group">
                        <label for="amount_value">Valor de Prestamo:</label>
                        <input type="text" readonly class="form-control" id="amount_value">
                    </div>
                    <div class="form-group">
                        <label for="done">Pagado:</label>
                        <input type="text" readonly class="form-control" id="done">
                    </div>
                    <div class="form-group">
                        <label for="saldo">Saldo:</label>
                        <input type="text" readonly class="form-control" id="saldo">
                    </div>
                    <div class="form-group">
                        <label for="payment_number">Valor de cuota:</label>
                        <input type="text" readonly class="form-control" id="payment_quote">
                    </div>
                    <div class="form-group">
                        <label for="done_payment">Cuotas pagadas:</label>
                        <input type="text" readonly class="form-control" id="done_payment">
                    </div>
                    <div class="form-group">
                        <label for="amount">Valor de abono:</label>
                        <input type="number" step="any" min="1" max="" required name="amount" class="form-control"
                            id="amount">
                    </div>
                </div>
                <div class="modal-body msg-success hidden">
                    <div class="form-group text-center">
                        <small class="text-color">Pago realizado</small>
                        <h2 class="text-success">0</h2>
                        <small class="text-color">Saldo</small>
                        <h2 class="text-primary">0</h2>
                    </div>
                </div>
                <div class="modal-footer main-body">
                    <button type="submit" class="btn btn-success btn-block btn-md">Guardar pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FIN MODAL ========-->

<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
    <div class="wrap">
        <section class="app-content">
            <div class="row">
                <div class="col-md-12">
                    {{-- {{$clients}} --}}
                    <div class="widget p-lg">
                        <a href="{{ url('payment-export') }} " class="btn btn-sm btn-primary float-right">Exportar
                            Excel</a>
                        <h4 class="m-b-lg">Clientes y Creditos</h4>
                        <div class="payments-table d-none d-lg-block d-xl-block">
                            <div class="input-router">
                                <form action="{{url('payment')}}" method="GET" autocomplete="off">
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
                            <table class="table agente-payments-table">
                                <thead>
                                    <tr>
                                        <th>Nombres</th>
                                        <th>#Credito</th>
                                        <th>Valor</th>
                                        <th>Saldo Actual</th>
                                        <th>Cuotas Pagadas</th>
                                        <th>Cuotas Atrasadas</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($clients as $client)
                                    {{-- @if($client->positive>0) --}}
                                    <tr id="credit_{{$client->credit_id}}">
                                        <td>{{$client->name}} {{$client->last_name}}</td>
                                        <td>{{$client->credit_id}}</td>
                                        <td>{{$client->amount_neto}}</td>
                                        <td id="saldo">{{$client->positive}}</td>
                                        <td>{{$client->payment_current}} / {{$client->payment_number}}</td>
                                        <td>{{$client->days_rest}}</td>
                                        <td>
                                            @if($client->days_rest <12 ) <span class="badge-success badge">
                                                BUENO</span>
                                                @elseif($client->days_rest >= 12 && $client->days_rest <30) <span
                                                    class="badge-warning badge">
                                                    REGULAR</span>
                                                    @elseif($client->days_rest >= 30)
                                                    <span class="badge-danger badge">MALO</span>
                                                    @endif
                                        </td>
                                        <td>
                                            <a href="{{url('payment')}}/{{$client->credit_id}}?rev=true"
                                                class="btn btn-success btn-xs"><i class="fa fa-money"></i> Pagar</a>
                                            <a href="{{url('summary')}}?id_credit={{$client->credit_id}}"
                                                class="btn btn-info btn-xs"><i class="fa fa-history"></i> Ver</a>
                                        </td>
                                    </tr>
                                    {{-- @endif --}}
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

                        {{--                            MOBILE--}}
                        <div class="payments-table d-sm-block d-lg-none">
                            <div class="input-router">
                                <form action="{{url('payment')}}" method="GET" autocomplete="off">
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
                            <table class="table agente-payments-table">
                                <thead class="d-none">
                                    <tr>
                                        <th>Nombres</th>
                                        <th>#Credito</th>
                                        <th>Valor</th>
                                        <th>Saldo Actual</th>
                                        <th>Cuotas Pagadas</th>
                                        <th>Cuotas Atrasadas</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($clients as $client)
                                    <tr id="credit_{{$client->credit_id}}">
                                        <td>{{$client->name}} {{$client->last_name}}</td>
                                        <td>{{$client->credit_id}}</td>
                                        <td>{{$client->amount_neto}}</td>
                                        <td id="saldo">{{$client->positive}}</td>
                                        <td>{{$client->payment_current}} / {{$client->payment_number}}</td>
                                        <td>{{$client->days_rest}}</td>
                                        <td>
                                            @if($client->days_rest <12 ) <span class="badge-success badge">
                                                BUENO</span>
                                                @elseif($client->days_rest >= 12 && $client->days_rest <30) <span
                                                    class="badge-warning badge">
                                                    REGULAR</span>
                                                    @elseif($client->days_rest >= 30)
                                                    <span class="badge-danger badge">MALO</span>
                                                    @endif
                                        </td>
                                        <td>
                                            <a href="{{url('payment')}}/{{$client->credit_id}}?rev=true"
                                                class="btn btn-success btn-xs"><i class="fa fa-money"></i> Pagar</a>
                                            <a href="{{url('summary')}}?id_credit={{$client->credit_id}}"
                                                class="btn btn-info btn-xs"><i class="fa fa-history"></i> Ver</a>
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

                    </div><!-- .widget -->
                </div>
            </div><!-- .row -->
        </section>
    </div>
</main>
@endsection