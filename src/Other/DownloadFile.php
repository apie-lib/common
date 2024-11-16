<?php
namespace Apie\Common\Other;

use Apie\Core\ValueObjects\UrlRouteDefinition;
use Psr\Http\Message\UploadedFileInterface;

/**
 * This class is only for OpenAPI specs
 * @codeCoverageIgnore
 */
class DownloadFile
{
    public function __construct(public string $id)
    {
    }

    public function download(UrlRouteDefinition $properties): UploadedFileInterface
    {
        throw new \LogicException('fake method for getInputType');
    }
}
