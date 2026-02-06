<?php

declare (strict_types=1);
namespace FormGent\Core\Authentication;

use FormGent\CoreInterfaces\Core\Authentication\AuthInterface;
use FormGent\CoreInterfaces\Core\Request\ParamInterface;
use FormGent\CoreInterfaces\Core\Request\RequestSetterInterface;
use FormGent\CoreInterfaces\Core\Request\TypeValidatorInterface;
use InvalidArgumentException;
/**
 * Use to apply authentication parameters to the request
 */
class CoreAuth implements AuthInterface
{
    private $parameters;
    private $isValid = \false;
    /**
     * @param ParamInterface ...$parameters
     */
    public function __construct(...$parameters)
    {
        $this->parameters = $parameters;
    }
    /**
     * @throws InvalidArgumentException
     */
    public function validate(TypeValidatorInterface $validator) : void
    {
        \array_walk($this->parameters, function ($param) use($validator) : void {
            $param->validate($validator);
        });
        $this->isValid = \true;
    }
    public function apply(RequestSetterInterface $request) : void
    {
        if (!$this->isValid) {
            return;
        }
        \array_walk($this->parameters, function ($param) use($request) : void {
            $param->apply($request);
        });
    }
}
