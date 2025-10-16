<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copy values from the old 'label' field to the new 'case_label' field
        $validLabels = ['high_priority', 'medium_priority', 'low_priority'];
        
        // Find all cases with a valid label and update their case_label
        $cases = DB::table('legal_cases')
            ->whereIn('label', $validLabels)
            ->whereNull('case_label')
            ->get();
            
        foreach ($cases as $case) {
            DB::table('legal_cases')
                ->where('id', $case->id)
                ->update(['case_label' => $case->label]);
        }
        
        // Log the number of updated records
        $count = count($cases);
        \Illuminate\Support\Facades\Log::info("Backfilled case_label values for $count legal cases");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data migration, no need to reverse it
    }
};
