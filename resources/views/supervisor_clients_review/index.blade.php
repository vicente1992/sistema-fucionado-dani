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
                  <td>{{$client->user->name}} {{$client->user->last_name}}</td>
                  <td>{{$client->created_at}}</td>
                  <td>
                    @if($client->days_crea < 30 ) <span>Vigente</span>
                      @elseif($client->days_crea === 30) <span>Vence hoy</span>
                      @elseif($client->days_crea >30)
                      <span>Vencido + {{$client->days_rest}} cuotas atrasdas </span>
                      @endif
                  </td>
                  <td>{{$client->days_rest}}</td>
                  <td>{{$client->quote}}</td>
                  <td>{{$client->amount_total}}</td>
                  <td id="saldo">{{$client->saldo}}</td>
                  @if($client->last_pay)
                  <td>{{$client->last_pay->created_at}}</td>
                  @else
                  <td>No hay pagos</td>
                  @endif
                  <td>{{$client->user->province}}</td>
                  <td>
                    @if($client->days_rest <12 ) <span class="badge-success badge">BUENO</span>
                      @elseif($client->days_rest >= 12 && $client->days_rest <30) <span class="badge-warning badge">
                        REGULAR</span>
                        @elseif($client->days_rest >= 30)
                        <span class="badge-danger badge">MALO</span>
                        @endif

                  </td>
                  <td>
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