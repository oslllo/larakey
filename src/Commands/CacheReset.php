<?php

namespace Ghustavh97\Larakey\Commands;

use Illuminate\Console\Command;
use Ghustavh97\Larakey\Padlock\Cache;

class CacheReset extends Command
{
    protected $signature = 'permission:cache-reset';

    protected $description = 'Reset the permission cache';

    public function handle()
    {
        if (app(Cache::class)->flushCache()) {
            $this->info('Permission cache flushed.');
        } else {
            $this->error('Unable to flush cache.');
        }
    }
}
