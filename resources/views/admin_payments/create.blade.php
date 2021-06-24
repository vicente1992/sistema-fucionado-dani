@extends('layouts.app')

@section('content')
<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
    <div class="wrap">
        <section class="app-content">
            <div class="row">
                <div class="col-md-12 col-lg-8 offset-lg-2">
                    @if ($message = Session::get('error'))
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ $message }}</strong>
                    </div>
                    @endif
                    <div class="widget">
                        <header class="widget-header">
                            <h4 class="widget-title">Listado de clientes</h4>
                        </header><!-- .widget-header -->
                        <hr class="widget-separator">
                        <div class="widget-body">
                            <form method="POST" action="{{ url('supervisor/payment') }}" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="country">Cobrador:</label>
                                    <select name="id_agent" class="form-control" id="country">
                                        @foreach($agents as $agent)
                                        @if(!$agent->ocuped)
                                        <option value="{{$agent->id}}">{{$agent->name}}
                                            - {{$agent->wallet_name}} ({{$agent->address}})

                                        </option>
                                        @endif
                                        @endforeach

                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success btn-block btn-md">Buscar</button>
                                </div>
                            </form>

                        </div><!-- .widget-body -->
                    </div><!-- .widget -->
                </div><!-- END column -->
            </div><!-- .row -->
        </section>
    </div>
</main>
@endsection