## Install via Composer

```php
 "require" : {
        "icomefromthenet/laterjob" :"dev-master"
 }
```

## Using the Git Repo

Make sure to run composer to download the dependecies, if you want to run the tests you will need to use the ``--dev`` option.

You can use the [migration library](https://github.com/icomefromthenet/Migrations) to setup your database schema.

## Setup the Metric script.

There is included a symfony2 console command under ``LaterJob\Command\MonitorCommand`` this script should be run 4 times an hour.
However you will need to assign the queue to the helper as shown below. 

```php

use Symfony\Component\Console\Application,
    Symfony\Component\Console\Helper\HelperSet,
    LaterJob\Command\QueueHelper,
    LaterJob\Command\MonitorCommand,
    LaterJob\Command\PurgeCommand;



$queue = '.... a valid queue instance';

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

$application->add(new MonitorCommand('app:monitor'));
$application->add(new PurgeCommand('app:purge'));

$application->getHelperSet()->set(new QueueHelper($queue));

# run the console
$application->run();

```
