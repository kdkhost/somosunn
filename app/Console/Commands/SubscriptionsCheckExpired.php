<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionsCheckExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for expired subscriptions and updates user plan status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        // Find users with plan_expires_at < now() and plan_id != null
        // We assume plan_id is nullable or there is a default free plan.
        // Adjust logic based on system requirements:
        // If expired, maybe set plan_id to null (free) or a specific free plan ID.

        $expiredUsers = User::whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<', now())
            ->whereNotNull('plan_id') // Only check users who actually have a plan
            ->get();

        $count = 0;

        foreach ($expiredUsers as $user) {
            $this->expireUserPlan($user);
            $count++;
        }

        $this->info("Processed $count expired subscriptions.");
    }

    private function expireUserPlan(User $user)
    {
        $this->info("Expiring plan for User ID: {$user->id} ({$user->email})");

        try {
            // Logic to revert to free plan. 
            // We need to know what the 'free' plan ID is, or just nullify.
            // For now, let's assume nullifying plan_id makes them 'free' or 'no plan'.
            // OR find a plan with price 0?
            // Safer: Set plan_id to null and plan_expires_at to null.

            $user->update([
                'plan_id' => null, // Or set to a default free plan ID if exists
                'plan_expires_at' => null,
            ]);

            // Optional: Notify user
            // $user->notify(new PlanExpiredNotification());

            Log::info("User #{$user->id} plan expired via Cron.");

        } catch (\Exception $e) {
            Log::error("Failed to expire plan for User #{$user->id}: " . $e->getMessage());
        }
    }
}
