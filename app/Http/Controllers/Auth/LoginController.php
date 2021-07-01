<?php

namespace App\Http\Controllers\Auth;

use App\db_audit;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Classes\GeoLocation;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Torann\GeoIP\Facades\GeoIP;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    private $geoLocation;

    function authenticated(Request $request)
    {
        $agent = new Agent();
        $a = ($agent->isMobile() || $agent->isTablet()) ? 'Móvil' : 'Escritorio';
        $geoIp = GeoIP::getLocation($request->ip());

        $deviceLocation = $this->geoLocation->checkLocation($geoIp);


        $device = array(
            'Dispositivo' => $a,
            'Tipo' => $agent->device(),
            'Ip' => $geoIp['ip'],
            'plataforma' => $agent->platform(),
            'Direccion' => $deviceLocation['address'],
            'Mapa' => $deviceLocation['map'],
            'Coordenadas' => $deviceLocation['coordinates'],
        );

        $user = User::find(Auth::id());
        $audit = array(
            'created_at' => Carbon::now(),
            'id_user' => Auth::id(),
            'data' => json_encode(array(
                'name' => $user->name.' '.$user->last_name,
                'id' => $user->id,
                'email' => $user->email
            )),
            'event' => 'update',
            'device' => json_encode($device),
            'type' => 'Inicio de sesión'
        );

        db_audit::insert($audit);
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->geoLocation =  new GeoLocation();
        $this->middleware('guest')->except('logout');
    }
    public function logout(){
        Auth::logout();
        return redirect('login')->withCookie(cookie('forward_session', '', -1));
    }
}
