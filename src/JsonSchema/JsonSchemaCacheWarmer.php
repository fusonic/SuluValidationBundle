<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ValidationBundle\JsonSchema;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Cache warmer for CachedSchemaStorage.
 */
class JsonSchemaCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var CachedSchemaStorageInterface
     */
    private $cachedSchemaStorage;

    public function __construct(CachedSchemaStorageInterface $cachedSchemaStorage)
    {
        $this->cachedSchemaStorage = $cachedSchemaStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->cachedSchemaStorage->initializeCache();
    }
}
