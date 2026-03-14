<?php
namespace Modules\Escalations\Entities;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $fillable = [
        'sub_category_name', 'category_id', 'status', 'created_by', 'edited_by'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
