<?php

namespace Grafkit\Client;

class DashboardMetadataBuilder
{
    private int $id;
    private string $uid;
    private string $title;
    private string $uri;
    private string $url;
    private string $slug;
    private array $tags;
    private bool $isStarred;

    /**
     * @param array $json
     * @return DashboardMetadata
     */
    public static function fromArray(array $json): DashboardMetadata
    {
        return DashboardMetadataBuilder::new()
            ->withId($json[DashboardMetadata::KEY_ID])
            ->withUid($json[DashboardMetadata::KEY_UID])
            ->withTitle($json[DashboardMetadata::KEY_TITLE])
            ->withUri($json[DashboardMetadata::KEY_URI])
            ->withUrl($json[DashboardMetadata::KEY_URL])
            ->withSlug($json[DashboardMetadata::KEY_SLUG])
            ->withTags($json[DashboardMetadata::KEY_TAGS])
            ->withIsStarred($json[DashboardMetadata::KEY_IS_STARRED])
            ->build();
    }

    /**
     * @return DashboardMetadataBuilder
     */
    public static function new(): DashboardMetadataBuilder
    {
        return new DashboardMetadataBuilder();
    }

    /**
     * @param int $id
     * @return DashboardMetadataBuilder
     */
    public function withId(int $id): DashboardMetadataBuilder
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $uid
     * @return DashboardMetadataBuilder
     */
    public function withUid(string $uid): DashboardMetadataBuilder
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @param string $title
     * @return DashboardMetadataBuilder
     */
    public function withTitle(string $title): DashboardMetadataBuilder
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $uri
     * @return DashboardMetadataBuilder
     */
    public function withUri(string $uri): DashboardMetadataBuilder
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param string $url
     * @return DashboardMetadataBuilder
     */
    public function withUrl(string $url): DashboardMetadataBuilder
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $slug
     * @return DashboardMetadataBuilder
     */
    public function withSlug(string $slug): DashboardMetadataBuilder
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @param array $tags
     * @return DashboardMetadataBuilder
     */
    public function withTags(array $tags): DashboardMetadataBuilder
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @param bool $isStarred
     * @return DashboardMetadataBuilder
     */
    public function withIsStarred(bool $isStarred): DashboardMetadataBuilder
    {
        $this->isStarred = $isStarred;
        return $this;
    }

    /**
     * @return DashboardMetadata
     */
    public function build(): DashboardMetadata
    {
        return new DashboardMetadata(
            $this->id,
            $this->uid,
            $this->title,
            $this->uri,
            $this->url,
            $this->slug,
            $this->tags,
            $this->isStarred
        );
    }
}
