<?php
namespace Apie\Common\ContextBuilders;

use Apie\Common\ContextBuilders\Exceptions\WrongTokenException;
use Apie\Common\Events\AddAuthenticationCookie;
use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Common\Wrappers\TextEncrypter;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class CheckAuthenticatedContextBuilder implements ContextBuilderInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function process(ApieContext $context): ApieContext
    {
        if (!$context->hasContext(DecryptedAuthenticatedUser::class)) {
            $textEncrypter = $context->getContext(TextEncrypter::class, false);
            $request = $context->getContext(ServerRequestInterface::class, false);
            $datalayer = $context->getContext(ApieDatalayer::class, false);
            if ($textEncrypter instanceof TextEncrypter
                && $request instanceof ServerRequestInterface
                && $datalayer instanceof ApieDatalayer) {
                $name = $request->getCookieParams()[AddAuthenticationCookie::COOKIE_NAME] ?? null;
                $this->logger->debug($request->getUri()->__toString() . ' ' . ($name ?? 'no cookie'));
                if ($name) {
                    try {
                        $decryptedUserId = new DecryptedAuthenticatedUser($textEncrypter->decrypt($name));
                        if ($decryptedUserId->isExpired()) {
                            throw new \LogicException('Token is expired!');
                        }
                        $authenticated = $datalayer->find($decryptedUserId->getId());
                        $context = $context
                            ->withContext(ContextConstants::AUTHENTICATED_USER, $authenticated)
                            ->registerInstance($decryptedUserId);
                    } catch (Exception $error) {
                        $this->logger->error(
                            'Error decrypting auth cookie: ' . $error->getMessage(),
                            ['error' => $error]
                        );

                        throw new WrongTokenException($error);
                    }
                }
            } else {
                $this->logger->debug(
                    sprintf(
                        'Could not determine authentication cookie as a dependency is missing: encrypter: %s, request: %s, data layer: %s',
                        $textEncrypter instanceof TextEncrypter ? 'true' : 'false',
                        $request instanceof ServerRequestInterface ? 'true' : 'false',
                        $datalayer instanceof ApieDatalayer ? 'true' : 'false'
                    )
                );
            }
        }
        return $context;
    }
}
