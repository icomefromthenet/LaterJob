<?php
/*
 * Development Config File
 *
 */

# ----------------------------------------------------
# Set Silex to Debug mode
# 
# ---------------------------------------------------

$app["debug"] = true;



# ----------------------------------------------------
# Set Monolog
# ---------------------------------------------------

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => $app['cache_path'].'logs/laterjob.log',
));

# ----------------------------------------------------
# Setup Database and PDOSessionHandler
# 
# ---------------------------------------------------

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'later_job',
        'user'      => 'root',
        'password'  => '',
    )
));
