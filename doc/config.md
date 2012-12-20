#Startup Config

Three components that can be configured **worker,queue,db** (example is included below).


```php
        $config = array('worker' => array(
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
         );               
```

## Worker

###1. jobs_process
The maximum jobs a worker will process, i.e. the maximum throughput.

###2. mean_runtime 
The baseline runtime of a worker in seconds, useful to compare against actual runtime. 

###3. cron_script
The cron argument used to launch the worker. The queue API can output a schedule based on this paramater.

###4. job_lock_timeout
The length of the lockout timer in seconds, a long running worker should have a long lockout.

###5. worker_name
A unique name of the worker will identify among an applications logs.

## Queue

###1. mean_service_time
Mean service time of a job, i.e time taken from adding to completion. Useful in comparisions against actual.

###2. max_retry
Number of times a job will be run before it transitions to failed state.

###3. retry_timer
Time in seconds before the worker will retry a job that transitioned to error state.

##Database (db)

The table names which by default are

```php

    'db' => array(
                'transition_table' => 'later_job_transition',
                'queue_table'      => 'later_job_queue',
                'monitor_table'    => 'later_job_monitor'
    )

```

If **two queues** are running the later will need a **different set of tables**, they can be set here.
