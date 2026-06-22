<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Str;

class UnisexServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Manikür', 'category_id' => 6, 'price' => 1000.00, 'duration' => 40],
            ['name' => 'Pedikür', 'category_id' => 6, 'price' => 1000.00, 'duration' => 45],
            ['name' => 'Kaş Alma', 'category_id' => 7, 'price' => 150.00, 'duration' => 15],
            ['name' => 'Altın Oran Kaş Tasarımı', 'category_id' => 7, 'price' => 250.00, 'duration' => 30],
            ['name' => 'Klasik Cilt Bakımı', 'category_id' => 8, 'price' => 1500.00, 'duration' => 60],
            ['name' => 'TANURA C4 Cilt Bakımı', 'category_id' => 8, 'price' => 5000.00, 'duration' => 90],
            ['name' => 'Dudak Üstü', 'category_id' => 9, 'price' => 250.00, 'duration' => 15],
            ['name' => 'Boyun', 'category_id' => 9, 'price' => 400.00, 'duration' => 15],
            ['name' => 'Ense', 'category_id' => 9, 'price' => 400.00, 'duration' => 15],
            ['name' => 'Çene', 'category_id' => 9, 'price' => 500.00, 'duration' => 20],
            ['name' => 'Koltuk Altı', 'category_id' => 9, 'price' => 500.00, 'duration' => 20],
            ['name' => 'Bikini Bölgesi', 'category_id' => 9, 'price' => 500.00, 'duration' => 25],
            ['name' => 'Yüz', 'category_id' => 9, 'price' => 500.00, 'duration' => 30],
            ['name' => 'Sırt', 'category_id' => 9, 'price' => 1250.00, 'duration' => 45],
            ['name' => 'Göğüs', 'category_id' => 9, 'price' => 1250.00, 'duration' => 45],
            ['name' => 'Kol', 'category_id' => 9, 'price' => 1250.00, 'duration' => 40],
            ['name' => 'Bacak', 'category_id' => 9, 'price' => 1500.00, 'duration' => 50],
            ['name' => 'Tüm Vücut', 'category_id' => 9, 'price' => 2500.00, 'duration' => 120],
            ['name' => 'Kemer Üstü', 'category_id' => 9, 'price' => 2500.00, 'duration' => 90],
            ['name' => 'Vip Hizmeti (C Paketi)', 'category_id' => 5, 'price' => 1500.00, 'duration' => 60],
            ['name' => 'Masaj', 'category_id' => 10, 'price' => 1000.00, 'duration' => 60],
            ['name' => 'Kafa Masajı', 'category_id' => 10, 'price' => 1250.00, 'duration' => 45],
        ];

        foreach ($services as $s) {
            // Check if service already exists
            $exists = Service::where('branch_id', 1)
                ->where('name', $s['name'])
                ->where('gender_type', 'unisex')
                ->exists();

            if (!$exists) {
                Service::create([
                    'branch_id' => 1,
                    'category_id' => $s['category_id'],
                    'name' => $s['name'],
                    'slug' => Str::slug($s['name']),
                    'description' => $s['name'] . ' Hizmeti',
                    'duration_minutes' => $s['duration'],
                    'buffer_time' => 0,
                    'price' => $s['price'],
                    'discounted_price' => null,
                    'gender_type' => 'unisex',
                    'image' => null,
                    'is_popular' => false,
                    'is_featured' => false,
                    'is_active' => true,
                ]);
            }
        }
    }
}
