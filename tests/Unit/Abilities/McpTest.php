<?php

use Roots\AcornAi\Abilities\Mcp;

test('tool() creates a public tool', function () {
    $mcp = Mcp::tool();

    expect($mcp->public)->toBeTrue()
        ->and($mcp->type)->toBe('tool')
        ->and($mcp->uri)->toBeNull();
});

test('resource() creates a public resource with uri', function () {
    $mcp = Mcp::resource('file:///posts');

    expect($mcp->public)->toBeTrue()
        ->and($mcp->type)->toBe('resource')
        ->and($mcp->uri)->toBe('file:///posts');
});

test('prompt() creates a public prompt', function () {
    $mcp = Mcp::prompt();

    expect($mcp->public)->toBeTrue()
        ->and($mcp->type)->toBe('prompt')
        ->and($mcp->uri)->toBeNull();
});

test('none() creates a non-public none type', function () {
    $mcp = Mcp::none();

    expect($mcp->public)->toBeFalse()
        ->and($mcp->type)->toBe('none')
        ->and($mcp->uri)->toBeNull();
});

test('toArray() returns correct shape for tool', function () {
    $meta = Mcp::tool()->toArray();

    expect($meta)->toBe([
        'mcp' => [
            'public' => true,
            'type' => 'tool',
        ],
    ]);
});

test('toArray() returns correct shape for resource', function () {
    $meta = Mcp::resource('file:///posts')->toArray();

    expect($meta)->toBe([
        'mcp' => [
            'public' => true,
            'type' => 'resource',
            'uri' => 'file:///posts',
        ],
    ]);
});

test('toArray() returns correct shape for prompt', function () {
    $meta = Mcp::prompt()->toArray();

    expect($meta)->toBe([
        'mcp' => [
            'public' => true,
            'type' => 'prompt',
        ],
    ]);
});

test('toArray() returns empty array for none', function () {
    $meta = Mcp::none()->toArray();

    expect($meta)->toBe([]);
});

test('annotations() returns a new instance', function () {
    $original = Mcp::tool();
    $annotated = $original->annotations(readonly: true);

    expect($annotated)->not->toBe($original)
        ->and($original->annotations)->toBe([])
        ->and($annotated->annotations)->toBe(['readonly' => true]);
});

test('annotations() merges values and filters nulls', function () {
    $mcp = Mcp::tool()->annotations(readonly: true, destructive: false);

    expect($mcp->annotations)->toBe([
        'readonly' => true,
        'destructive' => false,
    ]);
});

test('chained annotations() calls merge correctly', function () {
    $mcp = Mcp::tool()
        ->annotations(readonly: true)
        ->annotations(priority: 0.5, audience: 'internal');

    expect($mcp->annotations)->toBe([
        'readonly' => true,
        'audience' => 'internal',
        'priority' => 0.5,
    ]);
});

test('toArray() omits annotations when empty', function () {
    $meta = Mcp::tool()->toArray();

    expect($meta)->not->toHaveKey('annotations');
});

test('toArray() includes annotations when present', function () {
    $meta = Mcp::tool()->annotations(readonly: true)->toArray();

    expect($meta)->toHaveKey('annotations')
        ->and($meta['annotations'])->toBe(['readonly' => true]);
});
