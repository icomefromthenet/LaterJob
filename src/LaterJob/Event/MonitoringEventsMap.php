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
     * The monitor.lock event is raised when monitoring script starts and needs to lock a period
     *
     * The event listener receives LaterJob\Event\MonitoringEvent
     * instance.
     *
     * @var string
     */
   const MONITOR_LOCK   = 'laterjob.monitor.lock';
   
    /**
     * The monitor.run event is raised when monitoring script starts and after locked.
     * Info is gathered in this step
     *
     * The event listener receives LaterJob\Event\MonitoringEvent
     * instance.
     *
     * @var string
     */
   const MONITOR_RUN   = 'laterjob.monitor.run';
   
   
   /**
     * The monitor.commit event is raised when monitoring finished and saved to data store
     *
     * The event listener receives LaterJob\Event\MonitoringEvent
     * instance.
     *
     * @var string
     */
   const MONITOR_COMMIT   = 'laterjob.monitor.commit';
   
   
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