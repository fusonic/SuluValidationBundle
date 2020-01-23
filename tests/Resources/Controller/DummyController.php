<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ValidationBundle\Tests\Resources\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * This is a dummy controller for testing purposes.
 */
class DummyController
{
    /**
     * Function for testing purposes only.
     *
     * @return Response
     */
    public function testValidationAction()
    {
        return new Response();
    }
}
