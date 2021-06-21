@extends('layouts.app')

@section('content')
<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
  <div class="wrap">
    <section class="app-content">
      <div class="row">
        <div class="col-md-12 col-lg-8 offset-lg-2">
          <div class="widget">
            <hr class="widget-separator">
            <div class="widget-body">
              <form method="POST" action="{{url('supervisor/client-review')}}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group hidden">
                  <label for="agent"> Cobrador:</label>
                  <select name="agent" class="form-control" id="agent">
                    @foreach($agents as $a)
<<<<<<< Updated upstream
                    <option value="{{$a->id}}">{{$a->name}} {{$a->last_name}}</option>
=======
                    <option value="{{$a->id}}">{{$a->name}} {{$a->last_name}} - {{$a->wallet_name}} ({{$a->address}})</option>
>>>>>>> Stashed changes
                    @endforeach
                  </select>
                </div>
                <button type="submit" class="btn btn-success btn-block btn-md">Consultar</button>
            </div>
            </form>
          </div>
        </div>
      </div>
  </div>
  </section>
  </div>
</main>
@endsection