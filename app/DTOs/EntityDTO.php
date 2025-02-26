<?php

namespace App\DTOs;

class EntityDTO
{
    public function __construct(
        public readonly string $api,
        public readonly string $description,
        public readonly string $link,
        public readonly int $categoryId
    ) {}

    public static function fromArray(array $data, int $categoryId): self
    {
        return new self(
            api: $data['API'] ?? $data['api'],
            description: $data['Description'] ?? $data['description'],
            link: $data['Link'] ?? $data['link'],
            categoryId: $categoryId
        );
    }
}
