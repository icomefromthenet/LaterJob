<?php
namespace LaterJob;

use LaterJob\Exception as LaterJobException;
use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Event\JobEventsMap;
use LaterJob\Event\JobTransitionEvent;
use LaterJob\Model\Queue\Storage;
use LaterJob\Model\Activity\Transition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DateTime;

/**
  *  Provides an API to manage the lifecycle of a job instance.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Job
{
    
    /**
      *  @var LaterJob\Model\Queue\Storage 
      */
    protected $storage; 
 
    /**
      *  @var  LaterJob\Config\Queue
      */
    protected $config;
    
    /**
      *  @var  Symfony\Components\EventDispatcher\EventDispatcherInterface
      */
    protected $event;
    
    /**
      *  @var DateTime 
      */
    protected $start;
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param LaterJob\Model\Queue\Storage  $store
      *  @param LaterJob\Config\Queue $config
      */
    public function __construct(Storage $store, QueueConfig $config, EventDispatcherInterface $event)
    {
        $this->storage = $store;
        $this->config  = $config;
        $this->event   = $event;
    }
 
 
    //------------------------------------------------------------------
    # Properties
 
    /**
      * Return the queue config which each job shares
      *
      * @access public
      * @return LaterJob\Config\Queue
      */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
      *  Return the stored object
      *
      *  @access public
      *  @return LaterJob\Model\Queue\Storage
      */
    public function getStorage()
    {
        return $this->storage;
    }
    
    /**
      *  Return this jobs assigned unique id
      *
      *  @access public
      *  @return string the job unique id
      */
    public function getId()
    {
        return $this->storage->getJobId();
    }
    
    /**
      *  Return the data stored in the queue
      *
      *  @access public
      *  @return mixed the un-serialize entity
      */
    public function getData()
    {
        return $this->storage->getJobData();
    }
    
    /**
      *  Return the number of job retry's left
      *
      *  @access public
      *  @return integer the remaining retry count
      */
    public function getRetryCount()
    {
        return $this->storage->getRetryLeft();       
    }
    
    /**
      *  Return the current state of the Job
      *
      *  @access public
      *  @return integer the current state of job
      */
    public function getCurrentState()
    {
        return $this->config->getLiteral($this->storage->getState());
    }
 
    
    //------------------------------------------------------------------
    # Transitions (excludes ADD)
    
    /**
      *  Transition a job to starting state
      *
      *  @access public
      *  @param string $handle The worker's handle
      *  @param DateTime $start
      *  @param string $msg
      *  @throws LaterJob\Exception when starting state fails
      */
    public function start($handle,DateTime $start,$msg = '')
    {
        $current = $this->storage->getState();
        
        if($current === QueueConfig::STATE_ADD || $current === QueueConfig::STATE_ERROR) {
            
            # change the state on the storage
            $this->start = $start;
            $this->storage->setState(QueueConfig::STATE_START);
             
            # create the transition entity
            $trans = new Transition();
            
            $trans->setJob($this->getId());
            $trans->setState(QueueConfig::STATE_START);
            $trans->setOccured($start);                
            $trans->setMessage(' Job '.$this->getId(). ' STARTED :: '.$msg);
            $trans->setProcessHandle($handle);
            
            # raise the start event        
            $this->event->dispatch(JobEventsMap::STATE_START,new JobTransitionEvent($this,$trans));
        
            
        } else {
            
            throw new LaterJobException(sprintf('Can not transiton from %s to %s',
                                                    $this->getCurrentState(),
                                                    $this->config->getLiteral(QueueConfig::STATE_START)));
        }
        
    }
    
    /**
      *  Transition a job to error state
      *
      *  @access public
      *  @param string $handle The worker's handle
      *  @param DateTime $start
      *  @param string $msg
      *  @throws LaterJob\Exception when starting state fails
      */
    public function error($handle,DateTime $when,$msg = '')
    {
         $current = $this->storage->getState();
        
        if($current !== QueueConfig::STATE_START) {
            throw new LaterJobException(sprintf('Can not transiton from %s to %s',
                                                  $this->getCurrentState(),
                                                  $this->config->getLiteral(QueueConfig::STATE_ERROR)
                                        ));
        }
        
        # change the state on the storage
        $this->storage->setState(QueueConfig::STATE_ERROR);
        
        # set the timer for next retry
        $wait = clone $when;
        $t = $wait->getTimestamp();
        $v = $this->config->getRetryTimer();
        $wait->setTimestamp(($t + $v));
        $this->storage->setRetryLast($wait);
        
        # remove one off recount
        $count = $this->storage->getRetryLeft();
        
        if($count > 0) {
            $this->storage->setRetryLeft(($count-1));
        }
        
        # create the transition entity
        $trans = new Transition();
        
        $trans->setJob($this->getId());
        $trans->setState(QueueConfig::STATE_ERROR);
        $trans->setOccured($when);                
        $trans->setMessage(' Job '.$this->getId(). ' ERROR :: '.$msg);
        $trans->setProcessHandle($handle);
        
        # raise the error event        
        $this->event->dispatch(JobEventsMap::STATE_ERROR,new JobTransitionEvent($this,$trans));
        
    }
    
    /**
      *  Transition a job to failed state
      *
      *  @access public
      *  @param string $handle The worker's handle
      *  @param DateTime $start
      *  @param string $msg
      *  @throws LaterJob\Exception when starting state fails
      */
    public function fail($handle,DateTime $when,$msg = '')
    {
         $current = $this->storage->getState();
        
        if($current === QueueConfig::STATE_START || $current === QueueConfig::STATE_ERROR) {
            
            # change the state on the storage
            $this->storage->setState(QueueConfig::STATE_FAIL);
            
            # create the transition entity
            $trans = new Transition();
            
            $trans->setJob($this->getId());
            $trans->setState(QueueConfig::STATE_FAIL);
            $trans->setOccured($when);                
            $trans->setMessage(' Job '.$this->getId(). ' FAIL :: '.$msg);
            $trans->setProcessHandle($handle);
            
            # raise the fail event        
            $this->event->dispatch(JobEventsMap::STATE_FAIL,new JobTransitionEvent($this,$trans));
            
            
        } else {
            
            throw new LaterJobException(sprintf('Can not transiton from %s to %s',
                                                    $this->getCurrentState(),
                                                    $this->config->getLiteral(QueueConfig::STATE_FAIL)));
        }
        
        
    }
    
    
    
    /**
      *  Transition a job to Finished state
      *
      *  @access public
      *  @param string $handle The worker's handle
      *  @param DateTime $start
      *  @param string $msg
      *  @throws LaterJob\Exception when starting state fails
      */
    public function finish($handle,DateTime $finish,$msg = '')
    {
        $current = $this->storage->getState();
        
        if($current !== QueueConfig::STATE_START) {
            throw new LaterJobException(sprintf('Can not transiton from %s to %s',
                                                   $this->getCurrentState(),
                                                  $this->config->getLiteral(QueueConfig::STATE_FINISH)
                                        ));
        }
        
        # change the state on the storage
        $this->storage->setState(QueueConfig::STATE_FINISH);
        
        # create the transition entity
        $trans = new Transition();
        
        $trans->setJob($this->getId());
        $trans->setState(QueueConfig::STATE_FINISH);
        $trans->setOccured($finish);                
        $trans->setMessage(' Job '.$this->getId(). ' FINISHED :: '.$msg);
        $trans->setProcessHandle($handle);
        
        # raise the finish event        
        $this->event->dispatch(JobEventsMap::STATE_FINISH,new JobTransitionEvent($this,$trans));
        
    }
    
    
}

/* End of File */