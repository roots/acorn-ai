<?php

namespace Roots\AcornAi;

use Illuminate\Support\ServiceProvider;
use Roots\AcornAi\Abilities\Ability;
use Roots\AcornAi\Console\Commands\AbilityListCommand;
use Roots\AcornAi\Console\Commands\MakeAbilityCommand;

class AcornAiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ai-wordpress.php', 'ai-wordpress');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeAbilityCommand::class,
                AbilityListCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/ai-wordpress.php' => $this->app->configPath('ai-wordpress.php'),
            ], ['ai-wordpress', 'ai-wordpress-config']);

            $this->publishes([
                __DIR__.'/../stubs/ability.stub' => $this->app->basePath('stubs/ability.stub'),
            ], 'ai-stubs');
        }

        $this->registerAbilities();
    }

    /**
     * Register configured abilities with the WordPress Abilities API.
     */
    protected function registerAbilities(): void
    {
        if (! function_exists('wp_register_ability')) {
            return;
        }

        add_action('wp_abilities_api_init', function (): void {
            foreach ($this->app['config']->get('ai-wordpress.abilities', []) as $abilityClass) {
                /** @var Ability $ability */
                $ability = $this->app->make($abilityClass);

                $mcp = $ability->mcp();
                $meta = array_filter([
                    ...$ability->meta(),
                    ...$mcp->toArray(),
                ]);

                wp_register_ability($ability->name(), array_filter(
                    [
                        'label' => $ability->label(),
                        'description' => $ability->description(),
                        'execute_callback' => fn (array $input) => $ability->execute($input),
                        'permission_callback' => fn () => $ability->permission(),
                        'category' => $ability->category(),
                        'input_schema' => $ability->inputSchema() ?: null,
                        'output_schema' => $ability->outputSchema() ?: null,
                        'meta' => $meta ?: null,
                    ],
                    fn ($value) => $value !== null,
                ));
            }
        });
    }
}
