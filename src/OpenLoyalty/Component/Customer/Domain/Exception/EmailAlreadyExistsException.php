<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Exception;

/**
 * Class EmailAlreadyExistsException.
 */
class EmailAlreadyExistsException extends CustomerValidationException
{
    protected $message = 'customer with such email already exists';
}
