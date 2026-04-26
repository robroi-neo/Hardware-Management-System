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
        $suppliers = [
            [
                'supplier_name' => 'TechWorks Inc',
                'contact_person' => 'John Smith',
                'company_address' => '123 Industrial Ave, Tech City, TC 12345',
                'contact_number' => '+1-800-TECH-123',
                'contact_email' => 'sales@techworks.com',
                'status' => 'active',
            ],
            [
                'supplier_name' => 'Global Electronics Ltd',
                'contact_person' => 'Maria Garcia',
                'company_address' => '456 Commerce Boulevard, Trade Port, TP 54321',
                'contact_number' => '+1-800-GLOBAL-EL',
                'contact_email' => 'orders@globalelec.com',
                'status' => 'active',
            ],
            [
                'supplier_name' => 'Prime Hardware Distributors',
                'contact_person' => 'David Chen',
                'company_address' => '789 Enterprise Road, Supply City, SC 67890',
                'contact_number' => '+1-888-PRIME-HD',
                'contact_email' => 'procurement@primehw.com',
                'status' => 'active',
            ],
            [
                'supplier_name' => 'FastShip Components',
                'contact_person' => 'Sarah Johnson',
                'company_address' => '321 Logistics Way, Distribution Zone, DZ 11223',
                'contact_number' => '+1-855-FAST-SHIP',
                'contact_email' => 'support@fastship.com',
                'status' => 'active',
            ],
            [
                'supplier_name' => 'Reliable Supply Co',
                'contact_person' => 'Robert Williams',
                'company_address' => '654 Trade Center, Warehouse District, WD 33445',
                'contact_number' => '+1-800-RELIABLE',
                'contact_email' => 'sales@reliablesupply.com',
                'status' => 'active',
            ],
            [
                'supplier_name' => 'Vintage Tech Supplies',
                'contact_person' => 'Emily Brown',
                'company_address' => '987 Heritage Plaza, Old Town, OT 55667',
                'contact_number' => '+1-844-VINTAGE-TS',
                'contact_email' => 'info@vintagetech.com',
                'status' => 'inactive',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}

