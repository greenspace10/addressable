<?php

namespace Grnspc\Addresses\Providers;

use Illuminate\Support\Str;
use Grnspc\Addresses\Models\Address;
use Illuminate\Support\ServiceProvider;
use Grnspc\Addresses\Console\Commands\MigrateCommand;
use Grnspc\Addresses\Console\Commands\PublishCommand;
use Grnspc\Addresses\Console\Commands\RollbackCommand;

class AddressesServiceProvider extends ServiceProvider
{

    protected $commands = [
        MigrateCommand::class => 'command.grnspc.addresses.migrate',
        PublishCommand::class => 'command.grnspc.addresses.publish',
        RollbackCommand::class => 'command.grnspc.addresses.rollback',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../config/config.php'), 'grnspc.addresses');

        $this->registerModels([
            'grnspc.addresses.address' => Address::class,
        ]);

        // Register console commands
        $this->registerCommands($this->commands);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Publish Resources
        $this->publishesConfig('grnspc/addresses');
        $this->publishesMigrations('grnspc/addresses');
        !$this->autoloadMigrations('grnspc/addresses') || $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * Publish package migrations.
     *
     * @return void
     */
    protected function publishesMigrations(string $package, bool $isModule = false): void
    {
        if (!$this->publishesResources()) {
            return;
        }

        $namespace = str_replace('laravel-', '', $package);
        $basePath = $isModule ? $this->app->path($package)
            : $this->app->basePath('vendor/' . $package);

        if (file_exists($path = $basePath . '/database/migrations')) {
            $stubs = $this->app['files']->glob($path . '/*.php');
            $existing = $this->app['files']->glob($this->app->databasePath('migrations/' . $package . '/*.php'));

            $migrations = collect($stubs)->flatMap(function ($migration) use ($existing, $package) {
                $sequence = mb_substr(basename($migration), 0, 17);
                $match = collect($existing)->first(function ($item, $key) use ($migration, $sequence) {
                    return mb_strpos($item, str_replace($sequence, '', basename($migration))) !== false;
                });

                return [$migration => $this->app->databasePath('migrations/' . $package . '/' . ($match ? basename($match) : date('Y_m_d_His', time() + mb_substr($sequence, -6)) . str_replace($sequence, '', basename($migration))))];
            })->toArray();

            $this->publishes($migrations, $namespace . '::migrations');
        }
    }

    /**
     * Publish package config.
     *
     * @return void
     */
    protected function publishesConfig(string $package, bool $isModule = false): void
    {
        if (!$this->publishesResources()) {
            return;
        }

        $namespace = str_replace('laravel-', '', $package);
        $basePath = $isModule ? $this->app->path($package)
            : $this->app->basePath('vendor/' . $package);

        if (file_exists($path = $basePath . '/config/config.php')) {
            $this->publishes([$path => $this->app->configPath(str_replace('/', '.', $namespace) . '.php')], $namespace . '::config');
        }
    }

    protected function publishesResources(): bool
    {
        return !$this->app->environment('production') || $this->app->runningInConsole() || $this->runningInDevzone();
    }

    protected function autoloadMigrations(string $module): bool
    {
        return $this->publishesResources() && $this->app['config'][str_replace(['laravel-', '/'], ['', '.'], $module) . '.autoload_migrations'];
    }

    /**
     * Determine if the application is running in the console.
     *
     * @TODO: Implement this method to detect if we're in active dev zone or not!
     *        Ex: running inside cortex/console action
     *
     * @return bool
     */
    public function runningInDevzone()
    {
        return true;
    }

    /**
     * Register models into IoC.
     *
     * @param array $models
     *
     * @return void
     */
    protected function registerModels(array $models): void
    {
        // dd($models);
        foreach ($models as $service => $class) {
            // dd($this->app['config'][Str::replaceLast('.', '.models.', $service)]);
            $this->app->singleton($service, $model = $this->app['config'][Str::replaceLast('.', '.models.', $service)]);
            $model === $class || $this->app->alias($service, $class);
        }
    }

    /**
     * Register console commands.
     *
     * @param array $commands
     *
     * @return void
     */
    protected function registerCommands(array $commands): void
    {
        if (! $this->app->runningInConsole() && ! $this->runningInDevzone()) {
            return;
        }

        foreach ($commands as $key => $value) {
            $this->app->singleton($value, $key);
        }

        $this->commands(array_values($commands));
    }
}
