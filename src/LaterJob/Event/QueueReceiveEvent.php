<?php
namespace LaterJob\Event;

use Symfony\Component\EventDispatcher\Event;
use LaterJob\Model\Queue\Storage;

/**
  *  Events for receive operations on the queue, ie when job is
  *  received by the queue
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueReceiveEvent extends Event
{
    /**
      *  @var  LaterJob\Model\Queue\Storage
      */
    protected $storage;

    /**
      *  @var boolean result 
      */
    protected $result;

    /**
      *  Class Constructor
      *
      *  @access public
      */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }
    
    /**
      *  Return the Job to store on queue
      *
      *  @access public
      *  @return LaterJob\Model\Queue\Storage
      */
    public function getStorage()
    {
        return $this->storage;
    }
    
    /**
      *  Return the result of the rec operation
      *
      *  @access public
      *  @return boolean
      */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
      *  Sets the result of the rec operation
      *
      *  @access public
      *  @param boolean true if successful
      */
    public function setResult($result)
    {
        $this->result = $result;
    }
    
}

/* End of File */