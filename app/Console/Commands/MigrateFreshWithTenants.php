<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Facades\Tenancy;

class MigrateFreshWithTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fresh-with-tenants {--database= : The database connection to use}
                            {--drop-views : Drop all tables and views}
                            {--drop-types : Drop all tables and types (Postgres only)}
                            {--force : Force the operation to run when in production}
                            {--path=* : The path(s) to the migrations files to be executed}
                            {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                            {--schema-path= : The path to a schema dump file}
                            {--seed : Indicates if the seed task should be re-run}
                            {--seeder= : The class name of the root seeder}
                            {--step= : Force the migrations to be run so they can be rolled back individually}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables and re-run all migrations (including tenant databases)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->info('Dropping all tenant databases...');
        $this->deleteTenantDatabases();

        $this->info('Running migrate:fresh on central database...');
        $exitCode = Artisan::call('migrate:fresh', $this->getOptions());
        
        if ($exitCode !== 0) {
            $this->error('Central database migration failed.');
            return $exitCode;
        }

        $this->info('Central database migration completed successfully.');
        return 0;
    }

    /**
     * Delete all tenant databases.
     */
    protected function deleteTenantDatabases()
    {
        try {
            // Get all tenants from the central database
            $tenants = DB::table('tenants')->get();
            
            foreach ($tenants as $tenant) {
                $databaseName = $tenant->id;
                
                try {
                    // Drop the tenant database
                    DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
                    $this->line("Dropped tenant database: {$databaseName}");
                } catch (\Exception $e) {
                    $this->warn("Failed to drop tenant database {$databaseName}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->warn('Failed to retrieve tenants: ' . $e->getMessage());
        }
    }

    /**
     * Get the options to pass to migrate:fresh.
     */
    protected function getOptions()
    {
        $options = [];
        
        if ($this->option('database')) {
            $options['--database'] = $this->option('database');
        }
        
        if ($this->option('drop-views')) {
            $options['--drop-views'] = true;
        }
        
        if ($this->option('drop-types')) {
            $options['--drop-types'] = true;
        }
        
        if ($this->option('force')) {
            $options['--force'] = true;
        }
        
        if ($this->option('path')) {
            $options['--path'] = $this->option('path');
        }
        
        if ($this->option('realpath')) {
            $options['--realpath'] = true;
        }
        
        if ($this->option('schema-path')) {
            $options['--schema-path'] = $this->option('schema-path');
        }
        
        if ($this->option('seed')) {
            $options['--seed'] = true;
        }
        
        if ($this->option('seeder')) {
            $options['--seeder'] = $this->option('seeder');
        }
        
        if ($this->option('step')) {
            $options['--step'] = $this->option('step');
        }
        
        return $options;
    }

    /**
     * Confirm before proceeding with the command.
     */
    protected function confirmToProceed()
    {
        if ($this->option('force')) {
            return true;
        }

        return $this->confirm(
            'This will drop all tables in the central database and delete all tenant databases. Do you really wish to run this command?'
        );
    }
}