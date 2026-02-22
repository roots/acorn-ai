<?php

use Roots\AcornAi\Abilities\Ability;
use Roots\AcornAi\Abilities\Mcp;

class TestCreatePageAbility extends Ability
{
    public function label(): string
    {
        return 'Create Page';
    }

    public function description(): string
    {
        return 'Creates a new page.';
    }

    public function execute(array $input): mixed
    {
        return null;
    }
}

beforeEach(function () {
    unset($GLOBALS['wp_user_logged_in']);
});

test('name() derives namespace/kebab-name from class name', function () {
    $ability = new TestCreatePageAbility;

    expect($ability->name())->toBe('testcreatepageability/test-create-page');
});

test('permission() delegates to is_user_logged_in()', function () {
    $ability = new TestCreatePageAbility;

    $GLOBALS['wp_user_logged_in'] = false;
    expect($ability->permission())->toBeFalse();

    $GLOBALS['wp_user_logged_in'] = true;
    expect($ability->permission())->toBeTrue();
});

test('mcp() defaults to Mcp::none()', function () {
    $ability = new TestCreatePageAbility;

    $mcp = $ability->mcp();

    expect($mcp)->toBeInstanceOf(Mcp::class)
        ->and($mcp->type)->toBe('none')
        ->and($mcp->public)->toBeFalse();
});

test('meta() defaults to empty array', function () {
    $ability = new TestCreatePageAbility;

    expect($ability->meta())->toBe([]);
});

test('category() defaults to null', function () {
    $ability = new TestCreatePageAbility;

    expect($ability->category())->toBeNull();
});

test('inputSchema() defaults to empty array', function () {
    $ability = new TestCreatePageAbility;

    expect($ability->inputSchema())->toBe([]);
});

test('outputSchema() defaults to empty array', function () {
    $ability = new TestCreatePageAbility;

    expect($ability->outputSchema())->toBe([]);
});
