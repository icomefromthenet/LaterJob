<?php
namespace LaterJob;

use Symfony\Components\EventDispatcher\EventDispatcherInterface;
use Pimple;
use DateTime;
use LaterJob\Log\LogInterface;
use LaterJob\Worker;
use LaterJob\UUID;
use LaterJob\Loader\LoaderInterface;

/**
  *  This class will Provide API to lookup and fetch
  *  a Worker or create new worker for job storage.
  *
  *  Also the DI for a given queue by extending pimple
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Queue extends Pimple
{
    
    /**
      *  Class Constructor
      *  Boot the queue loading config and creating all dependecies
      *
      *  @access public
      *  @return \LaterJob\Queue;
      */    
    public function __construct(EventDispatcherInterface $dispatcher, LogInterface $logger, array $options, UUID $uuid, LoaderInterface $config_loader, LoaderInterface $model_loader,LoaderInterface $event_loader)
    {
        
        $this['dispatcher'] = $dispatcher;
        $this['logger']     = $logger;
        $this['uuid']       = $uuid;
        $this['options']    = $options;
        
        $logger->info('Starting loading LaterJob queue');
        
        $config_loader->boot($this);
        $model_loader->boot($this);
        $event_loader->boot($this);
        
        $logger->info('Finished loading LaterJob queue');
    }
    
    
    //------------------------------------------------------------------
    # Public Interface
    
    /**
      *   Add a job to the queue
      *
      *   @access public
      *   @return boolean true if job added
      *   @throws LaterJob\Exception
      */
    public function send(DateTime $now,$job_data)
    {
        $uuid          = $this['uuid'];
        
        # populate the storage object
        
        
        # generate the uuid for job
        $uuid->v3($uuid->v4(),md5(json_encode($job_data)));
        
        # raise the add event
        
        
        # no exceptions
        return true;            
    }
    
     /**
      *   Setup a new instance of a worker.
      *
      *   @access public
      *   @return LaterJob\Worker
      *   @throws LaterJob\Exception
      */
    public function worker()
    {
        $event         = $this['dispatcher'];
        $worker_config = $this['config']['worker'];
        $queue_config  = $this['config']['queue'];
        $uuid          = $this['uuid'];
        
        return new Worker($uuid->v3($uuid->v4(),$worker_config->getWorkerName()),
                          $event,
                          $worker_config,
                          new Allocator($queue_config,$event));
    }    
    
    /**
      *  Return the monitor API
      *
      *  @access public
      *  @return LaterJob\Monitor
      */
    public function monitor()
    {
                
    }
    
    
    /**
      *  Allows a query to be run on the queue
      *
      *  @return 
      *  @access public
      */
    public function query()
    {
       
       # raise query event
       
    }
    
}
/* End of File */