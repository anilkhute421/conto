<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class checkHeaderLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
         try{
    		\App::setLocale($_SERVER['HTTP_LANG']);
         }catch(\Exception $e){
            $response = [
                'success' => false,
                'message' => 'Incorrect lang key found.',
                'status'  => 201
            ];
            return response()->json($response,200);
         }

        return $next($request);
    }
}
