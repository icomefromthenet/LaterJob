#!/usr/bin/env php
<?php
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration as DoctrineConfiguration;

use LaterJob\Command\Runner;
use LaterJob\Command\QueueHelper;
use LaterJob\Command\Cleanup;
use LaterJob\Command\Monitor;
use LaterJob\Queue;
use LaterJob\Log\MonologBridge;
use LaterJob\UUID;
use LaterJob\Util\MersenneRandom;
use LaterJob\Loader\ConfigLoader;
use LaterJob\Loader\ModelLoader;
use LaterJob\Loader\EventSubscriber;

use DBALGateway\Feature\StreamQueryLogger;


# require composer autoloader
require __DIR__. DIRECTORY_SEPARATOR  .'..'. DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR .'autoload.php';

# setup monolog
$logger = new Logger('mysql');
$logger->pushHandler(new StreamHandler('/var/tmp/laterjob.log'));

# setup doctrine
$doctrine = DriverManager::getConnection( array(
                'dbname'   => 'later_job',
                'user'     => 'root',
                'password' => '',
                'host'     => 'localhost',
                'driver'   => 'pdo_mysql',
            ), new DoctrineConfiguration());

$doctrine->getConfiguration()->setSQLLogger(new StreamQueryLogger($logger));
            
# setup the queue
$queue = new Queue(new EventDispatcher(),
                   new MonologBridge($logger),
                   array(
                        'worker' => array(
                            'jobs_process'      => 300,
                            'mean_runtime'      => (60*60*1),
                            'cron_script'       => '* * * * *',
                            'job_lock_timeout'  => (60*60*4),
                            'worker_name'       => 'bobemail'
                        ),
                        'queue' => array(
                            'mean_service_time' => (60*60*1),
                            'max_retry'         => 3,
                            'retry_timer'       => (60*60*1)
                        ),
                        'db' => array(
                            'transition_table' => 'later_job_transition',
                            'queue_table'      => 'later_job_queue',
                            'monitor_table'    => 'later_job_monitor'
                        )
                    ),
                    new UUID(new MersenneRandom()),
                    new ConfigLoader(),
                    new ModelLoader($doctrine),
                    new EventSubscriber()
                );

# create new console application
$application = new Application();

# add the commands
$application->add(new Runner('app:runner'));
$application->add(new Cleanup('app:cleanup'));
$application->add(new Monitor('app:monitor'));

# add the helper
$application->getHelperSet()->set(new QueueHelper($queue));

# run the console
$application->run();

