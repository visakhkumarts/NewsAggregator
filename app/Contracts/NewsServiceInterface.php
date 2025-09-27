<?php

namespace App\Contracts;

interface NewsServiceInterface
{
    /**
     * Fetch articles from the news source.
     *
     * @param array $options
     * @return array
     */
    public function fetchArticles(array $options = []): array;

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getServiceName(): string;

    /**
     * Check if the service is available.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Prepare service-specific options.
     *
     * @param array $options
     * @return array
     */
    public function prepareOptions(array $options): array;
}
