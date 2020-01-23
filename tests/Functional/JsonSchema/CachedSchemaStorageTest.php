<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ValidationBundle\Tests\Functional\JsonSchema;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CachedSchemaStorageTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    public function setUp():void
    {
        $this->client = static::createClient();
    }

    public function testJsonSchemaCacheGeneration(): void
    {
        $container = $this->client->getKernel()->getContainer()->get('test.service_container');
        $cachedSchemaStorage = $container->get('sulu_validation.cached_schema_storage');
        $cachedSchemaStorage->initializeCache();
        $cacheFilePath = $container->getParameter('sulu_validation.schema_cache');
        $baseDir = 'file://' . $container->getParameter('kernel.project_dir') . '/tests/Resources/Schemas/';

        $this->assertFileExists($cacheFilePath);
        $cachedData = unserialize(file_get_contents($cacheFilePath));

        $this->assertArrayHasKey($baseDir . 'getActionSchema.json', $cachedData);
        $this->assertArrayHasKey($baseDir . 'postActionSchema.json', $cachedData);
        $this->assertArrayHasKey($baseDir . 'schemaWithInlineRefs.json', $cachedData);
        $this->assertArrayHasKey($baseDir . 'schemaWithRefs.json', $cachedData);
        $this->assertArrayHasKey($baseDir . 'SubSchema/addressSchema.json', $cachedData);

        $this->assertNotNull($cachedSchemaStorage->getSchema($baseDir . 'SubSchema/addressSchema.json'));

        $fileData = json_decode(file_get_contents($baseDir . 'SubSchema/addressSchema.json'));
        $this->assertEquals($fileData, $cachedData[$baseDir . 'SubSchema/addressSchema.json']);
    }
}
