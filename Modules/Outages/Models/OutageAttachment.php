<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Modules\Outages\Database\Factories\OutageAttachmentFactory;

class OutageAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'outage_attachments';

    protected $fillable = [
        'outage_id',
        'ticket_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'uploaded_by',
    ];

    protected $appends = ['file_url'];

    protected static function newFactory()
    {
        return OutageAttachmentFactory::new();
    }

    /**
     * Get the outage that owns the attachment.
     */
    public function outage()
    {
        return $this->belongsTo(Outage::class);
    }

    /**
     * Get the ticket that owns the attachment.
     */
    public function ticket()
    {
        return $this->belongsTo(OutageTicket::class);
    }

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the file icon based on file type.
     */
    public function getFileIcon()
    {
        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        
        $icons = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'jpg' => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'png' => 'fa-file-image',
            'gif' => 'fa-file-image',
            'txt' => 'fa-file-alt',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive',
            '7z' => 'fa-file-archive',
        ];

        return $icons[$extension] ?? 'fa-file';
    }

    /**
     * Get the human-readable file size.
     */
    public function getHumanReadableSize()
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Scope a query to only include attachments for a specific outage.
     */
    public function scopeForOutage($query, $outageId)
    {
        return $query->where('outage_id', $outageId);
    }

    /**
     * Scope a query to only include attachments for a specific ticket.
     */
    public function scopeForTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Delete the file from storage when the model is deleted.
     */
    protected static function booted()
    {
        static::deleted(function ($attachment) {
            if (Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
            }
        });
    }
}
