<?php

namespace Roots\AcornAi\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'ability:list')]
class AbilityListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ability:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered abilities';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (! function_exists('wp_get_abilities')) {
            $this->components->error('The WordPress Abilities API is not available. Requires WordPress 6.9 or later.');

            return;
        }

        $abilities = wp_get_abilities();

        if (empty($abilities)) {
            $this->components->info('No abilities are currently registered.');

            return;
        }

        $rows = [];

        foreach ($abilities as $ability) {
            $rows[] = [
                $ability->name,
                $ability->label ?? '',
                $ability->category ?? '—',
            ];
        }

        $this->table(['Name', 'Label', 'Category'], $rows);
    }
}
