<?php
namespace Apie\Common\Other;

use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizeSelfInterface;

interface EntitySnapshotInstance extends NormalizeSelfInterface
{
    public function applies(ApieSerializerContext $apieSerializerContext): bool;
}
