<?php
# ----------------------------------------------------
# App Variables
# ---------------------------------------------------

$app['resources_path']  = realpath(__DIR__ . "/../../resources") . DIRECTORY_SEPARATOR;
$app['cache_path']      = realpath(__DIR__ . "/../../resources/cache") . DIRECTORY_SEPARATOR;
$app['web_root']        = realpath(__DIR__  . "/../../web") . DIRECTORY_SEPARATOR;



# ----------------------------------------------------
# Load Environment Config File
# 
# ---------------------------------------------------

$app['environment'] = @$_SERVER["APP_ENVIRONMENT"] ?: "development";

# Load a environment specific configuration file
if (file_exists(__DIR__ . '/environments/'.$app['environment'].'.php')) {
    require (__DIR__ . '/environments/'.$app['environment'].'.php');
} else {
    throw new \RuntimeException('Env {'.$app['environment'].'}.php does not exist');
}






