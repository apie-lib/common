<?php
namespace Apie\Common;

use Apie\Core\Actions\ActionInterface;

interface ApieFacadeAction extends ActionInterface
{
    public function __construct(ApieFacade $apieFacade);
}
