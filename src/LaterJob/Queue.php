<?php
namespace LaterJob;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Cron\CronExpression;
use Pimple, DateTime;
use LaterJob\Model\Queue\Storage,
    LaterJob\Config\QueueConfig,
    LaterJob\Event\QueueReceiveEvent,
    LaterJob\Event\QueueListEvent,
    LaterJob\Event\QueueRemoveEvent,
    LaterJob\Event\QueuePurgeEvent,
    LaterJob\Event\QueueLookupEvent,
    LaterJob\Event\QueueEventsMap,
    LaterJob\Log\LogInterface,
    LaterJob\Worker,
    LaterJob\UUID,
    LaterJob\Loader\LoaderInterface,
    LaterJob\Exception as LaterJobException;

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
        
        $logger->info('Starting loading LaterJob Queue API with options',$options);
        
        $config_loader->boot($this);
        $logger->info('Finished loading and parsing Config');
        $model_loader->boot($this);
        $logger->info('Finish loading queue models');
        $event_loader->boot($this);
        $logger->info('Finished registering queue events subscribers');
        
        
    }
    
    //------------------------------------------------------------------
    # Properties
    
    /**
      *  Return the Event Dispatcher
      *
      *  @access public
      *  @return Symfony\Component\EventDispatcher\EventDispatcherInterface 
      */
    public function getDispatcher()
    {
        return $this['dispatcher'];
    }
    
    
    /**
      *   Returns the Logger
      *
      *   @access public
      *   @return LaterJob\Log\LogInterface
      */
    public function getLogger()
    {
        return $this['logger']; 
    }
    
   
    /**
      *  Return the UUID Generator
      *
      *  @access public
      *  @return LaterJob\UUID
      */
    public function getUUID()
    {
        return $this['uuid']; 
    }
    
    
    //------------------------------------------------------------------
    # Public Interface
    
    /**
      *   Add a job to the queue
      *
      *   @access public
      *   @return boolean true if job added
      *   @throws LaterJob\Exception
      *   @param DateTime $now
      *   @param mixed $job_data
      */
    public function send(DateTime $now,$job_data)
    {
        $uuid          = $this['uuid'];
        $event         = $this['dispatcher'];
        $queue_options = $this['config.queue'];
        $storage       = new Storage();    
        
        # generate the uuid for job
        $storage->setJobId($uuid->v3($uuid->v4(),md5(json_encode($job_data))));
        
        $storage->setDateAdded($now);
        $storage->setJobData($job_data);
        $storage->setState(QueueConfig::STATE_ADD);
        $storage->setRetryLeft($queue_options->getMaxRetry());
        
        # raise the add event
        $event->dispatch(QueueEventsMap::QUEUE_REC,new QueueReceiveEvent($storage));
        
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
        $worker_config = $this['config.worker'];
        $queue_config  = $this['config.queue'];
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
        return new Monitor($this['dispatcher'],$this['config.worker']);        
    }
    
    /**
      *  Return the Activity API
      *
      *  @return LaterJob\Activity
      *  @access public
      */
    public function activity()
    {
        return new Activity($this['dispatcher']);
    }
    
    /**
      *  Return a schedule of cron
      *
      *  @access public
      */
    public function schedule(DateTime $now , $iterations)
    {
        try{
        
        $cron = $this['config.worker']->getCronDefinition();
        $cron_parser = CronExpression::factory($cron);
        
        return $cron_parser->getMultipleRunDates($iterations, $now, false, true);
    
        } catch(Exception $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
    }
    
    
    /**
      *  Allows a query to be run on the queue
      *
      *  @return Traversable list of jobs
      *  @access public
      *  @param integer $offset
      *  @param integer $limit
      *  @param integer $state
      *  @param string order 'ASC' | 'DESC'
      *  @param DateTime $before
      *  @param DateTime $after 
      */
    public function query($offset,$limit,$state = null,$order ='ASC', DateTime $before = null , DateTime $after = null)
    {
       $event = new QueueListEvent($offset,$limit,$state,strtoupper($order),$before,$after);
       
       $this['dispatcher']->dispatch(QueueEventsMap::QUEUE_LIST,$event);
       
       return $event->getResult();
    }
    
    /**
      *  Remove a job from the queue if it has not been
      *  locked or processed
      *
      *  @access public
      *  @param string $job_id the uuid of the job
      *  @param DateTime $now
      *  @return boolean true if removed
      */
    public function remove($job_id, DateTime $now)
    {
        $event = new QueueRemoveEvent($job_id,$now);
        $this['dispatcher']->dispatch(QueueEventsMap::QUEUE_REMOVE,$event);
        
        return $event->getResult();
    }
    
    
    /**
      *  Purge the queue of finished and failed jobs that
      *  added to the queue before date X
      *
      *  @access public
      *  @param DateTime $before
      *  @return integer number of jobs purged
      */
    public function purge(DateTime $before)
    {
        $event = new QueuePurgeEvent($before);
        $this['dispatcher']->dispatch(QueueEventsMap::QUEUE_PURGE,$event);
        
        return $event->getResult();
    }
    
    
    /**
      *  Lookup a specify job using its id
      *
      *  @access public
      *  @return LaterJob\Model\Queue\Storage
      */
    public function lookup($job_id)
    {
        $event = new QueueLookupEvent($job_id);
        $this['dispatcher']->dispatch(QueueEventsMap::QUEUE_LOOKUP,$event);
        
        return $event->getResult();
    }
    
}
/* End of File */