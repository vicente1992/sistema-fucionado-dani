@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap mt-3">
    <div class="col-md-3 col-sm-6">
        <a href="{{url('supervisor/graph?type=overdraft')}}">
            <div class="widget stats-widget">
                <div class="widget-body clearfix bg-primary">
                    <div class="pull-left">
                        <h3 class="widget-title text-white">Prestamos</h3>
                    </div>
                    <span class="pull-right big-icon watermark"><i class="fa fa-bar-chart"></i></span>
                </div>
            </div><!-- .widget -->
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{url('supervisor/graph?type=bill')}}">
            <div class="widget stats-widget">
                <div class="widget-body clearfix bg-danger">
                    <div class="pull-left">
                        <h3 class="widget-title text-white">Gastos</h3>
                    </div>
                    <span class="pull-right big-icon watermark"><i class="fa fa-shopping-cart"></i></span>
                </div>
            </div><!-- .widget -->
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{url('supervisor/graph?type=payment')}}">
            <div class="widget stats-widget">
                <div class="widget-body clearfix bg-success">
                    <div class="pull-left">
                        <h3 class="widget-title text-white">Pagos</h3>
                    </div>
                    <span class="pull-right big-icon watermark"><i class="fa fa-money"></i></span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{url('supervisor/graph?type=winnings')}}">
            <div class="widget stats-widget">
                <div class="widget-body clearfix bg-info">
                    <div class="pull-left">
                        <h3 class="widget-title text-white">Ganancias vs Gastos</h3>
                    </div>
                    <span class="pull-right big-icon watermark"><i class="fa fa-money"></i></span>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection