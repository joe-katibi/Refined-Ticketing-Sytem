<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $fillable=[
      'country_name',
      'country_status'


  ];
  protected $casts = [
      'created_at' => 'datetime:d-M-Y'
  ];
}
