<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Requirement;

class RecalculateRequirementCounts extends Command
{
    protected $signature = 'requirements:recalculate-counts';
    protected $description = 'Recalculate paid and unpaid counts for all requirements';

    public function handle()
    {
        $requirements = Requirement::all();
        
        foreach ($requirements as $requirement) {
            $requirement->recalculateCounts();
            $this->info("Updated requirement: {$requirement->title} - Paid: {$requirement->paid}, Unpaid: {$requirement->unpaid}");
        }
        
        $this->info('All requirement counts have been recalculated.');
    }
}