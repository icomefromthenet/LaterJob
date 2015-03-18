#Concepts

## Workers.

A Worker requires the cron and not a daemon and  you may have overlapping executions as each worker will  obtain a lock jobs it will process. The lock is temporal and does expire and if a script exits due to unforeseen circumstances the jobs will be picked up by a later worker execution.

I would advise not too run more than one cron job for the same worker. Single cron job with quick execution time (every minute) should be sufficent for a database backed queue. If your looking for more capacity might be time to switch away from a database queue.

A worker has the following 3 states, in the activity log

1. Starting.
2. Finished.
3. Error. 

##Jobs.

A job will be processed by a worker, when job is added to the queue it is given the inital state of ``added``. When worker begins processing a job will transition into the ``starting`` state, from there it can transition to one of 3 states. The frist is ``finished`` this state a job has sucessfuly been processed. The second states is ``error`` the job could not be completed but can be retried later. The last possible state is the ``failed`` in which a job can not be completed and will not be executed again.

A job has a ``retry count`` the default number is set in the config, the retry also has a temporal lock to prevent poisioning. The timer can be set in the config to with a short running worker i that the job will be picked up by a later execution after the expiry of the timers.

The job states are, in the activity log

1. Added (inital)
2. Starting
3. Finished (last state)
4. Error
5. Failed (last state)

## Activity.

Each time a worker and job transtion the action is recorded in the activity log, this log can be accessed via the activity API and is used to drive metrics delivered by the monitor. This activity should be purged from time to time using a cron script and the purge method on the activity API. I would advise every 2 weeks purge. After the activity is monitored its wasted space.

## Monitor.

To manage the queue a monitor is run via a cron script once an hour. The monitor API will run over the activity for the last hour and generate metrics which can be combined into periods such as 6hours , 12 hours , 1 day, 1 week to identify trends and observe the health of a queue.

The monitor will only run once for that hour, a cron script that runs 4 times and hour will only execute the first time. I would run the monitor 4 times an hour to catch short periods of downtime.

If the hour is missed, you will be responsible for running the command with a timestamp argument for that missed hour. I have assumed that if the server down for extended period there won't be much activity to monitor.

