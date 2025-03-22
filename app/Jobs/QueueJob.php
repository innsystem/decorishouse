<?php

namespace App\Jobs;

class QueueJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobs = \DB::table('jobs')->get();
        $jobs_counts = count($jobs);

        \Log::info('QueueJob INIT :: ' . $jobs_counts);

        foreach ($jobs as $job) {
            \Artisan::call('queue:work --queue=' . $job->queue . ' --once');
        }
    }
}
