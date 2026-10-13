<?php
namespace Apie\Common\StreamFactory;

use Apie\Core\Actions\ActionResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(StreamFactoryInterface::class)]
interface StreamFactoryInterface
{
    public function createStream(
        ServerRequestInterface $request,
        ActionResponse $output
    ): ?ResponseInterface;
}
