<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'address'];

    public function branchInventories()
    {
        return $this->hasMany(BranchInventory::class, 'branch_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function terminals()
    {
        return $this->hasMany(PosTerminal::class, 'branch_id');
    }
}

