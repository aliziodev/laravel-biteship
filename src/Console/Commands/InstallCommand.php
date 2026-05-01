<?php

namespace Aliziodev\Biteship\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'biteship:install';

    protected $description = 'Install and configure the Biteship package';

    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=yellow>Installing Biteship...</>');
        $this->newLine();

        // 1. Publish config
        $this->line('Publishing config...');
        $this->callSilently('vendor:publish', [
            '--tag' => 'biteship-config',
            '--force' => false,
        ]);
        $this->line('  <fg=green>✓</> Config published → <comment>config/biteship.php</comment>');
        $this->newLine();

        // 2. Optional DB layer
        $useDb = $this->confirm('Do you want to use the optional database layer? (HasBiteship trait)', true);

        if ($useDb) {
            $this->line('Publishing migration...');
            $this->callSilently('vendor:publish', [
                '--tag' => 'biteship-migrations',
                '--force' => false,
            ]);
            $this->line('  <fg=green>✓</> Migration published → <comment>database/migrations/</comment>');
            $this->line('  <fg=yellow>!</> Run <comment>php artisan migrate</comment> to create the biteship_orders table.');
            $this->newLine();
        }

        // 3. Check .env
        $this->checkEnv();

        // 4. Next steps
        $this->newLine();
        $this->info('Biteship installed successfully!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  <fg=white>1.</> Set <comment>BITESHIP_API_KEY</comment> in your <comment>.env</comment>');

        if ($useDb) {
            $this->line('  <fg=white>2.</> Add <comment>use HasBiteship;</comment> to your Order model');
            $this->line('  <fg=white>3.</> Set your webhook URL in the Biteship dashboard:');
            $this->line('     <comment>'.url(config('biteship.webhook.path', 'biteship/webhook')).'</comment>');
            $this->line('  <fg=white>4.</> (Optional) Set <comment>BITESHIP_WEBHOOK_SIGNATURE_KEY</comment> and');
            $this->line('     <comment>BITESHIP_WEBHOOK_SIGNATURE_SECRET</comment> matching the Headers');
            $this->line('     configured in Biteship dashboard → Webhook Settings.');
        } else {
            $this->line('  <fg=white>2.</> Set your webhook URL in the Biteship dashboard:');
            $this->line('     <comment>'.url(config('biteship.webhook.path', 'biteship/webhook')).'</comment>');
            $this->line('  <fg=white>3.</> (Optional) Set <comment>BITESHIP_WEBHOOK_SIGNATURE_KEY</comment> and');
            $this->line('     <comment>BITESHIP_WEBHOOK_SIGNATURE_SECRET</comment> matching the Headers');
            $this->line('     configured in Biteship dashboard → Webhook Settings.');
        }

        $this->newLine();
        $this->line('Docs: <href=https://github.com/aliziodev/laravel-biteship>github.com/aliziodev/laravel-biteship</>');
        $this->newLine();

        return self::SUCCESS;
    }

    private function checkEnv(): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->warn('  .env file not found. Make sure to set BITESHIP_API_KEY.');

            return;
        }

        $env = file_get_contents($envPath);

        if (str_contains($env, 'BITESHIP_API_KEY')) {
            $this->line('  <fg=green>✓</> <comment>BITESHIP_API_KEY</comment> found in .env');
        } else {
            $this->warn('  BITESHIP_API_KEY is not set in your .env');
            $this->line('  Add the following to your <comment>.env</comment>:');
            $this->line('  <comment>BITESHIP_API_KEY=biteship_live.xxxxxxxxxxxx</comment>');
        }
    }
}
