<?php

namespace App\Repositories;

class GetDatabasesRepository extends DatabaseRepository
{
    public function getAllDatabases(): array
    {
        return $this->getConnection()->query('SHOW DATABASES')->fetchAll(\PDO::FETCH_OBJ);
    }
}