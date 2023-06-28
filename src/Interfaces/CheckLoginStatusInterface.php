<?php
namespace Apie\Common\Interfaces;

/**
 * If an entity adds this interface, we do extra checks on existing sessions to see if you are still allowed to be logged
 * in.
 */
interface CheckLoginStatusInterface
{
    public function isDisabled(): bool;
}
