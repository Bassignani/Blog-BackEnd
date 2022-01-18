<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
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
        //Comprobar si el usuario esta identificado
        $token = $request->header('Authorization',null);

        if(is_null($token)){
            //Mensaje de error
            $data = array(
                'code'      => 400,
                'status'    => 'error',
               'messaje'   => 'El usuario no esta identificado'
            );
            return response()->json($data,$data['code']);
        }else{
            $jwtAuth = new \JwtAuth();
            $checkToken = $jwtAuth->checkToken($token);
        
            if($checkToken){
            return $next($request);
            }else{
                //Mensaje de error
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                   'messaje'   => 'El usuario no esta identificado'
                );
                return response()->json($data,$data['code']);
            }  
        }

/*
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
    
        if($checkToken){
        return $next($request);
        }else{
            //Mensaje de error
            $data = array(
                'code'      => 400,
                'status'    => 'error',
               'messaje'   => 'El usuario no esta identificado'
            );
            return response()->json($data,$data['code']);
        }
*/

    }    
}
