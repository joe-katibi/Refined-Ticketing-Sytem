<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserCategory extends Model
{
    use HasFactory;
    protected $fillable=[
      'user_id',
      'category_id',
      'created_by',
      'supervisor_id'


  ];
  protected $casts = [
      'created_at' => 'datetime:d-M-Y',
  ];

  public static function userCategory()
  {
      $userCategory = DB::table('user_categories')->orderBy('id', 'asc')->get();
      return $userCategory;
  }

  public function category()
  {
       return $this->belongsTo(Category::class , 'category_id' , 'id');
   }
}
