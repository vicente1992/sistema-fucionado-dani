@extends('layouts.app')

@section('content')
<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
    <div class="wrap">
        <section class="app-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="widget p-lg overflow-auto">
                        <h4 class="m-b-lg">Detalles Clientes y Prestamos</h4>

                        <div class="client-table d-none d-lg-block d-xl-block">
                            <div class="input-router">
                                <form action="{{url('client')}}" method="GET" autocomplete="off">
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
                            <table class="table client-table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Apellidos</th>
                                        <th>Tipo de Negocio</th>
                                        <th>Total Prestamos</th>
                                        <th>Prestamos Terminados</th>
                                        <th>Prestamos Vigentes</th>
                                        <th>Monto Prestado</th>
                                        <th>Saldo Actual</th>
                                        <th>Status</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clients as $client)
                                    <tr>
                                        <td><span class="value">{{$client->name}}</span></td>
                                        <td><span class="value">{{$client->last_name}}</span></td>
                                        <td><span class="value">{{$client->province}}</span></td>
                                        <td><span class="value">{{$client->credit_count}}</span></td>
                                        <td><span class="value">{{$client->closed}}</span></td>
                                        <td><span class="value">{{$client->inprogress}}</span></td>
                                        <td><span
                                                class="value">{{($client->amount_net) ? $client->sum_amount_gap : 0}}</span>
                                        </td>
                                        <td><span class="value">{{$client->summary_net + $client->gap_credit}}</span>
                                        </td>
                                        <td>
                                            @if($client->status=='good')
                                            <span class="badge-info badge">BUENO</span>
                                            @elseif($client->status=='bad')
                                            <span class="badge-danger badge">MALO</span>
                                            @endif

                                        </td>
                                        <td>
                                            <a href="{{url('client/create')}}?id={{$client->id}}"
                                                class="btn btn-success btn-xs">Prestar</a>
                                            <a href="{{url('client')}}/{{$client->id}}"
                                                class="btn btn-info btn-xs">Datos</a>
                                            @if(isset($client->lat) && isset($client->lng))
                                            <a href="http://www.google.com/maps/place/{{$client->lat}},{{$client->lng}}"
                                                target="_blank" class="btn btn-info btn-xs">Ver Mapa</a>
                                            @endif

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

                        <div class="client-table d-sm-block d-lg-none">
                            <div class="input-router">
                                <form action="{{url('client')}}" method="GET" autocomplete="off">
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
                            <table class="table client-table">
                                <thead class="d-none">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Apellidos</th>
                                        <th>Tipo de Negocio</th>
                                        <th>Total Prestamos</th>
                                        <th>Prestamos Terminados</th>
                                        <th>Prestamos Vigentes</th>
                                        <th>Monto Prestado</th>
                                        <th>Saldo Actual</th>
                                        <th>Status</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clients as $client)
                                    <tr>
                                        <td><span class="value">{{$client->name}}</span></td>
                                        <td><span class="value">{{$client->last_name}}</span></td>
                                        <td><span class="value">{{$client->province}}</span></td>
                                        <td><span class="value">{{$client->credit_count}}</span></td>
                                        <td><span class="value">{{$client->closed}}</span></td>
                                        <td><span class="value">{{$client->inprogress}}</span></td>
                                        <td><span
                                                class="value">{{($client->amount_net) ? $client->sum_amount_gap : 0}}</span>
                                        </td>
                                        <td><span class="value">{{$client->summary_net + $client->gap_credit}}</span>
                                        </td>
                                        <td>
                                            @if($client->status=='good')
                                            <span class="badge-info badge">BUENO</span>
                                            @elseif($client->status=='bad')
                                            <span class="badge-danger badge">MALO</span>
                                            @endif

                                        </td>
                                        <td>
                                            <a href="{{url('client/create')}}?id={{$client->id}}"
                                                class="btn btn-success btn-xs">Prestar</a>
                                            <a href="{{url('client')}}/{{$client->id}}"
                                                class="btn btn-info btn-xs">Datos</a>
                                            @if(isset($client->lat) && isset($client->lng))
                                            <a href="http://www.google.com/maps/place/{{$client->lat}},{{$client->lng}}"
                                                target="_blank" class="btn btn-info btn-xs">Ver Mapa</a>
                                            @endif

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

                        <div class="w-100 mx-auto mt-4 alert alert-info d-flex justify-content-between">
                            <p class="m-0">Cartera total</p>
                            <h5 class="m-0">{{$total_pending}}</h5>
                        </div>
                    </div><!-- .widget -->
                </div>
            </div><!-- .row -->
        </section>
    </div>
</main>
@endsection