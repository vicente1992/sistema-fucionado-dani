@extends('layouts.app')

@section('content')

<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
  <div class="wrap">
    <section class="app-content">
      <div class="row">
        <div class="col-md-12">
          <div class="widget p-lg overflow-auto">
            <h4 class="m-b-lg">Historial de ingreso</h4>
            <table class="table agente-income-table">
              <thead class="d-none-edit">
                <tr>
                  <th># id</th>
                  <th>Agente</th>
                  <th>Cartera</th>
                  <th>Fecha del ingreso</th>
                  <th>Valor ingresado</th>
                  <th>Valor anterior</th>
                  <th>Valor Total</th>
                </tr>
              </thead>
              <tbody>
                @foreach($incomes as $income)
                <tr id="td_{{$income->id}}" class=" item" item-id="{{$income->id }}">
                  <td>{{$income->id}}</td>
                  <td>{{$income->user_name}}</td>
                  <td>{{$income->wallet_name}}</td>
                  <td>{{$income->created_at}}</td>
                  <td>{{$income->base}}</td>
                  <td>{{$income->base_current}}</td>
                  <td>{{$income->base_total}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div><!-- .widget -->
          <footer class="widget-footer">
            <p><b>Total ingresado: </b><span class="text-success">{{$total}}</span></p>
          </footer>
        </div>
      </div><!-- .row -->
    </section>
  </div>
</main>
@endsection