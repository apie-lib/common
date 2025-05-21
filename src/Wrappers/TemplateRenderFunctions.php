<?php
namespace Apie\Common\Wrappers;

use Apie\Core\Context\ApieContext;
use Apie\HtmlBuilders\ErrorHandler\StacktraceRenderer;
use Apie\HtmlBuilders\Interfaces\ComponentInterface;
use Throwable;

trait TemplateRenderFunctions
{
    public function renderApieCmsData(
        mixed $input,
        ApieContext $apieContext = new ApieContext()
    ): string {
        return $this->renderApieComponent(
            $this->fieldDisplayComponentFactory->createDisplayFor($input, $apieContext),
            $apieContext
        );
    }

    public function renderStacktrace(Throwable $throwable): string
    {
        $renderer = new StacktraceRenderer($throwable);
        return (string) $renderer;
    }

    public function renderApieComponent(
        ComponentInterface $component,
        ApieContext $apieContext = new ApieContext()
    ): string {
        return $this->renderer->render($component, $apieContext);
    }
}
