<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class SsoHandshakeController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = $request->input('token');
        $redirectUrl = $request->input('redirect', '/dashboard');

        if (!$token) {
            abort(400, 'Token missing');
        }

        // 1. Buscar y Validar Token en Base Maestra
        $ssoRecord = DB::connection('users')->table('sso_tokens')
            ->where('token', $token)
            ->first();

        // Validaciones de seguridad
        if (!$ssoRecord) {
            abort(403, 'Invalid Token');
        }

        if (Carbon::parse($ssoRecord->expires_at)->isPast()) {
            abort(403, 'Token Expired');
        }

        // 2. Encontrar usuario local
        $localUser = User::where('main_user_id', $ssoRecord->user_id)->first();

        if (!$localUser) {
            abort(403, 'User not provisioned in this application');
        }

        // 3. Login Real (Persistente con Sesión Laravel)
        Auth::login($localUser);

        // 4. Limpieza (Evitar Replay Attack)
        DB::connection('users')->table('sso_tokens')
            ->where('id', $ssoRecord->id)
            ->delete();

        // 5. Redirección al destino deseado
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => $redirectUrl]);
        }

        return redirect($redirectUrl);
    }
}
