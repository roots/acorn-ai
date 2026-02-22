<?php

/**
 * WordPress function and class stubs for testing.
 *
 * These stubs record their calls in globals so tests can assert
 * against them without requiring a real WordPress installation.
 */
if (! function_exists('wp_register_ability')) {
    function wp_register_ability(string $name, array $args): void
    {
        $GLOBALS['wp_registered_abilities'][] = compact('name', 'args');
    }
}

if (! function_exists('wp_get_abilities')) {
    function wp_get_abilities(): array
    {
        return $GLOBALS['wp_abilities'] ?? [];
    }
}

if (! function_exists('is_user_logged_in')) {
    function is_user_logged_in(): bool
    {
        return $GLOBALS['wp_user_logged_in'] ?? false;
    }
}

if (! function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $GLOBALS['wp_actions'][] = compact('hook', 'callback', 'priority', 'accepted_args');
    }
}

if (! class_exists('WP_Error')) {
    class WP_Error {}
}
