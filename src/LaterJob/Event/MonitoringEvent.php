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
      *  @var boolean was the event sucessful
      */
    protected $results;
    
    /**
      *  @var LaterJob\Model\Monitor\Stats  
      */
    protected $stats;
    
    /**
      *  @var string length of time monitor will observe
      *  example : '+1 hour' ie strtotime compitable string
      */
    protected $interval;
    
    
    /**
      *  Class constructor
      *
      *  @access public
      *  @param LaterJob\Model\Monitor\Stats $stats
      */
    public function __construct(Stats $stats)
    {
        $this->stats = $stats;
        $this->interval = '+ 1 hour';
    }
    
    /**
      *  Return this monitoring periods entity object
      *
      *  @access public
      *  @return LaterJob\Model\Monitor\Stats
      */    
    public function getStats()
    {
        return $this->stats;
    }
    
    
    /**
      *  Return the results of monitoring operation
      *
      *  @access public
      *  @return boolean
      */
    public function getResult()
    {
        return $this->results;
    }
    
    /**
      *  Set the results of monitoring operation
      *
      *  @access public
      *  @param boolean
      */
    public function setResult($results)
    {
        $this->results = $results;
    }
    
    /**
      *  Get the length of time monitor will observe
      *
      *  @access public
      *  @return string 
      */
    public function getInterval()
    {
        return $this->interval;
    }
    
    /**
      *  Set interval of time moniitor will observe
      * 
      *  @access public
      *  @param string $period e.g '+1 hour'
      */
    public function setInterval($period)
    {
        $this->interval = $period;
    }
    
}

/* End of File */