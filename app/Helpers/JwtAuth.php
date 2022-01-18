<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Suppport\Facades\DB;
use App\User;

class JwtAuth{

    public $key;

    public function __construct() {
        $this->key = 'esto_es_una_clave_super_secreta-99887766'; //Es la clave con la que se genera el token (hash)
    }




    public function signup($email, $password, $getToken = null){
        //Buscar si existe el usuario por sus credenciales
        $user = User::where([
            'email'     => $email,
            'password'  => $password
        ])->first();  
        //Comprobar si son correctas
        $singup = false;
        if(is_object($user)){
            $singup = true;
        }
        //Generarl el token con los datos del usuario identificado
        if ($singup) {
            $token = array(
                'sub'       => $user->id,  //En JWT hace referencia al ID del registro, en este caso del usuario
                'email'     => $user->email,
                'name'      => $user->name,
                'surname'   => $user->surname,
                'description' => $user->description,
                'image'     => $user->image,  
                'iat'       => time(),  //iat en JWT es la hora en la que se creo el token
                'exp'       => time() + (7 * 24 * 60 * 60),  //exp en JWT es la hora en la que va a caducar o expirar el token, en este caso caduca en una semana ( 7dias * 24horas * 60min * 60seg)
            );
            $jwt = JWT::encode($token, $this->key, 'HS256'); //Genera el token en si cono los datos del usuario que le asignamos arriba         
            $decoded = JWT::decode($jwt, $this->key, ['HS256']); //Decodifico el token
            //Devolver los datos decodificados o el token en funcion de una parametro $getToken
            if (is_null($getToken)) {
                $data =  $jwt;
            }else {
                $data =  $decoded;
            }
        }else {
            $data = array(
                'status'    => 'error',
                'message'   => 'Login incorrecto'
            );
        }      
        return  $data;
    }




    //Compruebo si el TOKEN es correcto
    public function checkToken($jwt, $getIdentity = false){
        $auth = false;

        try{
            $jwt =str_replace('"', '', $jwt);  //Le saca las comillas dobles por si el Token las trae
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        }catch(\UnexpectedValueExceptio $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }
        
        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        }else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        } 
        
        return $auth;
    }
}


?>