<?php

namespace FormGent\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \FormGent\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }
        return \FormGent\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
