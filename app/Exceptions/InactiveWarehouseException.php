<?php

namespace App\Exceptions;

use Exception;

class InactiveWarehouseException extends Exception
{
    public function __construct(string $warehouseName)
    {
        parent::__construct(
            "Warehouse \"{$warehouseName}\" is inactive and cannot participate in transfers.",
            422
        );
    }
}
