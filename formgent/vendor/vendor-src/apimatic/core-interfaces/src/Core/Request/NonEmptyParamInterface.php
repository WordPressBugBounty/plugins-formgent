<?php

namespace FormGent\CoreInterfaces\Core\Request;

interface NonEmptyParamInterface extends ParamInterface
{
    public function requiredNonEmpty();
}
