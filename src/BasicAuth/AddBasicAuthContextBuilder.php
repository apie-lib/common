<?php
namespace Apie\Common\BasicAuth;

use Apie\Common\LoginService;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;
use Psr\Http\Message\ServerRequestInterface;

class AddBasicAuthContextBuilder implements ContextBuilderInterface
{
    public function process(ApieContext $context): ApieContext
    {
        $request = $context->getContext(ServerRequestInterface::class, false);
        $loginService = $context->getContext(LoginService::class, false);
        if ($request instanceof ServerRequestInterface && $loginService instanceof LoginService) {
            $authHeader = $request->getHeaderLine('Authorization');

            if (str_starts_with($authHeader, 'Basic ')) {
                $encodedCredentials = substr($authHeader, 6);
                $decoded = base64_decode($encodedCredentials, true);

                if ($decoded !== false && str_contains($decoded, ':')) {
                    [$username, $password] = explode(':', $decoded, 2);
                    $user = $loginService->authorize($username, $password);
                    $context = $context->withContext(ContextConstants::AUTHENTICATED_USER, $user);
                }
            }
        }

        return $context;
    }
}
