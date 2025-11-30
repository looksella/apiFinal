<?
//Middleware 
namespace App\Http\Middleware;

use Closure;

class CheckTokenExpiration
{
    public function handle($request, Closure $next)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && $token->expires_at && $token->expires_at->isPast()) {
            $token->delete();

            return response()->json([
                'message' => 'Expired Token'
            ], 401);
        }
        return $next($request);
    }
}

