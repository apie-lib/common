<?php
namespace Apie\Tests\Common\Wrappers;

use Apie\Common\Wrappers\TextEncrypter;
use PHPUnit\Framework\TestCase;

class TextEncrypterTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_encrypt_and_decrypt_empty_string()
    {
        $testItem = new TextEncrypter('test');
        $actual = $testItem->encrypt('');
        $this->assertNotEquals('', $actual);
        $this->assertEquals('', $testItem->decrypt($actual));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_encrypt_and_decrypt_a_text()
    {
        $testItem = new TextEncrypter('test');
        $actual = $testItem->encrypt('This text is written within a test');
        $this->assertNotEquals('', $actual);
        $this->assertEquals('This text is written within a test', $testItem->decrypt($actual));
    }
}
