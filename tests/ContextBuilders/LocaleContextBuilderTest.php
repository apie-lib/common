<?php
namespace Apie\Tests\Common\ContextBuilders;

use Apie\Common\ContextBuilders\LocaleContextBuilder;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Identifiers\Identifier;
use Apie\Core\Identifiers\KebabCaseSlug;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class LocaleContextBuilderTest extends TestCase
{
    public function testProcessWithHeaders()
    {
        $testObj = new LocaleContextBuilder(null);
        $request = new ServerRequest('GET', 'http://localhost', [
            'Accept-Language' => 'da, en;q=0.7',
            'Content-Language' => 'nl-be'
        ]);
        $context = (new ApieContext())->registerInstance($request);

        $actual = $testObj->process($context);
        $this->assertEquals('da', $actual->getContext(ContextConstants::ACCEPT_LOCALE));
        $this->assertEquals(['da', 'en'], $actual->getContext(ContextConstants::ACCEPTED_LOCALES));
        $this->assertEquals('nl-be', $actual->getContext(ContextConstants::LOCALE));
        $this->assertEquals('nl-be', $actual->getContext(ContextConstants::DATA_LOCALE));
    }

    public function testProcessWithHeadersAndTypehint()
    {
        $testObj = new LocaleContextBuilder(KebabCaseSlug::class);
        $request = new ServerRequest('GET', 'http://localhost', [
            'Accept-Language' => 'incorrect name, en;q=0.7',
            'Content-Language' => 'nl-be'
        ]);
        $context = (new ApieContext())->registerInstance($request);

        $actual = $testObj->process($context);
        $this->assertEquals('en', $actual->getContext(ContextConstants::ACCEPT_LOCALE));
        $this->assertEquals(['incorrect name', 'en'], $actual->getContext(ContextConstants::ACCEPTED_LOCALES));
        $this->assertEquals('nl-be', $actual->getContext(ContextConstants::LOCALE));
        $this->assertEquals('nl-be', $actual->getContext(ContextConstants::DATA_LOCALE));
    }

    public function testProcessWithInvalidAcceptHeader()
    {
        $testObj = new LocaleContextBuilder(KebabCaseSlug::class);
        $request = new ServerRequest('GET', 'http://localhost', [
            'Accept-Language' => 'non sense, helllo there;q=0.7',
            'Content-Language' => 'nl-be'
        ]);
        $context = (new ApieContext())->registerInstance($request);

        $this->expectException(\Apie\Serializer\Exceptions\NotAcceptedException::class);
        $testObj->process($context);
    }

    public function testProcessWithInvalidContentLanguageHeader()
    {
        $testObj = new LocaleContextBuilder(Identifier::class);
        $request = new ServerRequest('GET', 'http://localhost', [
            'Content-Language' => 'non-sense, helllo there;q=0.7',
            'Accept-Language' => 'nl-be'
        ]);
        $context = (new ApieContext())->registerInstance($request);

        $this->expectException(\Apie\Serializer\Exceptions\NotAcceptedException::class);
        $testObj->process($context);
    }

    public function testProcessWithoutHeaders()
    {
        $testObj = new LocaleContextBuilder(null);
        $request = new ServerRequest('GET', 'http://localhost');
        $context = (new ApieContext())->registerInstance($request);

        $actual = $testObj->process($context);
        $this->assertFalse($actual->hasContext(ContextConstants::ACCEPT_LOCALE));
        $this->assertFalse($actual->hasContext(ContextConstants::ACCEPTED_LOCALES));
        $this->assertFalse($actual->hasContext(ContextConstants::LOCALE));
        $this->assertFalse($actual->hasContext(ContextConstants::DATA_LOCALE));
    }
}
