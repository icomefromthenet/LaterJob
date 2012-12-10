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
     * The monitor.run event is raised when monitoring script starts.
     *
     * The event listener receives LaterJob\Event\MonitoringEvent
     * instance.
     *
     * @var string
     */
   const MONITOR_RUN   = 'laterjob.monitor.run';
   
    /**
     * The monitor.query event is raised when a client wants to find
     * a monitor result
     *
     * The event listener receives LaterJob\Event\MonitoringEvent
     * instance.
     *
     * @var string
     */
   const MONITOR_QUERY = 'laterjob.monitor.query';
   
   
    
}
/* End of File */