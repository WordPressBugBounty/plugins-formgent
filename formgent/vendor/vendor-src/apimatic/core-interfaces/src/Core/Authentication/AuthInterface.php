<?php

namespace FormGent\CoreInterfaces\Core\Authentication;

use FormGent\CoreInterfaces\Core\Request\RequestSetterInterface;
use FormGent\CoreInterfaces\Core\Request\TypeValidatorInterface;
use InvalidArgumentException;
interface AuthInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function validate(TypeValidatorInterface $validator) : void;
    public function apply(RequestSetterInterface $request) : void;
}
