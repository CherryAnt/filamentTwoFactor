<?php

namespace CherryAnt\FilamentTwoFactor\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twofactor:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install script for Two Factor.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('***************************');
        $this->line('*     FILAMENT TWOFACTOR   *');
        $this->line('***************************');
        $this->newLine(2);
        $this->callSilent('vendor:publish', [
            '--tag' => 'filament-two-factor-migrations',
        ]);
        $this->call('migrate');
        $this->info('You may now enable 2FA by appending ->enableTwoFactorAuthentication() to TwoFactorPlugin::make(). See the docs for more info.');
        $this->newLine();

        return static::SUCCESS;
    }
}
