<?php
namespace LaterJob\Event;

use Symfony\Component\EventDispatcher\Event;
use DateTime;

/**
  *  Events purges finished and failed jobs from queue
  *  when given a date
  *
  *  Will not remove jobs in other states and doesn't purge transition history.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueuePurgeEvent extends Event
{
    /**
      *  @var DateTime purge added before date x
      */
    protected $before;

    /**
      *  @var boolean was the operation sucessful 
      */    
    protected $result;
    
    /**
      *  Class Constructor
      *
      *  @access public
      */
    public function __construct(DateTime $before = null)
    {
        $this->before = $before;
        $this->removed = 0;
    }

    /**
      *  Return the before date
      *
      *  @access public
      *  @return DateTime
      */
    public function getBeforeDate()
    {
        return $this->before;
    }
    
    /**
      *  Gets the result of the purge operation
      *
      *  @access public
      *  @return boolean true if operation a success
      */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
      *  Sets the result of the purge operation
      *
      *  @access public
      *  @param boolean true if operation a success
      */
    public function setResult($result)
    {
        $this->result = $result;
    }
    
}

/* End of File */