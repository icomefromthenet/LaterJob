<?php
namespace LaterJob\Event;

use Symfony\Component\EventDispatcher\Event;
use DateTime;

/**
  *  Events for locking and unlocking operations that occur on a queue 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueLockEvent extends Event
{
    /**
      *  @var uuid for this lock 
      */
    protected $handle;
    
    /**
      *  @var DateTime the lockout timmer 
      */
    protected $timeout;
    
    /**
      *  @var integer the locked count 
      */
    protected $locked;
    
    /**
      *  @var integer limit the lock to x 
      */
    protected $limit;
    
    /**
      *  @var DateTime the current time to compare the lockout against
      *  lockout that exceed now time are included for in new locking operation
      *  this resolves issue of expired locks
      */
    protected $now;
    
    /**
      *  Class Constructor
      *
      *  @access public
      */
    public function __construct($handle, DateTime $timeout, $limit, DateTime $now)
    {
        $this->handle  = $handle;
        $this->timeout = $timeout;
        $this->limit   = $limit;
        $this->now     = $now;
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
        return $this->timeout;
    }
    
    /**
      *  Return the limit 
      *
      *  @access public
      *  @return integer the limit
      */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
      *  Return the Assigned Now Time
      *
      *  @access public
      *  @return DateTime the assigned now time
      */
    public function getNow()
    {
        return $this->now;
    }
    
    /**
      *  Sets the number of rows locked
      *
      *  @access public
      *  @param integer $locked;
      */
    public function setResult($locked)
    {
        $this->locked = $locked;
    }
    
    /**
      *  Returns the number of locked rows
      *
      *  @access public
      *  @return integer the number of rows locked
      */
    public function getResult()
    {
        return $this->locked;
    }
    
}
/* End of File */