<?php

namespace Grafkit\Client;

readonly class DashboardMetadata
{
    public const KEY_ID = 'id';
    public const KEY_UID = 'uid';
    public const KEY_TITLE = 'title';
    public const KEY_URI = 'uri';
    public const KEY_URL = 'url';
    public const KEY_SLUG = 'slug';
    public const KEY_TAGS = 'tags';
    public const KEY_IS_STARRED = 'isStarred';

    public function __construct(
        public int $id,
        public string $uid,
        public string $title,
        public string $uri,
        public string $url,
        public string $slug,
        public array $tags,
        public bool $isStarred
    ) {}
}