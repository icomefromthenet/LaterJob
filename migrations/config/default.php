<?php

/* Database Config file */

return array (
  'type' => 'pdo_mysql',
  'schema' => 'c9',
  'user' => $_SERVER['C9_USER'],
  'password' => '',
  'host' => $_SERVER['IP'],
  'port' => 3306,
  'migration_table' => 'migrations_data',
  'socket' => false,
  'path' => NULL,
  'memory' => NULL,
  'charset' => false,
);


/* End of Config File */
