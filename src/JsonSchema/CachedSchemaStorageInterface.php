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
use JsonSchema\SchemaStorageInterface;

/**
 * Interface for cached schema storage.
 */
interface CachedSchemaStorageInterface extends SchemaStorageInterface
{
    /**
     * Returns a based on a given route id.
     *
     * @param string $routeId
     *
     * @return \stdClass
     *
     * @throws InvalidArgumentException
     */
    public function getSchemaByRoute($routeId);

    /**
     * Initializes the a config cache from the schemas configured in the sulu_validation.schemas parameter.
     */
    public function initializeCache();
}
