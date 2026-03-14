<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Category extends Model
{
    use HasFactory;
    protected $fillable=[
      'category_name',
      'service_id',
      'category_status',


  ];
  protected $casts = [
      'created_at' => 'datetime:d-M-Y'
  ];

  public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_categories',
            'category_id',
            'user_id',
        );
    }
    public function userCategory()
    {
         return $this->belongsTo(UserCategory::class , 'category_id' , 'id');
     } 

     public function parameters()
{
    return $this->hasMany(Parameter::class, 'category_id');
}


}
