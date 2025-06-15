<?php

namespace App\Http\Models;

use Doctrine\DBAL\Connection;

abstract class BaseModel
{
    protected Connection $database;

    public function __construct(Connection $database)
    {
        $this->database = $database;

    }
}
