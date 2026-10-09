<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:dispatch-scheduled-campaigns')]
#[Description('Command description')]
class DispatchScheduledCampaigns extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
