<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Criminal Law',
            'Family Law',
            'Personal Law',
            'Employment Law',
            'Tax Law',
            'Bankruptcy Law',
            'Personal Injury',
            'Environmental Law',
            'Intellectual Law'
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => str()->slug($category)
            ]);
        }
    }
} 