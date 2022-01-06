<?php

namespace Grnspc\Addresses\Console\Commands;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{

     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grnspc:migrate:addresses {--f|force : Force the operation to run when in production.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Grnspc Addresses Tables.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->alert($this->description);

        $path = config('grnspc.addresses.autoload_migrations') ?
            'vendor/grnspc/addresses/database/migrations' :
            'database/migrations/grnspc/addresses';

        if (file_exists($path)) {
            $this->call('migrate', [
                '--step' => true,
                '--path' => $path,
                '--force' => $this->option('force'),
            ]);
        } else {
            $this->warn('No migrations found! Consider publish them first: <fg=green>php artisan grnspc:publish:addresses</>');
        }

        $this->line('');
    }

}
