<?php

test('stub generates ability with mcp() method and Mcp import', function () {
    $stub = file_get_contents(__DIR__.'/../../../../stubs/ability.stub');

    expect($stub)
        ->toContain('extends Ability')
        ->and($stub)
        ->toContain('function mcp()')
        ->and($stub)
        ->toContain('use Roots\AcornAi\Abilities\Mcp')
        ->and($stub)
        ->toContain('Mcp::none()');
});

test('default stub generates ability without custom mcp override', function () {
    $this->artisan('make:ability', ['name' => 'TestDefaultAbility'])->assertSuccessful();

    $path = app_path('Ai/Abilities/TestDefaultAbility.php');

    expect(file_exists($path))->toBeTrue();

    $contents = file_get_contents($path);

    expect($contents)->toContain('extends Ability')->and($contents)->toContain('Mcp::none()');

    @unlink($path);
});
