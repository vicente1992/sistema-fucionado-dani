@extends('layouts.app')

@section('content')

<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
  <div class="wrap">
    <section class="app-content">
      <div class="row">
        <div class="col-md-12">
          <div class="widget p-lg overflow-auto">
            <h4 class="m-b-lg">Ordenar ruta</h4>
            @if(app('request')->input('hide'))
            <div class="alert alert-warning alert-custom alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                  aria-hidden="true">×</span></button>
              <h4 class="alert-title">Informacion</h4>
              <p>Orden cambiado por encima/debajo de un usuario que saltaste el dia de hoy.</p>
            </div>
            @endif
            <table class="table agente-order-route-table">
              <thead class="d-none-edit">
                <tr>
                  <th class="hidden">Orden</th>
                  <th># Credito</th>
                  <th>Nombres</th>
                  <th>Fecha de prestamo</th>
                  <th>Tipo de Negocio</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>



              <tbody class="connectedSortableRoute" id="complete-item-drop-route">
                @foreach($clients as $client)
                <tr id="td_{{$client->id}}" class="item" item-id="{{$client->id }}">
                  <td class="hidden">{{$client->order_list}}</td>
                  <td>{{$client->id}}</td>
                  <td>{{$client->name}} {{$client->last_name}}</td>
                  <td>{{$client->created_at}}</td>

                  <td>{{$client->province}}</td>
                  <td>
                    @if($client->status=='good')
                    <span class="badge-info badge">BUENO</span>
                    @elseif($client->status=='bad')
                    <span class="badge-danger badge">MALO</span>
                    @endif

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