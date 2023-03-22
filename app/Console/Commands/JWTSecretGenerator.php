<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class JWTSecretGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:generate-secret
        {--skip-if-present : Skip generating key if key exists.}
        {--f|force : Skip confirmation when overwriting an existing key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JWT secret Key used in signing JWT tokens';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $key = \Str::random(60);

        $envPath = $this->laravel->basePath('.env');

        if (file_exists($envPath) === false) {
             $this->error(".env file Is Not Available!");

            return;
        }

        if (\Str::contains(file_get_contents($envPath), 'JWT_SECRET') === false) {
            // Appends jwt secret to the end of .env file
            file_put_contents($envPath, PHP_EOL."JWT_SECRET=$key".PHP_EOL, FILE_APPEND);
        } else {

            if ($this->option('skip-if-present') || ! $this->isConfirmed()) {
                $this->comment('Secret key already exists. Skipping...');

                return;
            }

            // Update jwt secret entry on the .env
            file_put_contents($envPath, str_replace(
                "JWT_SECRET={$this->laravel['config']['jwt.secret']}",
                "JWT_SECRET=$key", file_get_contents($envPath)
            ));
        }

        $this->laravel['config']['jwt.secret'] = $key;

        $this->info("JWT secret '$key' set successfully.");
    }

    /**
     * Check if the modification is confirmed.
     *
     * @return bool
     */
    protected function isConfirmed(): bool
    {
        return $this->option('force') || $this->confirm(
                'This will invalidate all existing tokens. Are you sure you want to override the secret key?'
            );
    }
}
