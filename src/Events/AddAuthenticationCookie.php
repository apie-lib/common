<?php
namespace Apie\Common\Events;

use Apie\Common\ContextConstants;
use Apie\Common\Wrappers\TextEncrypter;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\ValueObjects\Utils;
use HttpSoft\Cookie\CookieCreator;
use HttpSoft\Cookie\CookieManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds an authentication cookie to the response.
 */
class AddAuthenticationCookie implements EventSubscriberInterface
{
    public const COOKIE_NAME = '_apie_auth';

    private const COOKIE_EXPIRE = 3600;

    public static function getSubscribedEvents()
    {
        return [ApieResponseCreated::class => 'onApieResponseCreated'];
    }

    public function onApieResponseCreated(ApieResponseCreated $event): void
    {
        $auth = $event->context->getContext(ContextConstants::AUTHENTICATED_USER, false);
        if ($auth instanceof EntityInterface) {
            $textEncrypter = $event->context->getContext(TextEncrypter::class);
            assert($textEncrypter instanceof TextEncrypter);
            $manager = new CookieManager();
            $value = $textEncrypter->encrypt(
                get_class($auth)
                . '/'
                . Utils::toString($auth->getId())
            );
            $manager->set(
                CookieCreator::create(
                    self::COOKIE_NAME,
                    $value,
                    time() + self::COOKIE_EXPIRE
                )
            );
            $event->response = $manager->send($event->response);
        }
    }
}
