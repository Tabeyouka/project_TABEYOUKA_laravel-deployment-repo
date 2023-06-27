<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureUniqueID
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $validated = $request->validate([
            'id' => 'required'
        ]);

        $exists = User::where('id', $validated['id'])->exists();
        
        if(!$exists) {
            return response()->json(['message' => 'User is already exists'], 422);
        }
        return $next($request);
    }
}
