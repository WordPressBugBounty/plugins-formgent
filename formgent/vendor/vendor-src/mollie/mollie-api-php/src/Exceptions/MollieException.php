<?php

namespace FormGent\Mollie\Api\Exceptions;

use FormGent\Psr\Http\Client\ClientExceptionInterface;
abstract class MollieException extends \Exception implements ClientExceptionInterface
{
}
