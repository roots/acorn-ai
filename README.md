# Acorn AI

AI support for [Acorn](https://github.com/roots/acorn) — wraps [laravel/ai](https://github.com/laravel/ai) and adds first-class integration with the [WordPress Abilities API](https://make.wordpress.org/core/2025/07/17/abilities-api/).

## Requirements

- PHP 8.4+
- WordPress 6.9+ (for Abilities API)
- [Acorn](https://github.com/roots/acorn) 5.0+

## Installation

```bash
composer require roots/acorn-ai
```

Publish the config files:

```shell
# WordPress-specific config (abilities registration)
wp acorn vendor:publish --provider="Roots\AcornAi\AcornAiServiceProvider" --tag=ai-wordpress-config

# AI provider config (providers, API keys, defaults)
wp acorn vendor:publish --provider="Laravel\Ai\AiServiceProvider" --tag=ai-config
```

Add your provider API keys to `.env`:

```env
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
```

## AI Providers & Agents

`roots/acorn-ai` includes [laravel/ai](https://github.com/laravel/ai) and makes it available inside WordPress via Acorn's container. You get the full `laravel/ai` feature set: agents, tools, embeddings, image generation, audio, transcription, and reranking — all configured through `config/ai.php` (published from `laravel/ai`).

### Creating an agent

```shell
wp acorn make:agent SupportAgent
```

### Creating a tool

```shell
wp acorn make:tool SearchPosts
```

Refer to the [laravel/ai documentation](https://github.com/laravel/ai) for full usage of agents, tools, structured output, and providers.

## Abilities

The WordPress Abilities API (WordPress 6.9+) provides a standardized way to define capabilities that AI agents can invoke — via REST API (`wp-abilities/v1/abilities/{name}/run`) and the [WordPress MCP Adapter](https://github.com/WordPress/mcp-adapter).

`roots/acorn-ai` wraps ability registration so that your ability classes are resolved through Laravel's service container, giving you full dependency injection.

### Creating an ability

```shell
wp acorn make:ability CreatePost
```

This generates `app/Ai/Abilities/CreatePostAbility.php`:

```php
namespace App\Ai\Abilities;

use Roots\AcornAi\Abilities\Ability;

class CreatePostAbility extends Ability
{
    public function __construct(private PostRepository $posts) {}

    public function label(): string
    {
        return 'Create Post';
    }

    public function description(): string
    {
        return 'Creates a new WordPress post with the given title and content.';
    }

    public function execute(array $input): mixed
    {
        return $this->posts->create($input['title'], $input['content'] ?? '');
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'The post title.'],
                'content' => ['type' => 'string', 'description' => 'The post body.'],
            ],
            'required' => ['title'],
        ];
    }
}
```

### Registering abilities

Add your ability classes to `config/ai-wordpress.php`:

```php
'abilities' => [
    \App\Ai\Abilities\CreatePostAbility::class,
],
```

### Ability names

The default name is derived from the root namespace and class name:

```
\App\Ai\Abilities\CreatePostAbility  →  "app/create-post"
```

Override `name()` to use a custom name:

```php
public function name(): string
{
    return 'app/create-post';
}
```

### Permissions

Override `permission()` to control access. Return `true`, `false`, or a `WP_Error`:

```php
public function permission(): bool|\WP_Error
{
    return current_user_can('edit_posts');
}
```

### MCP Adapter

To expose an ability as an MCP tool when the [WordPress MCP Adapter](https://github.com/WordPress/mcp-adapter) plugin is active, set `mcp.public` in `meta()`:

```php
public function meta(): array
{
    return [
        'mcp' => ['public' => true],
    ];
}
```

### Listing abilities

```shell
wp acorn ability:list
```

## Example: Post summarization via AI

This example shows a realistic ability that uses `laravel/ai` under the hood to summarize a WordPress post, exposes it over REST, and makes it available as an MCP tool for AI agents like Claude or Cursor.

**Generate the class:**

```shell
wp acorn make:ability SummarizePost
```

**`app/Ai/Abilities/SummarizePostAbility.php`:**

```php
namespace App\Ai\Abilities;

use Illuminate\Support\Facades\Cache;
use Laravel\Ai\AnonymousAgent;
use Roots\AcornAi\Abilities\Ability;

class SummarizePostAbility extends Ability
{
    public function name(): string
    {
        return 'app/summarize-post';
    }

    public function label(): string
    {
        return 'Summarize Post';
    }

    public function description(): string
    {
        return 'Generates a concise summary of a WordPress post given its ID. '
            . 'Use this when you need a brief overview of a post without retrieving its full content.';
    }

    public function execute(array $input): mixed
    {
        $post = get_post($input['post_id']);

        if (! $post || $post->post_status !== 'publish') {
            return new \WP_Error('not_found', 'Post not found.');
        }

        return Cache::remember("ai-summary-{$post->ID}", now()->addDay(), function () use ($post) {
            $content = wp_strip_all_tags($post->post_content);

            return AnonymousAgent::make(
                instructions: 'You are a helpful assistant that summarizes blog posts in 2-3 sentences.',
                messages: [],
                tools: [],
            )->prompt("Summarize this post:\n\n{$content}")->text;
        });
    }

    public function permission(): bool|\WP_Error
    {
        return is_user_logged_in();
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'post_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the post to summarize.',
                ],
            ],
            'required' => ['post_id'],
        ];
    }

    public function meta(): array
    {
        return [
            'mcp' => ['public' => true],
        ];
    }
}
```

**Register it in `config/ai-wordpress.php`:**

```php
'abilities' => [
    \App\Ai\Abilities\SummarizePostAbility::class,
],
```

**What this gives you:**

- **REST API** — any authenticated client can call `POST /wp-json/wp-abilities/v1/abilities/app/summarize-post/run` with `{"post_id": 42}`
- **MCP tool** — when the [WordPress MCP Adapter](https://github.com/WordPress/mcp-adapter) is active, the ability is surfaced to connected AI agents automatically
- **Caching** — summaries are cached for 24 hours so repeated calls don't burn API credits
- **Container injection** — swap `AnonymousAgent` for a dedicated agent class or any injected service by adding constructor dependencies to the ability

## License

MIT
