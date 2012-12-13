<?php
namespace LaterJob;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DateTime;
use LaterJob\Event\MonitoringEvent,
    LaterJob\Event\MonitoringQueryEvent,
    LaterJob\Event\MonitoringEventsMap,
    LaterJob\Model\Monitor\Stats,
    LaterJob\Config\WorkerConfig;

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
      *  @var LaterJob\Config\WorkerConfig 
      */
    protected $worker_config;
        
    /**
      *  Class Constructor
      *
      *  @access public
      *  @return void
      *  @param EventDispatcherInterface $event
      */    
    public function __construct(EventDispatcherInterface $event, WorkerConfig $worker)
    {
        $this->event         = $event;
        $this->worker_config = $worker;
    }
    
    
    /**
      *  Run the monitor over the given hour.
      *
      *  @access public
      *  @return LaterJob\Model\Monitor\Stats with result and stats data for period
      *  @param DateTime $hour should be the hour to run the monitor over.
      */
    public function monitor(DateTime $hour)
    {
        $stats = new Stats();
        $event = new MonitoringEvent($stats);
        
        $stats->setMonitorDate($hour);
        $stats->setWorkerMaxThroughput($this->worker_config->getJobsToProcess());
        
        # lock
        $this->event->dispatch(MonitoringEventsMap::MONITOR_LOCK,$event);
        
        # gather
        $event->setResult(false);
        $this->event->dispatch(MonitoringEventsMap::MONITOR_RUN,$event);
        
        # commit
        $event->setResult(false);
        $this->event->dispatch(MonitoringEventsMap::MONITOR_COMMIT,$event);
        
        return $event->getStats();
    }
    
    /**
      *  Query the monitor for a given period
      *
      *  @access public
      *  @param DateTime $start periods after x
      *  @param DateTime [optional] $end periods to stop the query at
      *  @param integer $limit the query limit
      *  @param integer $offset the query offset
      *  @param boolean $calculating include calculating rows ie not complete yet.
      */
    public function query($offset, $limit, $order = 'ASC', DateTime $before = null, DateTime $after = null,$calculating = false)
    {
        $event = new MonitoringQueryEvent($offset, $limit, $order,$before,$after,$calculating);
        
        $this->event->dispatch(MonitoringEventsMap::MONITOR_QUERY,$event);
        
        return $event->getResult();
    }
    
            
}
/* End of File */