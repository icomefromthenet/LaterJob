<?php
namespace LaterJob\Event;

use Symfony\Component\EventDispatcher\Event;
use DateTime;

/**
  *  Events Query transition history
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueQueryActivityEvent extends Event
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
      *  @var integer a db offset 
      */
    protected $offset;
    
    /**
      *  @var limit a db query limit 
      */
    protected $limit;
    
    /**
      *  @var string ASC | DESC  to order the transitions by date occured
      */
    protected $order;
    
    /**
     * @var string the UUID of a worker to filter on 
     */ 
    protected $worker_id;
    
    /**
     * @var string the UUID of a job to filter on 
     */ 
    protected $job_id;
    
    /**
      *  Class Constructor
      *
      *  @access public
      */
    public function __construct($offset, $limit, $order = 'ASC', DateTime $before = null, DateTime $after = null, $job_id = null, $worker_id = null)
    {
        $this->before    = $before;
        $this->after     = $after;
        $this->limit     = $limit;
        $this->offset    = $offset;
        $this->order     = $order;
        $this->worker_id = $worker_id;
        $this->job_id    = $job_id;
        
        $this->removed = 0;
    }
    
    /**
     *  fetch a job UUID
     * 
     * @access public
     * @return string
     */ 
    public function getJobID()
    {
        return $this->job_id;
    }
    
    /**
     *  Fetch a worker UUID
     * 
     * @return string
     * @access public
     */ 
    public function getWorkerID()
    {
        return $this->worker_id;
    }

    /**
      *  Return the before date
      *
      *  @access public
      *  @return DateTime
      */
    public function getBefore()
    {
        return $this->before;
    }
    
    /**
      *  Return the date after
      *
      *  @access public
      *  @return DateTime
      */
    public function getAfter()
    {
        return $this->after;
    }
    
    /**
      *  Return the date order.
      *
      *  @access public
      *  @return string ASC | DESC
      */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
      *  Return the limit
      *
      *  @access public
      *  @return integer the db query limit
      */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
      *  Return the offset
      *
      *  @access public
      *  @return integer the db offset
      */
    public function getOffset()
    {
        return $this->offset;
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