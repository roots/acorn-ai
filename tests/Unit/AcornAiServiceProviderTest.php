<?php

use Roots\AcornAi\Abilities\Ability;
use Roots\AcornAi\Abilities\Mcp;
use Roots\AcornAi\AcornAiServiceProvider;

beforeEach(function () {
    $GLOBALS['wp_registered_abilities'] = [];
});

afterEach(function () {
    unset($GLOBALS['wp_registered_abilities']);
});

test('config is merged on register()', function () {
    expect(config('ai-wordpress'))->toBeArray()
        ->and(config('ai-wordpress.abilities'))->toBeArray();
});

test('registerAbilities() registers action hook when wp_register_ability exists', function () {
    // boot() already ran during setUp, so check the globals that were set then
    $actions = collect($GLOBALS['wp_actions'] ?? []);

    expect($actions->contains('hook', 'wp_abilities_api_init'))->toBeTrue();
});

test('MCP metadata merges into meta array when mcp() returns a value', function () {
    $abilityClass = new class extends Ability
    {
        public function label(): string
        {
            return 'Test';
        }

        public function description(): string
        {
            return 'Test ability';
        }

        public function execute(array $input): mixed
        {
            return null;
        }

        public function mcp(): Mcp
        {
            return Mcp::tool()->annotations(readonly: true);
        }

        public function meta(): array
        {
            return ['custom' => 'value'];
        }
    };

    config()->set('ai-wordpress.abilities', [$abilityClass::class]);

    $provider = new AcornAiServiceProvider($this->app);
    $GLOBALS['wp_actions'] = [];
    $GLOBALS['wp_registered_abilities'] = [];
    $provider->boot();

    $action = collect($GLOBALS['wp_actions'])
        ->firstWhere('hook', 'wp_abilities_api_init');

    expect($action)->not->toBeNull();

    ($action['callback'])();

    $registered = $GLOBALS['wp_registered_abilities'][0];

    expect($registered['args']['meta'])->toHaveKey('custom', 'value')
        ->and($registered['args']['meta'])->toHaveKey('mcp')
        ->and($registered['args']['meta']['mcp'])->toBe(['public' => true, 'type' => 'tool'])
        ->and($registered['args']['meta'])->toHaveKey('annotations')
        ->and($registered['args']['meta']['annotations'])->toBe(['readonly' => true]);
});
