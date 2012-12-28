<?php
# ----------------------------------------------------
# Include Composer  Autoloader
# 
# ---------------------------------------------------

require_once(__DIR__ . "/../../vendor/autoload.php");


# ----------------------------------------------------
# Create the application
# 
# ---------------------------------------------------


$app = new Silex\Application();


#------------------------------------------------------------------
# Add Parse for json requests body
#
#------------------------------------------------------------------

$app->before(function (Symfony\Component\HttpFoundation\Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

#------------------------------------------------------------------
# Load the Configuration
#
#------------------------------------------------------------------

require (__DIR__ . "/resources/config/application.php");


# ----------------------------------------------------
# Load ValidatorServiceProvider
# 
# ---------------------------------------------------

$app->register(new Silex\Provider\ValidatorServiceProvider());


# ----------------------------------------------------
# Setup LaterJob Queue
# 
# ---------------------------------------------------

$app->register(new LaterJobApi\Provider\QueueServiceProvider('mailqueue'), array(
              'mailqueue.options' => array(
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
                    )
                )
            );


# ----------------------------------------------------
# Setup LaterJob API
# 
# ---------------------------------------------------

$app->register(new LaterJobApi\Provider\APIServiceProvider('mailqueue'.LaterJobApi\Provider\QueueServiceProvider::QUEUE), array());

return $app;