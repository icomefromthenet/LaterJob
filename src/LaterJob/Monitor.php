<?php
namespace LaterJob;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LaterJob\Event\MonitoringEvent;
use LaterJob\Event\MonitoringQueryEvent;
use LaterJob\Event\MonitoringEventsMap;
use LaterJob\Model\Monitor\Stats;
use DateTime;

/**
  *  Monitor API 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Monitor
{
    /**
      *  @var EventDispatcherInterface $event 
      */
    protected $event;    
        
        
    /**
      *  Class Constructor
      *
      *  @access public
      *  @return void
      *  @param EventDispatcherInterface $event
      */    
    public function __construct(EventDispatcherInterface $event)
    {
        $this->event = $event;
    }
    
    
    /**
      *  Run the monitor over the given hour.
      *
      *  @access public
      *  @return boolean true if no errors
      *  @param DateTime $hour should be the hour to run the monitor over.
      */
    public function monitor(DateTime $hour)
    {
        $stats = new Stats();
        $event = new MonitoringEvent($stats);
        
        $stats->setMonitorDate($hour);
        $this->event->dispatch(MonitoringEventsMap::MONITOR_RUN,$event);
        
        return $event->getResult();
    }
    
    /**
      *  Query the monitor for a given period
      *
      *  @access public
      *  @param DateTime $start periods after x
      *  @param DateTime [optional] $end periods to stop the query at
      */
    public function query(DateTime $start, DateTime $end = null)
    {
        $event = new MonitoringQueryEvent($start,$end);
        
        $this->event->dispatch(MonitoringEventsMap::MONITOR_QUERY,$event);
        
        return $event->getResult();
    }
    
            
}
/* End of File */