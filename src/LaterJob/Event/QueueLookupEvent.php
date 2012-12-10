<?php
namespace LaterJob\Event;

use LaterJob\Model\Queue\Storage;
use Symfony\Component\EventDispatcher\Event;

/**
  *  Event allow the queue to look up a single job 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueLookupEvent extends Event
{
    
    /**
      *  @var  LaterJob\Model\Queue\Storage
      */
    protected $result;
    
    /**
      *  @var string the job id 
      */
    protected $job_id;
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @return void
      *  @param string $job_id
      */
    public function __construct($job_id)
    {
        $this->job_id = $job_id;
    }
    
    
    /**
      *  Return the job_id want lookup
      *
      *  @access public
      */
    public function getJobId()
    {
        return $this->job_id;
    }
    
    
    /**
      *  Gets the result of lookup search
      *
      *  @access public
      *  @return Storage
      */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
      *  Sets the result of lookup search
      *
      *  @access public
      *  @param Storage
      */
    public function setResult($result)
    {
        $this->result = $result;
    }
    
}

/* End of File */