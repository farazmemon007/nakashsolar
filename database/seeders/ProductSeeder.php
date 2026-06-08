<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'admin_or_user_id' => 1,
                'item_code' => 'SOL-001',
                'item_name' => 'Solar Panel 545W Mono PERC',
                'product_mode' => 'simple',
                'height' => null,
                'width' => null,
                'area' => null,
                'wholesale_price' => 21000.00,
                'retail_price' => 23000.00,
            ],
            [
                'admin_or_user_id' => 1,
                'item_code' => 'SOL-002',
                'item_name' => 'Hybrid Inverter 5kW',
                'product_mode' => 'simple',
                'height' => null,
                'width' => null,
                'area' => null,
                'wholesale_price' => 250000.00,
                'retail_price' => 265000.00,
            ],
            [
                'admin_or_user_id' => 1,
                'item_code' => 'SOL-003',
                'item_name' => 'Lithium-ion Battery 48V 100Ah',
                'product_mode' => 'simple',
                'height' => null,
                'width' => null,
                'area' => null,
                'wholesale_price' => 300000.00,
                'retail_price' => 320000.00,
            ],
            [
                'admin_or_user_id' => 1,
                'item_code' => 'SOL-004',
                'item_name' => 'Solar DC Cable 6mm (Per Meter)',
                'product_mode' => 'simple',
                'height' => null,
                'width' => null,
                'area' => null,
                'wholesale_price' => 150.00,
                'retail_price' => 180.00,
            ],
            [
                'admin_or_user_id' => 1,
                'item_code' => 'SOL-005',
                'item_name' => 'Galvanized Mounting Structure (Per Panel)',
                'product_mode' => 'simple',
                'height' => null,
                'width' => null,
                'area' => null,
                'wholesale_price' => 2500.00,
                'retail_price' => 3000.00,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['item_code' => $product['item_code']],
                $product
            );
        }
    }
}
