<?php
namespace LaterJob;

use Iterator;
use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueueLockEvent;
use LaterJob\Event\QueueSendEvent;
use LaterJob\Pool\Storage;
use LaterJob\Config\QueueConfig as QueueConfig;
use LaterJob\Exception as LaterJobException;
use LaterJob\Job;

/**
  *  This class will for a given worker select jobs to process. 
  *
  *  It will act as an collection lazy loading a locked job from the queue.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Allocator implements Iterator
{
    
    /**
      *  @var array the cache of loaded jobs 
      */
    protected $jobs; 

    /**
      *  @var the current cache index
      */
    protected $current;
    
    /**
      *  @var Symfony\Component\EventDispatcher\EventDispatcherInterface
      */
    protected $event;
    
    /**
      *  @var LaterJob\Config\QueueConfig
      */
    protected $config;
    
    /**
      *  @var string the worker id that used to lock a job to that worker 
      */
    protected $worker_id;
    
    /**
      *  @var DateTime the lockout should last to x datetime. 
      */
    protected $worker_lockout;
    
    /**
      *  @var DateTime the assigned time could be different from now ie not a system time 
      */
    protected $assigned_now;
    
    /**
      *  @var DateTime the current system time that the receive method was called
      */
    protected $receive_time;
    
    /**
      *  Class Constructor
      *
      *  
      */
    public function __construct(QueueConfig $config,EventDispatcherInterface $event)
    {
        $this->current   = 0;
        $this->event     = $event;
        $this->config    = $config;
        $this->jobs      = array();
    }

    
    /**
      *  Receive jobs from the queue
      *
      *  @access public
      *  @param integer $number of jobs to receive from queue
      *  @param string the worker_id to bind the jobs to
      *  @param DateTime lockout the max time of the lockout
      *  @param DateTime the current time 
      */
    public function receive($number,$worker_id, DateTime $lockout, DateTime $now)
    {
        if(is_integer($number) === false) {
            throw new LaterJobException('Allocator number of jobs to receive is not and integer');
        }
        
        if($number <= 0) {
            throw new LaterJobException('Allocator number of jobs must be greater than zero');
        }
        
        $this->worker_id      = $worker_id;
        $this->worker_lockout = $lockout;
        $this->assigned_now   = $now;
        $this->receive_time   = new DateTime(); 
        
        $event = new QueueLockEvent($worker_id,$lockout,$number,$now);
        
        $this->event->dispatch(QueueEventsMap::QUEUE_LOCK,$event);
    }
    
    
    //------------------------------------------------------------------
    # Iterator Interface
    
    public function rewind()
    {
        $this->current = 0;
    }

    public function current()
    {
        return $this->jobs[$this->current];
    }

    public function key()
    {
        return $this->current;
    }

    public function next()
    {
        ++$this->current;
    }

    public function valid()
    {
        if(!isset($this->jobs[$this->current])) {
            # need to calculate the time from when frist receive call to current vaid call
            # do this by getting the difference from the received time to now time
            # both rec time and the new datetime are assigned by the system, where assigned_now could be manually set
            
            $event = $this->event->dispatch(QueueEventsMap::QUEUE_SENT,new QueueSendEvent($this->worker_id,$this->assigned_now->add($this->getRunningInterval())));
            
            if($event->getResult() === null) {
                return false;
            }
            
            $store = reset($event->getResult()->getIterator());
            
            $this->jobs[$this->current] = new Job($store,$this->config,$this->event);
        }        
        
        return true;
    }
    
    /**
      *  Using the Received time calculate the approx running time
      *  of the current allocator
      *
      *  @access public
      *  @return \DateInterval
      */
    public function getRunningInterval()
    {
        if($this->receive_time === null) {
            throw new LaterJobException('Can not return running time before self::receive() has been called');
        }
        
        return $this->receive_time->diff(new DateTime());
        
    }
    
    
    //------------------------------------------------------------------
    # Properties
    
    /**
      *  Return the time assigned when receive was called
      *
      *  @access public
      *  @return DateTime
      */
    public function getReceivedTime()
    {
        return $this->receive_time;
    }
    
    /**
      *  Returns the worker_id used to identify a lock
      *
      *  @access public
      *  @return string a worker_id (UUID)
      */
    public function getWorkerId()
    {
        return $this->worker_id;
    }
    
    /**
      *  Returns the datetime used as the lock timer
      *
      *  @access public
      *  @return DateTime
      */
    public function getWorkerLockout()
    {
        return $this->worker_lockout;
    }
    
    /**
      *  Return the time assigned, could be different from system time
      *
      *  @access public
      *  @return DateTime 
      */
    public function getAssignedTime()
    {
        return $this->assigned_now;
    }
    
}
/* End of File */