<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ValidationBundle\Tests\Functional\Validation;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This test class is testing sulu validation.
 */
class ValidationTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test calling a route without validation.
     */
    public function testNoValidation(): void
    {
        $this->client->request('GET', '/no-validation');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test validation success on GET request.
     */
    public function testGetValidationSuccess(): void
    {
        $this->client->request('GET', '/get-validation', ['locale' => 'en']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * tests validation error on GET request.
     */
    public function testGetValidationError(): void
    {
        $this->client->request('GET', '/get-validation');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $responseContent);
        $this->assertResponseContainsProperties($responseContent, ['locale']);
    }

    /**
     * Test validation success on POST request.
     */
    public function testPostValidationSuccess(): void
    {
        $this->client->request(
            'POST',
            '/post-validation',
            [
                'locale' => 'en',
                'name' => 'test',
                'attributes' => [
                    ['id' => 2],
                ],
            ]
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * tests validation error on POST request.
     */
    public function testPostValidationError(): void
    {
        $this->client->request('POST', '/post-validation');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $responseContent);
        $this->assertResponseContainsProperties($responseContent, ['name', 'attributes']);
    }

    public function testValidationOfSchemaWithInlineRefs(): void
    {
        $data = [
            'billingAddress' => [
                'street' => 'Teststreet',
                'city' => 'Testcity',
                'zip' => 'ABC1234',
                'country' => 'Testcountry',
            ],
            'shippingAddress' => [
                'street' => 'Teststreet',
                'city' => 'Testcity',
                'zip' => 'ABC1234',
                'country' => 'Testcountry',
            ],
        ];

        $this->client->request('POST', '/schema-with-inline-refs', $data);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testValidationOfSchemaWithRefs(): void
    {
        $data = [
            'billingAddress' => [
                'street' => 'Teststreet',
                'city' => 'Testcity',
                'zip' => 'ABC1234',
                'country' => 'Testcountry',
            ],
            'shippingAddress' => [
                'street' => 'Teststreet',
                'city' => 'Testcity',
                'zip' => 'ABC1234',
                'country' => 'Testcountry',
            ],
        ];

        $this->client->request('POST', '/schema-with-refs', $data);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  tests if missing shipping address and missing zip of billing address are detected when using inline refs.
     */
    public function testValidationOfSchemaWithInlineRefsAndErrors(): void
    {
        $data = [
            'billingAddress' => [
                'street' => 'Teststreet',
                'city' => 'Testcity',
                'country' => 'Testcountry',
            ],
        ];

        $this->client->request('POST', '/schema-with-inline-refs', $data);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseContainsProperties($responseContent, ['shippingAddress', 'billingAddress.zip']);
    }

    /**
     *  tests if missing shipping address and missing zip of billing address are detected when using refs.
     */
    public function testValidationOfSchemaWithRefsAndErrors(): void
    {
        $data = [
            'billingAddress' => [
                'street' => 'Teststreet',
                'city' => 'Testcity',
                'country' => 'Testcountry',
            ],
        ];

        $this->client->request('POST', '/schema-with-refs', $data);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseContainsProperties($responseContent, ['shippingAddress', 'billingAddress.zip']);
    }

    /**
     * tests if response contains expected properties.
     *
     * @param array $responseContent
     * @param array $properties
     */
    public function assertResponseContainsProperties(array $responseContent, array $properties): void
    {
        foreach ($responseContent as $index => $content) {
            $this->assertContains($content['property'], $properties);
            unset($responseContent[$index]);
        }
    }
}
