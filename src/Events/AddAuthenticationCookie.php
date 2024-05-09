<?php
namespace Apie\Common\Events;

use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Common\Wrappers\TextEncrypter;
use HttpSoft\Cookie\CookieCreator;
use HttpSoft\Cookie\CookieManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds an authentication cookie to the response.
 */
class AddAuthenticationCookie implements EventSubscriberInterface
{
    public const COOKIE_NAME = '_apie_auth';

    public static function getSubscribedEvents(): array
    {
        return [ApieResponseCreated::class => 'onApieResponseCreated'];
    }

    public function onApieResponseCreated(ApieResponseCreated $event): void
    {
        $auth = $event->context->getContext(DecryptedAuthenticatedUser::class, false);
        if ($auth instanceof DecryptedAuthenticatedUser) {
            $textEncrypter = $event->context->getContext(TextEncrypter::class);
            assert($textEncrypter instanceof TextEncrypter);
            $manager = new CookieManager();
            $auth = $auth->refresh(time() + 3600);
            $value = $textEncrypter->encrypt(
                $auth->toNative()
            );

            $manager->set(
                CookieCreator::create(
                    self::COOKIE_NAME,
                    $value,
                    $auth->getExpireTime()
                )
            );
            $event->response = $manager->send($event->response);
        }
    }
}
