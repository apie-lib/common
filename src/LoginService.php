<?php
namespace Apie\Common;

use Apie\Common\ActionDefinitions\RunGlobalMethodDefinition;
use Apie\Common\Exceptions\CanNotLoginException;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Serializer\Serializer;
use Generator;
use ReflectionMethod;
use SensitiveParameter;

final class LoginService
{
    public function __construct(
        private readonly BoundedContextHashmap $boundedContextHashmap,
        private readonly ActionDefinitionProvider $actionDefinitionProvider,
        private readonly Serializer $serializer
    ) {
    }
    public function authorize(
        string $username,
        #[SensitiveParameter]
        string $password,
        ApieContext $apieContext = new ApieContext()
    ): EntityInterface {
        $prio = $apieContext->getContext(BoundedContextId::class, false);
        /** @var array<\Exception> */
        $errors = [];
        if ($prio instanceof BoundedContextId && isset($this->boundedContextHashmap[$prio->toNative()])) {
            $boundedContext = $this->boundedContextHashmap[$prio->toNative()];
            foreach ($this->provideLogins($boundedContext, $apieContext) as $actionDefinition) {
                $method = $actionDefinition->getMethod();
                try {
                    $constructedData = $this->constructData($method, $username, $password);
                    $response = $this->serializer->denormalizeOnMethodCall(
                        $constructedData,
                        null,
                        $method,
                        $apieContext
                    );
                    if ($response instanceof EntityInterface) {
                        return $response;
                    }
                } catch (\Exception) {
                }
            }
        }
        foreach ($this->boundedContextHashmap as $boundedContext) {
            foreach ($this->provideLogins($boundedContext, $apieContext) as $actionDefinition) {
                $method = $actionDefinition->getMethod();
                try {
                    $constructedData = $this->constructData($method, $username, $password);
                    $response = $this->serializer->denormalizeOnMethodCall(
                        $constructedData,
                        null,
                        $method,
                        $apieContext
                    );
                    if ($response instanceof EntityInterface) {
                        return $response;
                    }
                } catch (\Exception $error) {
                    $errors[] = $error;
                }
            }
        }
        throw new CanNotLoginException($errors);
    }

    private function constructData(ReflectionMethod $method, string|int|bool $username, #[SensitiveParameter] string|int|bool $password): ItemHashmap
    {
        // TODO: find which argument is the correct one.
        // @phpstan-ignore nullCoalesce.expr, nullsafe.neverNull
        $usernameField = $method->getParameters()[0]?->getName() ?? 'username';
        // @phpstan-ignore nullCoalesce.expr, nullsafe.neverNull
        $passwordField = $method->getParameters()[1]?->getName() ?? 'password';
        return new ItemHashmap([
            $usernameField => (string) $username,
            $passwordField => (string) $password
        ]);
    }

    /**
     * @return Generator<int, RunGlobalMethodDefinition>
     */
    private function provideLogins(BoundedContext $boundedContext, ApieContext $apieContext): \Generator
    {
        $actions = $this->actionDefinitionProvider->provideActionDefinitions(
            $boundedContext,
            $apieContext,
            runtimeChecks: true
        );
        foreach ($actions as $actionDefinition) {
            if ($actionDefinition instanceof RunGlobalMethodDefinition
                && $actionDefinition->getMethod()->getName() === 'verifyAuthentication') {
                yield $actionDefinition;
            }
        }
    }
}
