<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
      'title','content', 'category_id', 'image'
  ];

    //Relación de muchos a uno, muchos post pueden ser creados por el mismo usuario
    public function user(){
      return $this->belongsTo('App\User', 'user_id');
    }

    //Relación de muchos a uno, muchos post pertenecen a una misma categotia
    public function category(){
      return $this->belongsTo('App\Category', 'category_id');
    }

}
