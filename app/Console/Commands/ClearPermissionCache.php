<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class ClearPermissionCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the permission cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Clear the permission cache
        Cache::forget('spatie.permission.cache');

        // Also call the forgetCachedPermissions method on the registrar
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Permission cache cleared successfully.');

        return Command::SUCCESS;
    }
}
