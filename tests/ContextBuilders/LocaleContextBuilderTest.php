<?php
namespace Apie\Tests\Common\ContextBuilders;

use Apie\Common\ContextBuilders\LocaleContextBuilder;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class LocaleContextBuilderTest extends TestCase
{
    public function testProcessWithHeaders()
    {
        $testObj = new LocaleContextBuilder();
        $request = new ServerRequest('GET', 'http://localhost', [
            'Accept-Language' => 'da, en;q=0.7',
            'Content-Language' => 'nl-BE'
        ]);
        $context = (new ApieContext())->registerInstance($request);

        $actual = $testObj->process($context);
        $this->assertEquals('da', $actual->getContext(ContextConstants::ACCEPT_LOCALE));
        $this->assertEquals(['da', 'en'], $actual->getContext(ContextConstants::ACCEPTED_LOCALES));
        $this->assertEquals('nl-BE', $actual->getContext(ContextConstants::LOCALE));
        $this->assertEquals('nl-BE', $actual->getContext(ContextConstants::DATA_LOCALE));
    }

    public function testProcessWithoutHeaders()
    {
        $testObj = new LocaleContextBuilder();
        $request = new ServerRequest('GET', 'http://localhost');
        $context = (new ApieContext())->registerInstance($request);

        $actual = $testObj->process($context);
        $this->assertFalse($actual->hasContext(ContextConstants::ACCEPT_LOCALE));
        $this->assertFalse($actual->hasContext(ContextConstants::ACCEPTED_LOCALES));
        $this->assertFalse($actual->hasContext(ContextConstants::LOCALE));
        $this->assertFalse($actual->hasContext(ContextConstants::DATA_LOCALE));
    }
}
