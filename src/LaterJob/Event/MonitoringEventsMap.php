<?php
namespace LaterJob\Event;

/**
  *  Map of all events that are raised by the monitor 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
final class MonitoringEventsMap
{
    /**
     * The monitor.start event is raised when monitoring script starts.
     *
     * The event listener receives LaterJob\Event\MonitoringEvent
     * instance.
     *
     * @var string
     */
   const MONITOR_START   = 'laterjob.monitor.start';
   
    /**
     * The monitor.finish event is raised when monitoring is finished and results for
     * the hour are in.
     *
     * The event listener receives LaterJob\Event\MonitoringEvent
     * instance.
     *
     * @var string
     */
   const MONITOR_FINISH = 'laterjob.monitor.finish';
   
   
    
}
/* End of File */