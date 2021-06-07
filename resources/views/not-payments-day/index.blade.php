@extends('layouts.app')

@section('content')

<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
  <div class="wrap">
    <section class="app-content">
      <div class="row">
        <div class="col-md-12">
          <div class="widget p-lg overflow-auto">
            <h4 class="m-b-lg">Clientes que no pagaron hoy</h4>

            <div class="d-none d-lg-block d-xl-block">
              <table class="table agente-not-pay-day-table">
                <thead>
                  <tr>
                    <th>Id</th>
                    <th># Credito</th>
                    <th>Nombres</th>
                    <th>Tipo de negocio</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach($clients as $client)
                  <tr>
                    <td>{{$client->id_user}}</td>
                    <td>{{$client->id_credit}}</td>
                    <td>{{$client->name}} {{$client->last_name}}</td>
                    <td>{{$client->province}}</td>

                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            {{--                        MOBILE--}}
            <div class="d-sm-block d-lg-none">
              <table class="table agente-not-pay-day-table">
                <thead class="d-none">
                  <tr>
                    <th>Id</th>
                    <th># Credito</th>
                    <th>Nombres</th>
                    <th>Tipo de negocio</th>
                  </tr>
                </thead>
                <h4 class="m-b-lg">Clientes Pendientes</h4>
                <tbody>
                  @foreach($clients as $client)
                  <tr>
                    <td>{{$client->id_user}}</td>
                    <td>{{$client->id_credit}}</td>
                    <td>{{$client->name}} {{$client->last_name}}</td>
                    <td>{{$client->province}}</td>

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