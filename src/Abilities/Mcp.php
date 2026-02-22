<?php

namespace Roots\AcornAi\Abilities;

readonly class Mcp
{
    /**
     * @param  array<string, mixed>  $annotations
     */
    private function __construct(
        public bool $public,
        public string $type,
        public ?string $uri = null,
        public array $annotations = [],
    ) {}

    /**
     * Create an MCP tool exposure.
     */
    public static function tool(): self
    {
        return new self(
            public: true,
            type: 'tool',
        );
    }

    /**
     * Create an MCP resource exposure.
     */
    public static function resource(string $uri): self
    {
        return new self(
            public: true,
            type: 'resource',
            uri: $uri,
        );
    }

    /**
     * Create an MCP prompt exposure.
     */
    public static function prompt(): self
    {
        return new self(
            public: true,
            type: 'prompt',
        );
    }

    /**
     * Create no MCP exposure.
     */
    public static function none(): self
    {
        return new self(
            public: false,
            type: 'none',
        );
    }

    /**
     * Set annotations on the MCP exposure.
     *
     * Returns a new instance with the given annotations merged in.
     */
    public function annotations(
        ?bool $readonly = null,
        ?bool $destructive = null,
        ?bool $idempotent = null,
        ?string $audience = null,
        ?string $lastModified = null,
        ?float $priority = null,
    ): self {
        $annotations = array_filter(
            [
                'readonly' => $readonly,
                'destructive' => $destructive,
                'idempotent' => $idempotent,
                'audience' => $audience,
                'lastModified' => $lastModified,
                'priority' => $priority,
            ],
            fn ($value) => $value !== null,
        );

        return new self(
            public: $this->public,
            type: $this->type,
            uri: $this->uri,
            annotations: array_merge($this->annotations, $annotations),
        );
    }

    /**
     * Convert to the meta array shape expected by wp_register_ability().
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->type === 'none') {
            return [];
        }

        $meta = [
            'mcp' => array_filter([
                'public' => $this->public,
                'type' => $this->type,
                'uri' => $this->uri,
            ]),
        ];

        if ($this->annotations !== []) {
            $meta['annotations'] = $this->annotations;
        }

        return $meta;
    }
}
