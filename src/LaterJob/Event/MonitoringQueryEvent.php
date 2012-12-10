<?php
namespace LaterJob\Event;

use LaterJob\Model\Monitor\Stats;
use Symfony\Component\EventDispatcher\Event;
use Traversable;
use DateTime;

/**
  *  Event class passed duing events that occur in MonitoringEventMap 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class MonitoringQueryEvent extends Event
{
    /**
      *  @var Traversable the results
      */
    protected $results;
    
    /**
      *  @var DateTime the start period 
      */
    protected $start;
    
    /**
      *  @var DateTime the end period 
      */
    protected $end;
    
    /**
      *  Class constructor
      *
      *  @access public
      *  @param LaterJob\Model\Monitor\Stats $results
      */
    public function __construct(DateTime $start, DateTime $end = null)
    {
       $this->start = $start;
       $this->end   = $end;
    }
    
    
    /**
      *  Fetch the start time of the query
      *
      *  @access public
      *  @return DateTime
      */
    public function getStart()
    {
        return $this->start;
    }
    
    /**
      *  Return the end time of the query
      *
      *  @access public
      *  @return DateTime
      */
    public function getEnd()
    {
        return $this->end;
        
    }
    
    
    /**
      *  Return the results of monitoring operation
      *
      *  @access public
      *  @return Traversable
      */
    public function getResult()
    {
        return $this->results;
    }
    
    /**
      *  Set the results of monitoring operation
      *
      *  @access public
      *  @param Traversable
      */
    public function setResult(Traversable $results)
    {
        $this->results = $results;
    }
    
}

/* End of File */