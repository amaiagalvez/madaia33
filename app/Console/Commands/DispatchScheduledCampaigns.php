<?php

namespace App\Console\Commands;

use App\Jobs\Messaging\DispatchCampaignJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class DispatchScheduledCampaigns extends Command
{
    protected $signature = 'campaigns:dispatch-scheduled';

    protected $description = 'Dispatch due scheduled campaigns';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $campaigns = Campaign::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($campaigns as $campaign) {
            $campaign->update(['status' => 'sending']);
            dispatch(new DispatchCampaignJob($campaign->id));
        }

        $this->info('Scheduled campaigns dispatched: ' . $campaigns->count());

        return self::SUCCESS;
    }
}
