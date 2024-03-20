<?php
namespace Apie\Common\Actions;

use Apie\Common\ContextConstants;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\Actions\MethodActionInterface;
use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\StringList;
use Apie\Serializer\Exceptions\ValidationException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Runs a global method and returns the return value of this method.
 */
final class RunAction implements MethodActionInterface
{
    public function __construct(private ApieFacadeInterface $apieFacade)
    {
    }

    public static function isAuthorized(ApieContext $context, bool $runtimeChecks, bool $throwError = false): bool
    {
        return true; //TODO
    }

    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $context->withContext(ContextConstants::APIE_ACTION, __CLASS__)->checkAuthorization();
        $method = new ReflectionMethod(
            $context->getContext(ContextConstants::SERVICE_CLASS),
            $context->getContext(ContextConstants::METHOD_NAME)
        );
        $object = $method->isStatic()
            ? null
            : $context->getContext($context->getContext(ContextConstants::SERVICE_CLASS));
        try {
            $returnValue = $this->apieFacade->denormalizeOnMethodCall($rawContents, $object, $method, $context);
        } catch (ValidationException $error) {
            return ActionResponse::createClientError($this->apieFacade, $context, $error);
        }
        return ActionResponse::createRunSuccess($this->apieFacade, $context, $returnValue, $object);
    }

    public static function getRouteAttributes(ReflectionClass $class, ?ReflectionMethod $method = null): array
    {
        assert($method instanceof ReflectionMethod);
        return [
            ContextConstants::GLOBAL_METHOD => true,
            ContextConstants::SERVICE_CLASS => $method->getDeclaringClass()->name,
            ContextConstants::METHOD_NAME => $method->getName(),
            ContextConstants::DISPLAY_FORM => true,
        ];
    }

    private static function getNameToDisplay(?ReflectionMethod $method = null): string
    {
        if ($method === null) {
            return 'null';
        }
        $methodName = $method->getName();
        if ($methodName === '__invoke') {
            return $method->getDeclaringClass()->getShortName();
        }

        return $methodName;
    }

    public static function getDescription(ReflectionClass $class, ?ReflectionMethod $method = null): string
    {
        return 'Calls method ' . self::getNameToDisplay($method) . ' and returns return value.';
    }

    public static function getTags(ReflectionClass $class, ?ReflectionMethod $method = null): StringList
    {
        $class = $method ? $method->getDeclaringClass() : $class;
        return new StringList([$class->getShortName(), 'action']);
    }

    public static function getInputType(ReflectionClass $class, ?ReflectionMethod $method = null): ReflectionMethod
    {
        assert($method instanceof ReflectionMethod);
        return $method;
    }

    public static function getOutputType(ReflectionClass $class, ?ReflectionMethod $method = null): ReflectionMethod
    {
        assert($method instanceof ReflectionMethod);
        return $method;
    }

    public static function getPossibleActionResponseStatuses(?ReflectionMethod $method = null): ActionResponseStatusList
    {
        if (!$method || empty($method->getParameters())) {
            return new ActionResponseStatusList([
                ActionResponseStatus::SUCCESS,
            ]);
        }
        return new ActionResponseStatusList([
            ActionResponseStatus::CLIENT_ERROR,
            ActionResponseStatus::SUCCESS
        ]);
    }
}
