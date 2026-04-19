<?php

namespace Modules\Base\Models;

use Carbon\Carbon;
use danielme85\LaravelLogToDB\Models\LogToDbCreateObject;
use Illuminate\Database\Eloquent\Model;

class LogDb extends Model
{
    use LogToDbCreateObject;

    protected $table = 'log';

    protected $appends = ['color'];

    public function scopeFilter($query, array $filters = [])
    {
        // Filter by level name
        $level = $filters['fLevel'] ?? null;
        if ($level) {
            $query->where('level_name', $level);
        }

        // Filter by date
        if ($date = ($filters['fDate'] ?? null)) {
            $query->when($date == 1, fn ($query) => $query->whereDate('created_at', Carbon::today()))
                ->when($date == 2, fn ($query) => $query->whereDate('created_at', Carbon::yesterday()))
                ->when($date == 3, fn ($query) => $query->whereDate('created_at', '>', Carbon::now()->subWeek()));
        }

        return $query;
    }

    public function getColorAttribute()
    {

        return match ($this->level_name) {
            'ERROR' => 'danger',
            'WARNING' => 'warning',
            default => 'primary'
        };
    }
}
