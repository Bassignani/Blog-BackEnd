<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except'=>['index','show']]);  //Indico en el constructor cuales son los metodos que yo no quero que utilisen el middleware
    }                                                                 //Osea que lo usaran todos los metodos menos los seleccionados en 'except'

    public function index(){
        $categories = Category::all();

        return response()->json([
            'code'      => 200,
            'status'    => 'success',
            'categories'    => $categories
        ]);
    } 


    public function show($id){
        $category = Category::find($id);

        if (is_object($category)) {
           $data = array(
               'code'       => 200,
               'status'     => 'success',
               'category'   => $category
           ); 
        }else{
            $data = array(
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'La categoria no existe'
            ); 
        }
        return response()->json($data,$data['code']);
    }




    public function store(Request $request){
        //Recoger los datos por POST
        $json = $request->input('json',null);
        $params_array = json_decode($json, true);

        if(!is_null($params_array)){
            //Validar los datos
            $validate = Validator::make($params_array, [
                'name' => ['required'],
            ]);

            //Guardar la categoria 
            if ($validate->fails()) {
                $data = [
                    'code'  => 400,
                    'status' => 'error',
                    'mesagge' => 'No se ha guardado la categoria',
                    'errors'  => $validate->errors()
                ];
            }else {
                $category = new Category();
                $category->name =$params_array['name'];
                $category->save();

                $data = [
                    'code'      => 200,
                    'status'    => 'success',
                    'category'  =>  $category,
                    'mesagge'   => 'La categoria se guardado correctamente'
                ];
            }
        }else{
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'No has enviado ninguna categoria'
            ];
        }
        //Devolver los resultados
        return response()->json($data, $data['code']);
    }


    public function update($id, Request $request){
        //recoger los datos por post
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);
        if(!empty($params_array)){
            //Validar los datos
            $validate = Validator::make($params_array,[
                'name'  => ['required']
            ]);
            //Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);
            //Actualizar el registro
            $category = Category::where('id', $id)->update($params_array);
            $data = [
                'code'      => 200,
                'status'    => 'success',
                'category'   => $params_array
            ];

        }else{
            $data = [
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'No has enviado ninguna categoria'
            ];
        }
        //devolver los datos
        return response()->json($data, $data['code']);
    }
}
