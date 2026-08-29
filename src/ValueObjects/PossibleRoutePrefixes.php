<?php
namespace Apie\Common\ValueObjects;

use Apie\Core\Attributes\FakeMethod;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\Core\ValueObjects\Utils;
use Faker\Generator;
use Stringable;

#[FakeMethod('createRandom')]
final class PossibleRoutePrefixes implements ValueObjectInterface, Stringable
{
    /**
     * @param array<int, string> $internal
     */
    private function __construct(
        private array $internal
    ) {
    }

    public static function createRandom(Generator $faker): static
    {
        $val = [];
        if ($faker->boolean()) {
            $val[] = 'api';
        }
        if ($faker->boolean()) {
            $val[] = 'cms';
        }

        return static::fromNative($val);
    }

    /**
     * @return static
     */
    public static function fromNative(mixed $input): self
    {
        if ($input instanceof ValueObjectInterface) {
            $input = $input->toNative();
        }
        if (is_string($input)) {
            return new PossibleRoutePrefixes([$input]);
        }
        return new PossibleRoutePrefixes(Utils::toArray($input));
    }
    /**
     * @return array<string|int, mixed>
     */
    public function toNative(): array
    {
        return $this->internal;
    }

    /**
     * @return array<string, string>
     */
    public function getRouteRequirements(): array
    {
        return match (count($this->internal)) {
            0 => [],
            1 => [],
            // TODO; quote regular expression?
            default => ['prefix' => implode('|', $this->internal)],
        };
    }

    public function __toString(): string
    {
        switch (count($this->internal)) {
            case 0:
                return '/';
            case 1:
                return '/' . reset($this->internal) . '/';
            default:
                return '/{prefix}/';
        }
    }
}
