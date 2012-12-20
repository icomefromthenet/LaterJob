## The Queue Constructor

The queues constructor has the following signature.

```php
public function __construct(EventDispatcherInterface $dispatcher, 
                            LogInterface $logger, 
                            array $options, 
                            UUID $uuid, 
                            LoaderInterface $config_loader, 
                            LoaderInterface $model_loader,
                            LoaderInterface $event_loader){}
```

1. EventDispatcherInterface - Symfony event dispatcher. (if your running two or more queues in same script use a new instance for each).
2. LogInterface - This is an instance of a logger, a monolog bridge is included for convenience.
3. array $options - The configuration options of this queue.
4. UUID - instance of the UUID generator included with laterjob.
5. LoaderInterface $config_loader - Parse and validate the config options passed in earlier.
6. LoaderInterface $model_loader  - Load the model and bind them to a doctrine connection.
7. LoaderInterface $event_loader  - Event subscriber will bind model to the events fired by the QueueAPI.


## Example Cron Job

This example can be found under [LaterJob\Command\RunnerCommand](https://github.com/icomefromthenet/LaterJob/blob/master/src/LaterJob/Command/RunnerCommand.php).

```php

    /**
    *  Example Runner for a job
    *
    * @param InputInterface $input An InputInterface instance
    * @param OutputInterface $output An OutputInterface instance
    * @return null|integer null or 0 if everything went fine, or an error code
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $this->getHelper('queue')->getQueue();
        
        # output to console.
        $queue->getDispatcher()->addSubscriber(new ConsoleSubscriber($output));
        
        # fetch a worker instance from queue api
        $worker = $queue->worker();
        
        
        try {
            # start the worker, record started state in the activity log
            $worker->start(new DateTime());
            
            # load the allocator, allocates work from pool of locked jobs 
            $allocator = $worker->receive(new DateTime());
            
            # fetch worker handle
            $handle = $worker->getId();
            
            # iterate over jobs to proces
	    foreach($allocator as $job) {
                
                # inner error catch so error job wont stop worker processing
                try {
                    
                    # start job, will transition job from added to starting in the activity log
                    $job->start($handle,new DateTime());
                    
                    # simulate time taken by a single job to process
                    $sleep = (integer) mt_rand(0,15);    
                    sleep($sleep);        
                
                    # simulate a chance to fail    
                    $value = $this->floatRand(0,1);
                
                    if($value <= 0.08) {
                        # Setting the rety left to 0 to force a `failed` transition.
                        # the api will decerement the count for you normally.
                        $job->getStorage()->setRetryLeft(0);
                        
                        # throw exception to be caught by inner handler
                        throw new LaterJobException('failure has occured');
                    }
                
                    # cause the `error` transition if retry counter > 0
                    if($value <= 0.08) {
                        throw new LaterJobException('error has occured');
                    }
                    
                    # normal execution finished, transition from starting to finished in activity log
                    $job->finish($handle,new DateTime());
                }
                catch(LaterJobException $e) {
                    
                    # transiton to failed or error  up to the developer to handle
                    # which transiton to pick, you may want the option to ignore failures
                    # and go to failed.
                    if($job->getRetryCount() > 0) {
                        $job->error($handle,new DateTime(),$e->getMessage());    
                    }
                    else {
                        $job->fail($handle,new DateTime(),$e->getMessage());    
                    }
                }
            
            }
            
	    # finish the worker, will record finished state in activity log
            $worker->finish(new DateTime());
            
        } catch(LaterJobException $e) {
            # transition to error state, will record error state in activity log
            $worker->error($handle,new DateTime(),$e->getMessage());
            throw $e;            
        }
        
	return 0;
    }


```

