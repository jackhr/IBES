<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxiRequest extends Model
{
    protected $table = 'taxi_requests';

    protected $primaryKey = 'request_id';

    public $timestamps = false;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'pickup_location',
        'dropoff_location',
        'pickup_time',
        'number_of_passengers',
        'special_requirements',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_time' => 'datetime',
            'number_of_passengers' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
