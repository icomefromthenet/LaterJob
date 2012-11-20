<?php
namespace LaterJob\Event;

use LaterJob\Model\Monitor\Stats;
use Symfony\Component\EventDispatcher\Event;

/**
  *  Event class passed duing events that occur in MonitoringEventMap 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class MonitoringEvent extends Event
{
    /**
      *  @var LaterJob\Model\Monitor\Stats 
      */
    protected $results;
    
    /**
      *  Class constructor
      *
      *  @access public
      *  @param LaterJob\Model\Monitor\Stats $results
      */
    public function __construct(Stats $results)
    {
        $this->results = $results;
    }
    
    
    /**
      *  Return the results of monitoring operation
      *
      *  @access public
      *  @return LaterJob\Model\Monitor\Stats
      */
    public function getResults()
    {
        return $this->results;
    }
    
}

/* End of File */