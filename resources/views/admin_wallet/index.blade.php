@extends('layouts.app')

@section('content')
<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
  <div class="wrap">
    <section class="app-content">
      <div class="d-flex flex-wrap justify-content-center">
        @foreach($data as $item)
        <div class="widget m-2 stats-widget widget-resume">
          <div class="widget-body clearfix h-100 bg-white">
            <div class="pull-left">
              <h3 class="widget-title text-dark">{{$item->name}}</h3>
              <h3 class="widget-title text-dark">
                <h5> Pais: <span class=""> {{$item->name_country}}</span>
                  <h5> Ciudad: <span class=""> {{$item->address}}</span>
                    <h5>Cobrador: <span class=""> {{$item->agent_name}}</span>
                      <h5>Supervisor: <span class=""> {{$item->name_supervisor}}</span>
                        <h5>Caja: <span class=""> {{$item->box}}</span>
              </h3>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </section>
  </div>
</main>
@endsection