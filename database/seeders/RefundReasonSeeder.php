<?php

namespace Database\Seeders;

use App\Models\RefundReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RefundReasonSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $reasons = [
            'Wrong item delivered',
            'Damaged item',
            'Customer changed mind',
            'Pricing error',
            'Other',
        ];

        foreach ($reasons as $name) {
            RefundReason::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}

