<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Appointment\Models\Appointment;
use Modules\Outages\Models\Outage;
use App\Traits\OptimizedQueries;

class TicketPhoto extends Model
{
    use HasFactory, OptimizedQueries;

    protected $fillable = [
        'ticket_id',
        'ticket_type',
        'appointment_id',
        'outage_id',
        'onu_photo',
        'atb_photo',
        'speed_test_photo',
        'fdt_photo',
        'mdu_photo',
        'pre_outage_photo',
        'post_outage_photo',
        'uploaded_by',
        'notes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the appointment that owns the photo (if appointment ticket)
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Get the outage that owns the photo (if outage ticket)
     */
    public function outage()
    {
        return $this->belongsTo(Outage::class, 'outage_id');
    }

    /**
     * Get the user who uploaded the photo
     */
    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by', 'id');
    }

    /**
     * Scope to get photos for appointment tickets
     */
    public function scopeAppointmentPhotos($query)
    {
        return $query->where('ticket_type', 'appointment');
    }

    /**
     * Scope to get photos for outage tickets
     */
    public function scopeOutagePhotos($query)
    {
        return $query->where('ticket_type', 'outage');
    }

    /**
     * Scope to get photos by ticket ID
     */
    public function scopeByTicketId($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Get appointment-specific photos (ONU, ATB, Speed Test)
     */
    public function getAppointmentPhotosAttribute()
    {
        return [
            'onu_photo' => $this->onu_photo,
            'atb_photo' => $this->atb_photo,
            'speed_test_photo' => $this->speed_test_photo,
        ];
    }

    /**
     * Get outage-specific photos (FDT, MDU, Pre/Post outage)
     */
    public function getOutagePhotosAttribute()
    {
        return [
            'fdt_photo' => $this->fdt_photo,
            'mdu_photo' => $this->mdu_photo,
            'pre_outage_photo' => $this->pre_outage_photo,
            'post_outage_photo' => $this->post_outage_photo,
        ];
    }

    /**
     * Get all non-null photos for the ticket
     */
    public function getAllPhotosAttribute()
    {
        $photos = [];
        
        if ($this->ticket_type === 'appointment') {
            $appointmentPhotos = $this->appointment_photos;
            foreach ($appointmentPhotos as $key => $photo) {
                if ($photo) {
                    $photos[$key] = $photo;
                }
            }
        } elseif ($this->ticket_type === 'outage') {
            $outagePhotos = $this->outage_photos;
            foreach ($outagePhotos as $key => $photo) {
                if ($photo) {
                    $photos[$key] = $photo;
                }
            }
        }
        
        return $photos;
    }
}
