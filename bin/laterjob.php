#!/usr/bin/env php
<?php
use Symfony\Component\Console\Application,
    Symfony\Component\Console\Helper\HelperSet,
    Symfony\Component\EventDispatcher\EventDispatcher;

use Monolog\Logger,
    Monolog\Handler\StreamHandler;

use Doctrine\DBAL\DriverManager,
    Doctrine\DBAL\Configuration as DoctrineConfiguration;

use DBALGateway\Feature\StreamQueryLogger;

use LaterJob\Command\RunnerCommand,
    LaterJob\Command\QueueHelper,
    LaterJob\Command\CleanupCommand,
    LaterJob\Command\MonitorCommand,
    LaterJob\Command\PurgeCommand,
    LaterJob\Queue,
    LaterJob\Log\MonologBridge,
    LaterJob\UUID,
    LaterJob\Util\MersenneRandom,
    LaterJob\Loader\ConfigLoader,
    LaterJob\Loader\ModelLoader,
    LaterJob\Loader\EventSubscriber;

/*
|--------------------------------------------------------------------------
| Require Autoloader
|--------------------------------------------------------------------------
|
| Using Composer as out autoloader. 
|
*/
require __DIR__. DIRECTORY_SEPARATOR  .'..'. DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR .'autoload.php';

/*
|--------------------------------------------------------------------------
| Monolog
|--------------------------------------------------------------------------
|
| Using Monolog and StreamHandler to log events to a file under /var/tmp/laterjob.log
|
*/

$logger_stream = new StreamHandler('/var/tmp/laterjob.log');
$logger_mysql = new Logger('mysql');
$logger_app   = new Logger('app');
$logger_mysql->pushHandler($logger_stream);
$logger_app->pushHandler($logger_stream);
/*
|--------------------------------------------------------------------------
| Doctrine and Query Logger
|--------------------------------------------------------------------------
|
| Setup doctrine access and bind a custom logging class (StreamQueryLogger)  
| which use monolog to push queries to the log file.
|
| Would advise NOT to use the query log in production setting.
|
*/
$doctrine = DriverManager::getConnection( array(
                'dbname'   => 'later_job',
                'user'     => 'root',
                'password' => '',
                'host'     => 'localhost',
                'driver'   => 'pdo_mysql',
            ), new DoctrineConfiguration());

$doctrine->getConfiguration()->setSQLLogger(new StreamQueryLogger($logger_mysql));
            
/*
|--------------------------------------------------------------------------
| Setup LaterJob Queue
|--------------------------------------------------------------------------
|
| The Queue is configured here, If you have customized the queue you
| will need to replace the default loaders:
|
| 1. ConfigLoader - Parse the config and provides the Database Meta Data.
| 2. ModelLoader($doctrine) - Loads the models.
| 3. EventSubscriber() - Binds events that model need to respond to ie events from the API Classes.
|
*/
$queue = new Queue(new EventDispatcher(),
                   new MonologBridge($logger_app),
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

/*
|--------------------------------------------------------------------------
| Setup Symfony2 Console Application
|--------------------------------------------------------------------------
|
| Using Symfony2 console and provides a helper (QueueHelper) 
| allowing the commands to access the above instanced Queue API
|
*/
$application = new Application();

$application->add(new RunnerCommand('app:runner'));
$application->add(new CleanupCommand('app:cleanup'));
$application->add(new MonitorCommand('app:monitor'));
$application->add(new PurgeCommand('app:purge'));

$application->getHelperSet()->set(new QueueHelper($queue));

# run the console
$application->run();

