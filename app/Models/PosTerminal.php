<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTerminal extends Model
{
    protected $table = 'pos_terminals';

    protected $fillable = [
        'terminal_id',
        'terminal_name',
        'branch_id',
    ];

    protected $casts = [
        'terminal_id' => 'integer',
        'branch_id' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}

