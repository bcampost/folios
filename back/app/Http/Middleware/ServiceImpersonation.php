<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ServiceImpersonation
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Validar la API Key del Servicio (Seguridad de Red)
        $serviceKey = $request->header('X-Service-Key');
        $validKey = config('services.crm_integration.key'); // Definir en config/services.php

        if (!$serviceKey || $serviceKey !== $validKey) {
            return response()->json(['message' => 'Unauthorized Service Key'], 401);
        }

        // 2. Impersonación opcional
        $mainUserId = $request->header('X-Impersonate-User-Id');

        if ($mainUserId) {
            $localUser = User::where('main_user_id', $mainUserId)->first();

            if (!$localUser) {
                return response()->json(['message' => 'Local user mapping not found'], 403);
            }

            Auth::onceUsingId($localUser->id);
        }

        return $next($request);
    }
}
