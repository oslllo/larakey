<?php

namespace Ghustavh97\Larakey\Commands;

use Illuminate\Console\Command;
use Ghustavh97\Larakey\Padlock\Cache;

class CacheReset extends Command
{
    /**
     * Command signature.
     *
     * @var string
     */
    protected $signature = 'permission:cache-reset';

    /**
     * Command description.
     *
     * @var string
     */
    protected $description = 'Reset the permission cache';

    /**
     * Command handle function.
     *
     * @return void
     */
    public function handle()
    {
        if (app(Cache::class)->flushCache()) {
            $this->info('Permission cache flushed.');
        } else {
            $this->error('Unable to flush cache.');
        }
    }
}
