<?php
namespace LaterJob\Event;

use Symfony\Component\EventDispatcher\Event;
use DateTime;
use LaterJob\Model\Queue\Storage;
use Traversable;

/**
  *  Events for send operations on the queue ie when queue returns job that
  *  has been stored on the queue
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueSendEvent extends Event
{
    /**
      *  @var string the lock handle to use. 
      */
    protected $handle;
    
    /**
      *  @var DateTime the lockout max 
      */
    protected $lockout;
    
    /**
      *  @var  LaterJob\Model\Queue\Storage a stored job
      */
    protected $job;
    
    /**
      *  @var integer number of jobs to select from queue 
      */
    protected $limit;
    
    /**
      *  Class Constructor
      *
      *  @access public
      */
    public function __construct($handle,DateTime $timeout,$limit =1)
    {
        $this->handle  = $handle;
        $this->lockout = $timeout;
        $this->limit   = $limit;
    }
    
    
    /**
      *  Return the lockout timer
      *
      *  @access public
      *  @return string the lock handle
      */
    public function getHandle()
    {
        return $this->handle;
    }
    
    /**
      *  Return the lockout timeout
      *
      *  @access public
      *  @return DateTime the lockout
      */
    public function getTimeout()
    {
        return $this->lockout;
    }
    
    /**
      *  Return the number of jobs to send from the queue
      *
      *  @access public
      *  @return integer number of jobs to send from queue
      */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
      *  Sets the stored job
      *
      *  @access public
      *  @param Traversable
      */
    public function setResult(Traversable $job)
    {
        $this->job = $job;
    }
    
    /**
      *  Return the job storage
      *
      *  @access public
      *  @return Traversable
      */
    public function getResult()
    {
        return $this->job;
    }
    
}

/* End of File */