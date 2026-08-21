<?php

return [
    'host' => getenv('MYPETTS_DB_HOST') ?: 'localhost',
    'dbname' => getenv('MYPETTS_DB_NAME') ?: 'mypetts',
    'port' => getenv('MYPETTS_DB_PORT') ?: '3306',
    'usuario' => getenv('MYPETTS_DB_USER') ?: 'admin',
    'clave' => getenv('MYPETTS_DB_PASS') ?: 'root',
];
