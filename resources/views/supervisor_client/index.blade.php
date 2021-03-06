@extends('layouts.app')

@section('content')
<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
    <div class="wrap">
        <section class="app-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="widget p-lg">
                        <h4 class="m-b-lg">Clientes</h4>
                        <table class="table supervisor-clientes-table">
                            <thead class="d-none-edit">
                                <tr>
                                    <th>Nombres</th>
                                    <th>Tipo de Negocio</th>
                                    <th>Total Creditos</th>
                                    <th>Pagados</th>
                                    <th>Vigentes</th>
                                    <th>Tipo</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($clients as $client)
                                <tr>
                                    {{-- <td>{{$client->name}} {{$client->last_name}}</td> --}}
                                    <td>{{$client->user->name}} {{$client->user->last_name}}</td>
                                    <td>{{$client->user->province}}</td>
                                    {{-- <td>{{$client->province}}</td> --}}
                                    <td>{{$client->total_credit}}</td>
                                    <td>{{$client->credit_close}}</td>
                                    <td>{{$client->credit_inprogress}}</td>
                                    <td>
                                        {{-- @if($client->status=='good')
                                        <span class="badge-info badge">BUENO</span>
                                        @elseif($client->status=='regular')
                                        <span class="badge-warning badge">MALO</span>
                                        @elseif($client->status=='bad')
                                        <span class="badge-danger badge">MALO</span>
                                        @endif --}}
                                        @if($client->days_rest <12 ) <span class="badge-success badge">BUENO</span>
                                            @elseif($client->days_rest >= 12 && $client->days_rest <30) <span
                                                class="badge-warning badge">REGULAR</span>
                                                @elseif($client->days_rest >= 30)
                                                <span class="badge-danger badge">MALO</span>
                                                @endif

                                    </td>
                                    <td>
                                        <a href="{{url('supervisor/client')}}/{{$client->id_user}}/edit"
                                            class="btn btn-warning btn-xs">Editar</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div><!-- .widget -->
                </div>
            </div><!-- .row -->
        </section>
    </div>
</main>
@endsection