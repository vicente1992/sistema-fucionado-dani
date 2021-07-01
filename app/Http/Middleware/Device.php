<?php

namespace App\Http\Middleware;
use Classes\GeoLocation;
use Jenssegers\Agent\Agent;
use Closure;
use Torann\GeoIP\Facades\GeoIP;

class Device extends GeoLocation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $agent = new Agent();
        $a = ($agent->isMobile() || $agent->isTablet()) ? 'Móvil' : 'Escritorio';
        $geoIp = GeoIP::getLocation($request->ip());

        $deviceLocation = $this->checkLocation($geoIp);

        $device = array(
            'Dispositivo' => $a,
            'Tipo' => $agent->device(),
            'Ip' => $geoIp['ip'],
            'plataforma' => $agent->platform(),
            'Direccion' => $deviceLocation['address'],
            'Mapa' => $deviceLocation['map'],
            'Coordenadas' => $deviceLocation['coordinates'],
        );

        $request['device'] = json_encode($device);

        return $next($request);
    }
}
