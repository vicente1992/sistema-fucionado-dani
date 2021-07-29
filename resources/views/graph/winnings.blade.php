@extends('layouts.app')

@section('content')
<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
  <div class="wrap">
    <section class="app-content">
      <div class="row">
        <div class="col-md-12">
          <div class="widget p-lg">
            <div class="row">
              <div class="col-md-2">
                <h4 class="m-b-lg mx-2">Ganancias vs Gastos</h4>
              </div>
              @if (count($data)>0)
              <div class="col-md-6">
                <a href="{{url('supervisor/graph?type=overdraft')}}{{$params}}" class="btn btn-success">Ver graficas de
                  prestamos</a>
                <a href="{{url('supervisor/graph?type=payment')}}{{$params}}" class="btn btn-primary">Ver graficas de
                  pagos</a>
                <a href="{{url('supervisor/graph?type=bill')}}{{$params}}" class="btn btn-danger">Ver
                  graficas de gastos</a>
              </div>
              @endif
            </div>
            <form class="container" action="{{url('supervisor/graph')}}" method="GET">
              {{ csrf_field() }}
              <div class="form-group">
                <label for="payment_number">Cobrador:</label>
                <select name="agent" class="form-control" id="agent">
                  @foreach($clients as $client)
                  <option value="{{$client->id}}">
                    {{$client->name}} {{$client->last_name}}
                    - {{$client->wallet_name}} ({{$client->address}})
                  </option>
                  @endforeach

                </select>
              </div>
              <div class="row align-items-end">
                <div class="col-sm-4">
                  <label for="nit_number"> Fecha Inicial:</label>
                  <input type="text" name="date_start" class="form-control datepicker-trigger" id="date_start" required>
                </div>
                <div class="col-sm-4">
                  <label for="nit_number"> Número de semanas:</label>
                  <select name="numberWeek" class="form-control" id="numberWeek" required>
                    @foreach($numberWeeks as $numberWeek)
                    <option value="{{$numberWeek}}">
                      {{$numberWeek}}
                    </option>
                    @endforeach
                  </select>

                </div>
                <div class="col-sm-4">
                  <button class="btn btn-info hidden" type="submit">Buscar</button>
                  <a href="{{url('supervisor/graph?type=default')}}" class="btn btn-dark">Regresar</a>
                </div>
              </div>
              <input type="hidden" name="type" id="type" value="winnings">
            </form>
            <br class="clearfix">
            <div class="container">
              @if(count($data)>0)
              <input type="hidden" name="dataGraph" id="dataGraph" value="{{json_encode($data)}}">
              <div class="mt-3 d-flex flex-wrap justify-content-around">
                <div class="chart-container" style="width: 70vh !important;">
                  <canvas id="dataAmount"></canvas>
                </div>
                <div class="chart-container" style="width: 70vh !important;">
                  <canvas id="dataItems"></canvas>
                </div>
              </div>
              <div class="mt-3 d-flex flex-wrap justify-content-center">
                <div class="chart-container" style="width: 100vh">
                  <canvas id="dataWeeks"></canvas>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>
<script>
  function load() {
            const dataGraph = JSON.parse(document.getElementById('dataGraph').value);      
     
            graphicsWinnings(
                [dataGraph.dataAmount.credits, dataGraph.dataAmount.bills],
                [dataGraph.labelsWinn, dataGraph.labelsWinn],
                'Ganancias y Gastos',
                'dataAmount'
            );
            graphicsWinnings(
                [dataGraph.dataItems.credits, dataGraph.dataItems.bills],
                [dataGraph.labelsWinn, dataGraph.labelsWinn],
                'Cantidad de ganancias y gastos',
                'dataItems'
            );      

            graphicsWeeksWinn(
            dataGraph.dataWeeks.data,
            dataGraph.dataWeeks.labels,
            'Ganancias y Gastos por semana',
            'dataWeeks'
            );
        }
        setTimeout(function () {
            window.onload = load()
        }, 2000)
</script>
@endsection