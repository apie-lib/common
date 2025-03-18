<?php
namespace Apie\Common\RouteDefinitions;

use Apie\Common\Enums\UrlPrefix;
use Apie\Common\Interfaces\HasRouteDefinition;
use Apie\Common\ValueObjects\PossibleRoutePrefixes;

final class PossibleRoutePrefixProvider
{
    /**
     * @var array<string, string>
     */
    private array $prefixes;

    public function __construct(
        private readonly string $cmsPath,
        private readonly string $apiPath
    ) {
        $this->prefixes = [
            UrlPrefix::CMS->value => ltrim($this->cmsPath, '/'),
            UrlPrefix::API->value => ltrim($this->apiPath, '/'),
        ];
    }

    public function getPossiblePrefixes(HasRouteDefinition $routeDefinition): PossibleRoutePrefixes
    {
        $result = [];
        foreach ($routeDefinition->getUrlPrefixes() as $urlPrefix) {
            if (isset($this->prefixes[$urlPrefix->value])) {
                $result[] = $this->prefixes[$urlPrefix->value];
            }
        }
        return PossibleRoutePrefixes::fromNative($result);
    }
}
