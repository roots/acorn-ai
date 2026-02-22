<?php

namespace Roots\AcornAi\Abilities;

use Illuminate\Support\Str;

abstract class Ability
{
    /**
     * The unique name of the ability in "namespace/ability-name" format.
     *
     * By default this is derived from the app namespace and class name.
     * For example, \App\Ai\Abilities\CreatePageAbility → "app/create-page".
     *
     * Override this method to use a custom name.
     */
    public function name(): string
    {
        $class = class_basename(static::class);

        $ability = Str::kebab(Str::replaceLast('Ability', '', $class));
        $namespace = Str::lower(Str::before(static::class, '\\'));

        return "{$namespace}/{$ability}";
    }

    /**
     * The human-readable label for the ability.
     */
    abstract public function label(): string;

    /**
     * A description of what the ability does.
     *
     * This is surfaced to AI agents when using the MCP adapter, so write it
     * as you would a tool description in a system prompt.
     */
    abstract public function description(): string;

    /**
     * Execute the ability with the given input.
     *
     * @param  array<string, mixed>  $input
     */
    abstract public function execute(array $input);

    /**
     * Determine whether the current user has permission to execute the ability.
     *
     * Return true/false, or a WP_Error to return a specific error message.
     */
    public function permission(): bool|\WP_Error
    {
        return is_user_logged_in();
    }

    /**
     * The category this ability belongs to.
     *
     * Must be a registered category slug (e.g. "content", "site", "user").
     */
    public function category(): ?string
    {
        return null;
    }

    /**
     * The JSON Schema for the ability's input.
     *
     * @return array{
     *     type?: string,
     *     properties?: array<string, array<string, mixed>>,
     *     required?: string[],
     *     description?: string,
     *     enum?: list<mixed>,
     *     default?: mixed,
     *     minLength?: int,
     *     maxLength?: int,
     *     ...
     * }
     */
    public function inputSchema(): array
    {
        return [];
    }

    /**
     * The JSON Schema for the ability's output.
     *
     * @return array{
     *     type?: string,
     *     properties?: array<string, array<string, mixed>>,
     *     required?: string[],
     *     description?: string,
     *     enum?: list<mixed>,
     *     ...
     * }
     */
    public function outputSchema(): array
    {
        return [];
    }

    /**
     * The MCP exposure configuration for this ability.
     *
     * Override this method to expose the ability via the
     * WordPress MCP Adapter plugin when it is active.
     */
    public function mcp(): Mcp
    {
        return Mcp::none();
    }

    /**
     * Additional metadata for the ability.
     *
     * @return array{
     *     show_in_rest?: bool,
     *     annotations?: array{
     *         readonly?: bool|null,
     *         destructive?: bool|null,
     *         idempotent?: bool|null,
     *     },
     *     ...
     * }
     */
    public function meta(): array
    {
        return [];
    }
}
