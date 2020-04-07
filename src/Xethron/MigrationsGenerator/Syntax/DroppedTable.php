<?php

namespace Xethron\MigrationsGenerator\Syntax;

use Illuminate\Support\Facades\Config;

class DroppedTable
{
    /**
     * Get string for dropping a table
     *
     * @param  string  $tableName
     * @param  string  $connection
     *
     * @return string
     */
    public function drop(string $tableName, string $connection): string
    {
        if (substr($tableName, 0,strlen(DB::connection($connection)->getTablePrefix()))
            == DB::connection($connection)->getTablePrefix())
        {
            $tableName = substr($tableName, strlen(DB::connection($connection)->getTablePrefix()));
        }
        
        if ($connection !== Config::get('database.default')) {
            $connectionMethod = 'connection(\''.$connection.'\')->';
        }

        return "Schema::".($connectionMethod ?? '')."drop('$tableName');";
    }
}
