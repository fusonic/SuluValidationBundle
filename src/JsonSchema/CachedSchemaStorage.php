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

use InvalidArgumentException;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Iterator\ObjectIterator;
use JsonSchema\SchemaStorage;
use Sulu\Bundle\ValidationBundle\Exceptions\MalformedJsonException;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * This schema storage makes use of a config cache to prevent the same schemas multiple times. The cache is build on
 * base of the values in the sulu_validation.schemas parameter.
 */
class CachedSchemaStorage extends SchemaStorage implements CachedSchemaStorageInterface
{
    const FILE_PREFIX = 'file://';

    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var array
     */
    private $configuredSchemas;

    /**
     * @var bool
     */
    private $debugMode;

    /**
     * @var string
     */
    private $cacheFilePath;

    /**
     * @var bool
     */
    private $isInitialized = false;

    /**
     * @param array                $configuredSchemas array containing all file paths to configured schemas
     * @param FileLocatorInterface $fileLocator
     * @param string               $cacheFilePath
     * @param string               $environment
     */
    public function __construct(
        array $configuredSchemas,
        FileLocatorInterface $fileLocator,
        $cacheFilePath,
        $environment
    ) {
        parent::__construct();

        $this->fileLocator = $fileLocator;
        $this->configuredSchemas = $configuredSchemas;
        $this->debugMode = $environment !== 'prod';
        $this->cacheFilePath = $cacheFilePath;
    }

    /**
     * Initializes the a config cache from the schemas configured in the sulu_validation.schemas parameter.
     */
    public function initializeCache()
    {
        if ($this->isInitialized) {
            return;
        }

        $schemaCache = new ConfigCache($this->cacheFilePath, $this->debugMode);

        if (!$schemaCache->isFresh()) {
            $resources = [];
            $processedSchemas = [];

            foreach ($this->configuredSchemas as $schemaPath) {
                $this->processSchema($schemaPath, $processedSchemas, $resources);
            }

            $schemaCache->write(serialize($processedSchemas), $resources);
        }

        $this->schemas = unserialize(file_get_contents($schemaCache->getPath()));
        $this->isInitialized = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaByRoute($routeId)
    {
        if (!$this->initializeCache()) {
            $this->initializeCache();
        }

        $schemaFilePath = self::FILE_PREFIX . $this->fileLocator->locate($this->configuredSchemas[$routeId]);

        return $this->getSchema($schemaFilePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema($id)
    {
        if (!$this->initializeCache()) {
            $this->initializeCache();
        }

        return parent::getSchema($id);
    }

    /**
     * Locates, validates and adds schema to the cache.
     *
     * @param string $schemaPath
     * @param array  $serializedSchemas
     * @param array  $resources
     *
     * @throws MalformedJsonException
     * @throws InvalidArgumentException
     * @throws FileLocatorFileNotFoundException
     */
    protected function processSchema($schemaPath, array &$serializedSchemas, array &$resources)
    {
        if (array_key_exists($schemaPath, $serializedSchemas)) {
            return;
        }

        $absoluteSchemaPath = $this->fileLocator->locate($schemaPath);
        $schema = json_decode(file_get_contents($absoluteSchemaPath));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MalformedJsonException('Malformed json encountered in ' . $schemaPath);
        }

        if (strpos($absoluteSchemaPath, self::FILE_PREFIX) !== 0) {
            $absoluteSchemaPath = self::FILE_PREFIX . $absoluteSchemaPath;
        }

        $serializedSchemas[$absoluteSchemaPath] = $schema;
        $resources[] = new FileResource($absoluteSchemaPath);
        $this->processReferencesInSchema($schema, $absoluteSchemaPath, $serializedSchemas, $resources);
    }

    /**
     * Resolves references within a given schema and triggers the processing of the newly detected schemas.
     *
     * @param \stdClass $schema
     * @param string    $schemaFilePath
     * @param array     $serializedSchemas
     * @param array     $resources
     *
     * @throws MalformedJsonException
     * @throws InvalidArgumentException
     * @throws FileLocatorFileNotFoundException
     */
    protected function processReferencesInSchema($schema, $schemaFilePath, array &$serializedSchemas, array &$resources)
    {
        $objectIterator = new ObjectIterator($schema);
        foreach ($objectIterator as $toResolveSchema) {
            if (property_exists($toResolveSchema, '$ref') && is_string($toResolveSchema->{'$ref'})) {
                $uri = $this->uriResolver->resolve($toResolveSchema->{'$ref'}, $schemaFilePath);
                $jsonPointer = new JsonPointer($uri);
                $toResolveSchema->{'$ref'} = (string) $jsonPointer;
                $this->processSchema($jsonPointer->getFilename(), $serializedSchemas, $resources);
            }
        }
    }
}
