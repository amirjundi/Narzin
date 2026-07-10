<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $table = 'search_logs';

    protected $fillable = [
        'session_id', 'user_id', 'query', 'normalized_query',
        'results_count', 'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
