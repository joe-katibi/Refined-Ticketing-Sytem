<?php
namespace Modules\Escalations\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Category extends Model
{
    protected $fillable = [
        'category_name', 'status', 'created_by', 'edited_by'
    ];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
