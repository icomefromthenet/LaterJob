<?php
namespace LaterJob\Event;

use Symfony\Component\EventDispatcher\Event;
use DateTime;
use Traversable;

/**
  *  Event for List operation of a queue to show work contained within
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueListEvent extends Event
{
    /**
      *  @var Datetime 
      */
    protected $before;
    
    /**
      *  @var DateTime 
      */
    protected $after;
    
    /**
      *  @var string LaterJob\Config\Queue::STATE_*
      */
    protected $state;
    
    /**
      *  @var integer limit results to x
      */
    protected $limit;
    
    /**
      * @var integer the offset starts at x  
      */
    protected $offset;
    
    /**
      *  @var string ASC|DESC the order to use on date added to the queue
      */
    protected $dte_order;
    
    /**
      *  @var Doctrine\Common\Collections\Collection
      */
    protected $results;
    
    /**
      *  Class Constructor
      *
      *  @access public
      */
    public function __construct($offset, $limit,$state = null,$order = 'ASC',DateTime $before = null, DateTime $after = null)
    {
        $this->offset = $offset;
        $this->limit  = $limit;
        $this->state  = $state;
        $this->order  = $order;
        $this->before = $before;
        $this->after  = $after;
    }

    
    public function getOffset()
    {
        return $this->offset;
    }
    
    public function getLimit()
    {
        return $this->limit;
    }
    
    public function getState()
    {
        return $this->state;
    }
    
    public function getOrder()
    {
        return $this->order;
    }
    
    public function getBefore()
    {
        return $this->before;
    }
    
    public function getAfter()
    {
        return $this->after;
    }
    
    /**
      *  Return the results of the list operation
      *
      *  @access public
      *  @return Traversable
      */
    public function getResult()
    {
        return $this->results;
    }
    
    /**
      *  Sets the results of the list operation
      *
      *  @param Traversable
      *  @access public
      */
    public function setResult(Traversable $results)
    {
        $this->results = $results;
    }
    
}
/* End of File */