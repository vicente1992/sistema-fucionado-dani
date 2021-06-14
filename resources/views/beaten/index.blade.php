@extends('layouts.app')

@section('content')

<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
  <div class="wrap">
    <section class="app-content">
      <div class="row">
        <div class="col-md-12">
          <div class="widget p-lg overflow-auto">
            <h4 class="m-b-lg">Creditos Vencidos</h4>
            <table class="table agente-beaten-table">
              <thead class="d-none-edit">
                <tr>
                  <th># Credito</th>
                  <th>Nombres</th>
                  <th>Fecha del prestamo</th>
                  <th>Fecha que venció</th>
                  <th>Cuotas Atrasadas</th>
                  <th>Tipo de Negocio</th>

                </tr>
              </thead>

              <tbody>
                @foreach($clients as $client)
                <tr id="td_{{$client->id}}" class=" item" item-id="{{$client->id }}">
                  <td>{{$client->id}}</td>
                  <td>{{$client->user->name}} {{$client->user->last_name}}</td>
                  <td>{{$client->created_at}}</td>
                  <td>{{$client->vencio}}</td>
                  <td>{{$client->days_rest}}</td>
                  <td>{{$client->user->province}}</td>
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