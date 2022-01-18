<?php

namespace App\Http\Controllers;

use App\Post;
use App\Category;   //Esta de aca
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
Use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except' => [
            'index', 
            'show', 
            'getImage', 
            'getPostsByCategory', 
            'getPostsByUser'
            ]]);
    }


    public function index(){
        $posts = Post::all()->load('category');  //Me saca todos los post con un objeto adjunto de la categoria que corresponde a cada post
        $data = [
            'code'      => 200,
            'status'    => 'success',
            'posts'     => $posts
        ];
        return response()->json($data, $data['code']);
    }



    public function show($id){
        $post = Post::find($id)->load('category','user');
        if (is_object($post)) {
            $data = [
                'code'      => 200,
                'status'    => 'success',
                'post'     => $post
            ]; 
        }else {
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'La entrada no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }


    public function store(Request $request){
        //Recoger datos por post
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array =json_decode($json,true);
        if (!empty($params_array)) {
            //Conseguir usuario identificado
            $user =$this->getIdentyti($request);
            //Validar los datos
            $validate = Validator::make($params_array, [
                'title'         => ['required'],
                'content'       => ['required'],
                'category_id'   => ['required']
            ]);
            //Guardar el post
            if ($validate->fails()) {
                $data = [
                    'code'      => 400,
                    'status'    => 'error',
                    'mesagge' => 'No se ha guardado el post',
                    'errors'  => $validate->errors()
                ];
            }else{
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params_array['category_id'];
                $post->title = $params_array['title'];
                $post->content = $params_array['content'];
                if (empty($params_array['image'])) {
                    $post->image = null;
                }else {
                    $post->image = $params_array['image'];
                }
                $post->save();
                $data = [
                    'code'      => 200,
                    'status'    => 'success',
                    'post'      =>  $post,
                    'mesagge'   => 'El post se guardado correctamente'
                ];
            }      
        }else{
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'Error al enviar los datos'
            ];
        }
        //Devolver la respuesta
        return response()->json($data, $data['code']);
    } 



    public function update($id, Request $request){
        //Recoger los datos por POST
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);
        if(!empty($params_array)){
            //Validar los datos
            $validate = Validator::make($params_array, [
                'title'         => ['required'],
                'content'       => ['required'],
                'category_id'   => ['required']
            ]);
            if ($validate->fails()) {
                $data = [
                    'code'      => 400,
                    'status'    => 'error',
                    'mesagge' => 'No se ha Actualizado el post',
                    'errors'  => $validate->errors()
                ];
            }else{
                //Eliminar lo que no queremos actualizar
                unset($params_array['id']);
                unset($params_array['created_at']);
                unset($params_array['updated_at']);
                unset($params_array['user']);
                unset($params_array['category']);
                //Conseguir usuario identificado
                $user =$this->getIdentyti($request);
                //Buscar el registro
                $post = Post::where('id', $id)  
                    ->where('user_id',$user->sub)
                    ->first();   //Saca el poste en el cual coincida el id del post y que conincida el usuario que lo creo y el usuario identificado
                    if (is_null($post)) {
                        $data = [
                            'code'      => 404,
                            'satatus'   => 'error',
                            'message'   => 'No se ha encontrado el post o no eres el creador del mismo'
                        ];
                    }else{    
                        //Actualizar el registro
                        $post = Post::where('id',$id)->update($params_array);

                        //Devolver el resultado
                        $data = [
                            'code'      => 200,
                            'status'    => 'success',
                            'mesagge'   => 'El post se actualizo correctamente'
                        ];
                    }
            }  
        }else {
            $data = [
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Enviar correctamente los datos para actualizacion'
            ];
        }  
        return response()->json($data, $data['code']);
    }





    public function destroy($id, Request $request){
        //Conseguir usuario identificado
        $user =$this->getIdentyti($request);
        //Conseguir el post
        $post = Post::where('id', $id)  
                    ->where('user_id',$user->sub)
                    ->first();   //Saca el poste en el cual coincida el id del post y que conincida el usuario que lo creo y el usuario identificado
        if (is_null($post)) {
            $data = [
                'code'      => 404,
                'satatus'   => 'error',
                'message'   => 'No se ha encontrado el post o no eres el creador del mismo'
            ];
        }else{
            //Borrarlo
            $post->delete();
            $data = [
                'code'      => 200,
                'satatus'   => 'success',
                'post'      => $post,
                'message'   => 'El post se ha borrasdo correctamente'
            ];
        }
        //Devolver el resultado
        return response()->json($data, $data['code']);
    }




    public function upload(Request $request){
        //Recoger la imagen de la peticoin
        $image = $request->file('file0');
        //validar la imagen
        $validate = Validator::make($request->all(), [
            'file0' => ['required', 'image', 'mimes:jpg,jpeg,png,gif']
        ]);
        //Guardar la imagen en un disco
        if ($validate->fails()) {
            $data = [
                'code'      => 400,
                'status'    => 'error',
                'messge'    => 'Error al subir la imagen',
                'error'     => $validate->errors()
            ];
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));
            $data = [
                'code'      => 200,
                'status'    => 'succes',
                'image'     => $image_name
            ];
        }
        //Devolver datos
        return response()->json($data,$data['code']);
    }



    public function getImage($filename){
        //Comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);
        if ($isset) {
            //Consegui la imagen
            $file = \Storage::disk('images')->get($filename);
            //Devolver la imagen  
            return new Response($file,200);
        }else{
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'   =>  'No se encontro la imagen'
            ];
        }
        //Mostrar el error
        return response()->json($data,$data['code']);
    }


    public function getPostsByCategory($id){
        $category = Category::where('id',$id);    
        if (!is_null($category)) {
            $category = Post::where('category_id',$id)->first(); 
            if (!is_null($category)) {
                $posts = Post::where('category_id',$id)->get();
                $data = [
                    'code'      => 200,
                    'status'    => 'success',
                    'posts'      => $posts
                ]; 
            } else {
                $data = [
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'No hay post en esta categoria' 
                ]; 
            }                   
        }else {
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'La categoria no existe'
            ];
        }
        return response()->json($data,$data['code']);
    }



    public function getPostsByUser($id){
        $user = Post::where('user_id',$id)->first();
        if (!is_null($user)) {
            $posts = Post::where('user_id',$id)->get();
            $data = [
                'code'      => 200,
                'status'    => 'success',
                'posts'      => $posts
            ]; 
        }else {
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'El usuario no existe'
            ];
        }
        return response()->json($data,$data['code']);
    }




    private function getIdentyti($request){ //Al ser un metodo PRIVADO solo se puede usar dentro de esta clase
        //Para sacar el usuario identificado
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization');
        $user = $jwtAuth->checkToken($token, true);  //En esta variable obtengo el usuario
        return $user;
    }

}
