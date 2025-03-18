<?php
namespace Apie\Common\Wrappers;

use Defuse\Crypto\Crypto;
use SensitiveParameter;

final class TextEncrypter
{
    public function __construct(#[SensitiveParameter] private readonly string $encryptionKey)
    {
    }

    public function encrypt(string $text): string
    {
        return Crypto::encryptWithPassword($text, $this->encryptionKey);
    }

    public function decrypt(string $encryptedText): string
    {
        return Crypto::decryptWithPassword($encryptedText, $this->encryptionKey);
    }
}
