<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;

class VisitSession extends Model
{
    protected $table = 'visit_sessions';

    protected $fillable = [
        'session_id', 'user_id',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'referrer', 'landing_url', 'first_seen_at', 'last_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}
