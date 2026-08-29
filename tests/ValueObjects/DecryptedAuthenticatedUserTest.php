<?php

namespace Apie\Tests\Common\ValueObjects;

use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Fixtures\Identifiers\UserAutoincrementIdentifier;
use Apie\Fixtures\TestHelpers\ValueObjectTestCase;

class DecryptedAuthenticatedUserTest extends ValueObjectTestCase
{
    public static function className(): string
    {
        return DecryptedAuthenticatedUser::class;
    }

    public static function provideFromNative(): array
    {
        $validId = UserAutoincrementIdentifier::class . '/test/10/0';
        return [
            'valid expired id' => [$validId, $validId]
        ];
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'decryptedauthenticateduser'
        ];
    }
}