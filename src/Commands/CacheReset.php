<?php

namespace Ghustavh97\Guardian\Commands;

use Illuminate\Console\Command;
use Ghustavh97\Guardian\GuardianRegistrar;

class CacheReset extends Command
{
    protected $signature = 'permission:cache-reset';

    protected $description = 'Reset the permission cache';

    public function handle()
    {
        if (app(GuardianRegistrar::class)->forgetCachedPermissions()) {
            $this->info('Permission cache flushed.');
        } else {
            $this->error('Unable to flush cache.');
        }
    }
}
