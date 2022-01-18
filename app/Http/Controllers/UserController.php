<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\User;

class UserController extends Controller
{

    public function register(Request $request){
        //Recoger los datos del usuario por POST
        $json = $request->input('json',null);
        $params = json_decode($json); //Me da un OBJETO con todos los elementos del json recibido
        $params_array = json_decode($json, true); //Me da un ARRAY con todos los elementos del json recibido

        if(!empty($params_array) && !empty($params)){
            //Limpiar datos (Limpia los espacios)
            $params_array = array_map('trim', $params_array);
            //Validar los datos
            $validate = Validator::make($params_array, [
                'name' => ['required','alpha'],
                'surname' => ['required','alpha'],
                'email' => ['required','email', 'unique:users'],
                'password' => ['required'],
            ]);
            if($validate->fails()){
                $data = array(
                    'status'  => 'error',
                    'code'    => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors'  => $validate->errors(),
                );
            }else{
                //Cifrar la contraseña
                $pwd = hash('sha256', $params->password);
                //Crear el uausario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                $user->save();
                $data = array(
                    'status'  => 'success',
                    'code'    => '200',
                    'message' => 'El usuario se ha creado correctamente',
                );
            }
        }else{
            $data = array(
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Los datos enviados no son correctos',
            );
        }
        return response()->json($data, $data['code']);
    }




    public function login(Request $request){
        $JwtAuth = new \JwtAuth();
        //Recibir los datps por POST
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);
        //Validar los datos
        $validate = Validator::make($params_array, [
            'email' => ['required','email'],
            'password' => ['required']
        ]);
        if($validate->fails()){
            $signup = array(
                'status'  => 'error',
                'code'    => 404,
                'message' => 'El usuario no se ha podido logear',
                'errors'  => $validate->errors(),
            );
        }else{
            //Cifrar la contraseña
            $pwd = hash('sha256', $params->password);
            //$pwd = password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);

            //Devolver TOKEN o datos
            if (!empty($params->getToken)) {
                $signup = $JwtAuth->signup($params->email,$pwd,true);
            }else{
                $signup = $JwtAuth->signup($params->email,$pwd);
            }
        }
        return response()->json($signup);
    }



    public function update(Request $request){
        $user = new User();
        //Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        //Recoger los datos por post
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);
        if ($checkToken && !empty($params_array)) {
            //Sacar usuario identificado
            $user = $jwtAuth->checkToken($token,true);
            //Validar los datos
            $validate = Validator::make($params_array, [
                'name' => ['required','alpha'],
                'surname' => ['required','alpha'],
                'email' => ['required','email', 'unique:users','$user->sub']//Sub == Id
            ]);
            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);
            //Actualizar datos
            $user_update = User::where('id',$user->sub)->update($params_array);
            //Devolver array con resultado
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'messaje'   => $user,
                'change'    => $params_array
            );
        }else {
            //Mensaje de error
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'messaje'   => 'El usuario no esta identificado'
            );

        }
        return response()->json($data, $data['code']);
    }




    public function upload(Request $request){
        //Recoger los datos de la peticion
        $image = $request->file('file0');
        //Validar la imagen
        $validate = Validator::make($request->all(), [
           'file0' => ['required','image','mimes:jpg,jpeg,png,gif']
        ]);
        //Guardar la imagen
        if(!$image || $validate->fails()) {
            $data = array(
                'code'      => '400',
                'status'    => 'error',
                'image'     => 'Error al subir imagen'
            );
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));
            $data = array(
                'code'      => '200',
                'status'    => 'success',
                'image'     => $image_name
            );
        }
        return response()->json($data,$data['code']);
    }



    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return  new Response($file,200);
        }else{
            $data = array(
                'code'      => '404',
                'status'    => 'error',
                'image'     => 'El fichero no existe'
            );
            return response()->json($data,$data['code']);
        }
    }



    public function detail($id){
        $user = User::find($id);
        if (is_object($user)) {
            $data = array(
                'code'      => '200',
                'status'    => 'success',
                'user'      => $user
            );
        }else{
            $data = array(
                'code'      => '404',
                'status'    => 'error',
                'image'     => 'El ususarion no existe'
            );
        }
        return response()->json($data,$data['code']);
    }



}
