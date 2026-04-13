<?php
namespace Apie\Common\Enums;

use Apie\Core\Attributes\Description;

enum AuditLogEvent: string
{
    #[Description('Object was created')]
    case Created = 'Created';
    #[Description('Object was modified')]
    case Modified = 'Modified';
    #[Description('Object was created and ignored the previous object')]
    case Replaced = 'Replaced';
    #[Description('Object was removed')]
    case Removed = 'Removed';
    #[Description('Object was read')]
    case Read = 'Read';
    #[Description('An action was performed on the object')]
    case MethodCalled = 'MethodCalled';
}
