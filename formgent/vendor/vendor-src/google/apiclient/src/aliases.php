<?php

namespace FormGent;

if (\class_exists('FormGent\\Google_Client', \false)) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}
$classMap = ['FormGent\\Google\\Client' => 'Google_Client', 'FormGent\\Google\\Service' => 'Google_Service', 'FormGent\\Google\\AccessToken\\Revoke' => 'Google_AccessToken_Revoke', 'FormGent\\Google\\AccessToken\\Verify' => 'Google_AccessToken_Verify', 'FormGent\\Google\\Model' => 'Google_Model', 'FormGent\\Google\\Utils\\UriTemplate' => 'Google_Utils_UriTemplate', 'FormGent\\Google\\AuthHandler\\Guzzle6AuthHandler' => 'Google_AuthHandler_Guzzle6AuthHandler', 'FormGent\\Google\\AuthHandler\\Guzzle7AuthHandler' => 'Google_AuthHandler_Guzzle7AuthHandler', 'FormGent\\Google\\AuthHandler\\AuthHandlerFactory' => 'Google_AuthHandler_AuthHandlerFactory', 'FormGent\\Google\\Http\\Batch' => 'Google_Http_Batch', 'FormGent\\Google\\Http\\MediaFileUpload' => 'Google_Http_MediaFileUpload', 'FormGent\\Google\\Http\\REST' => 'Google_Http_REST', 'FormGent\\Google\\Task\\Retryable' => 'Google_Task_Retryable', 'FormGent\\Google\\Task\\Exception' => 'Google_Task_Exception', 'FormGent\\Google\\Task\\Runner' => 'Google_Task_Runner', 'FormGent\\Google\\Collection' => 'Google_Collection', 'FormGent\\Google\\Service\\Exception' => 'Google_Service_Exception', 'FormGent\\Google\\Service\\Resource' => 'Google_Service_Resource', 'FormGent\\Google\\Exception' => 'Google_Exception'];
foreach ($classMap as $class => $alias) {
    // \class_alias(($class, $alias);
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Google_Task_Composer extends \FormGent\Google\Task\Composer
{
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
// \class_alias(('FormGent\\Google_Task_Composer', 'Google_Task_Composer', \false);
/** @phpstan-ignore-next-line */
if (\false) {
    class Google_AccessToken_Revoke extends \FormGent\Google\AccessToken\Revoke
    {
    }
    class Google_AccessToken_Verify extends \FormGent\Google\AccessToken\Verify
    {
    }
    class Google_AuthHandler_AuthHandlerFactory extends \FormGent\Google\AuthHandler\AuthHandlerFactory
    {
    }
    class Google_AuthHandler_Guzzle6AuthHandler extends \FormGent\Google\AuthHandler\Guzzle6AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle7AuthHandler extends \FormGent\Google\AuthHandler\Guzzle7AuthHandler
    {
    }
    class Google_Client extends \FormGent\Google\Client
    {
    }
    class Google_Collection extends \FormGent\Google\Collection
    {
    }
    class Google_Exception extends \FormGent\Google\Exception
    {
    }
    class Google_Http_Batch extends \FormGent\Google\Http\Batch
    {
    }
    class Google_Http_MediaFileUpload extends \FormGent\Google\Http\MediaFileUpload
    {
    }
    class Google_Http_REST extends \FormGent\Google\Http\REST
    {
    }
    class Google_Model extends \FormGent\Google\Model
    {
    }
    class Google_Service extends \FormGent\Google\Service
    {
    }
    class Google_Service_Exception extends \FormGent\Google\Service\Exception
    {
    }
    class Google_Service_Resource extends \FormGent\Google\Service\Resource
    {
    }
    class Google_Task_Exception extends \FormGent\Google\Task\Exception
    {
    }
    interface Google_Task_Retryable extends \FormGent\Google\Task\Retryable
    {
    }
    class Google_Task_Runner extends \FormGent\Google\Task\Runner
    {
    }
    class Google_Utils_UriTemplate extends \FormGent\Google\Utils\UriTemplate
    {
    }
}
