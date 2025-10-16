<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check the structure of the law_firm_profiles table
$columns = Schema::getColumnListing('law_firm_profiles');
echo "Columns in law_firm_profiles table:\n";
print_r($columns);

// Check if the show_office_address column exists
$hasColumn = Schema::hasColumn('law_firm_profiles', 'show_office_address');
echo "\nDoes show_office_address column exist? " . ($hasColumn ? 'Yes' : 'No') . "\n";

// Show a sample record
$profile = DB::table('law_firm_profiles')->first();
echo "\nSample law_firm_profile record:\n";
print_r($profile); 