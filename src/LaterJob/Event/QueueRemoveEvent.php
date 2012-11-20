<?php
namespace LaterJob\Event;

use Symfony\Component\EventDispatcher\Event;
use DateTime;

/**
  *  Events for the remove job operation.
  *
  *  Jobs that are not locked can be removed or where the lock expired
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */

class QueueRemoveEvent extends Event
{
    /**
      *  @var string the uuid of the job 
      */
    protected $job_id;

    /**
      *  @var DateTime the current date time 
      */
    protected $now;
    
    /**
      *  @var boolean the result of the operation 
      */
    protected $result;
    
    /**
      *  Class Constructor
      *
      *  @access public
      */
    public function __construct($job_id, DateTime $now)
    {
        $this->job_id = $job_id;
        $this->now    = $now;
    }

    /**
      *  Return the job id for removal
      *
      *  @access public
      *  @return string the uuid of the job
      */
    public function getJobId()
    {
        return $this->job_id;
    }
    
    /**
      *  Return the current time
      *
      *  @access public
      *  @return DateTime
      */
    public function getNow()
    {
        return $this->now;
    }
    
    /**
      *  Sets if the remove operation was sucessful
      *
      *  @access public
      *  @param boolean true if removal was sucessful
      */
    public function setResult($success)
    {
        $this->result = $success;
    }
    
    /**
      *  Return the result of the remove operation
      *
      *  @access public
      *  @return boolean true if remove sucessful
      */
    public function getResult()
    {
        return $this->result;
    }
    
}

/* End of File */