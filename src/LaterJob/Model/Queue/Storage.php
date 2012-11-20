<?php
namespace LaterJob\Model\Queue;

use DateTime;

/**
  *  Entity for a stored job 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Storage
{
    /**
      * @var string the job id  
      */
    protected $id;
    
    /**
      *  @var integer the current state 
      */
    protected $state_id;
    
    /**
      *  @var DateTime the date job stored on the queue
      */
    protected $date_added;
    
    /**
      *  @var integer numer of retry left
      */
    protected $retry_left;
    
    /**
      *  @var DateTime last time the job was executed 
      */
    protected $retry_last;
    
    /**
      *  @var mixed the date of the lock 
      */
    protected $data;
    
    /**
      *  @var string id the the lock 
      */
    protected $handle;
    
    /**
      *  @var DateTime the date of the lockout ends
      */
    protected $lock_timeout;
    
    
    
    public function getJobId()
    {
        return $this->id;
    }
    
    public function setJobId($id)
    {
        $this->id = $id;
    }
    
    public function getState()
    {
        return $this->state_id;
    }
    
    public function setState($state)
    {
        $this->state_id = $state;
    }
    
    public function getDateAdded()
    {
        return $this->date_added;
    }
    
    public function setDateAdded(DateTime $date)
    {
        $this->date_added = $date;
    }
    
    public function getRetryLeft()
    {
        return $this->retry_left;
    }
    
    public function setRetryLeft($left)
    {
        $this->retry_left = $left;
    }
    
    
    public function getRetryLast()
    {
        return $this->retry_last;
    }
    
    public function setRetryLast(DateTime $last)
    {
        $this->retry_last = $last;
    }
    
    
    public function getJobData()
    {
        return $this->data;
    }
    
    public function setJobData($obj)
    {
        $this->data = $obj;
    }
    
    public function getLockoutHandle()
    {
        return $this->handle;
    }
    
    public function setLockoutHandle($handle)
    {
        $this->handle = $handle;
    }
    
    public function getLockoutTimer()
    {
        return $this->lock_timeout;
    }
    
    public function setLockoutTimer(DateTime $timer)
    {
        $this->lock_timeout = $timer;
    }
}
/* End of File */