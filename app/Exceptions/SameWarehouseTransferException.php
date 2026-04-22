<?php

namespace App\Exceptions;

use Exception;

class SameWarehouseTransferException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Source and destination cannot be the same warehouse.',
            422
        );
    }
}
