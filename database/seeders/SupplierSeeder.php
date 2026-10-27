<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fictional suppliers for the demo. Emails use the reserved .example domain
        // and phone numbers are obviously fake.
        $suppliers = [
            ['Davao Builders Supply', 'Carlo Villanueva', 'J.P. Laurel Ave, Davao City', '09000000101', 'sales@davaobuilders.example', 'active'],
            ['Mindanao Motor Parts Trading', 'Grace Dizon', 'Apokon Rd, Tagum City', '09000000102', 'orders@mindanaomotor.example', 'active'],
            ['Southern Tiles & Ceramics Depot', 'Paolo Sison', 'National Highway, Panabo City', '09000000103', 'sales@southerntiles.example', 'active'],
            ['Tagum Steel and Cement Center', 'Maricel Gomez', 'Rizal St, Tagum City', '09000000104', 'steel@tagumsteel.example', 'active'],
            ['Island Paints Distribution', 'Dennis Lim', 'Lanang, Davao City', '09000000105', 'orders@islandpaints.example', 'active'],
            ['Panabo Pipe & Electrical', 'Rowena Tan', 'Quezon St, Panabo City', '09000000106', 'info@panabopipe.example', 'inactive'],
        ];

        $suppliers = array_map(fn ($s) => [
            'supplier_name' => $s[0],
            'contact_person' => $s[1],
            'company_address' => $s[2],
            'contact_number' => $s[3],
            'contact_email' => $s[4],
            'status' => $s[5],
        ], $suppliers);

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}

