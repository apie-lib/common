<?php
namespace Apie\Tests\Common\Wrappers;

use Apie\Common\Wrappers\TextEncrypter;
use PHPUnit\Framework\TestCase;

class TextEncrypterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_encrypt_and_decrypt_text()
    {
        $testItem = new TextEncrypter('test');
        $actual = $testItem->encrypt('');
        $this->assertNotEquals('', $actual);
        $this->assertEquals('', $testItem->decrypt($actual));
    }
}
