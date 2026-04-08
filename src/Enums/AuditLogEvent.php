<?php
namespace Apie\Common\Enums;

enum AuditLogEvent: string
{
    case Created = 'Created';
    case Modified = 'Modified';
    case Replaced = 'Replaced';
}
