<?php

namespace Ghustavh97\Larakey\Commands;

use Illuminate\Console\Command;
use Ghustavh97\Larakey\LarakeyRegistrar;

class CacheReset extends Command
{
    protected $signature = 'permission:cache-reset';

    protected $description = 'Reset the permission cache';

    public function handle()
    {
        if (app(LarakeyRegistrar::class)->flushCache()) {
            $this->info('Permission cache flushed.');
        } else {
            $this->error('Unable to flush cache.');
        }
    }
}
