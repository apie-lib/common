<?php
namespace Apie\Common\Events;

use Apie\Core\Context\ApieContext;
use Psr\Http\Message\ResponseInterface;

final class ApieResponseCreated
{
    public function __construct(public ResponseInterface $response, public ApieContext $context)
    {
    }
}
