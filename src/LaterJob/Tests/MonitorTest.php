<?php
namespace LaterJob\Tests;

use LaterJob\Monitor;
use LaterJob\Event\MonitoringEventsMap;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Test for the Monitor API 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class MonitorTest extends PHPUnit_Framework_TestCase
{
    
    public function testQuery()
    {
        $before = new DateTime();
        $after = new DateTime();
        
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        
        $activity = new Monitor($mock_event);
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(MonitoringEventsMap::MONITOR_QUERY),$this->isInstanceOf('LaterJob\Event\MonitoringQueryEvent'));
        
        $activity->query($before,$after);
    }
    
    
    public function testMonitor()
    {
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $before = new DateTime();
        
        $activity = new Monitor($mock_event);
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(MonitoringEventsMap::MONITOR_RUN),$this->isInstanceOf('LaterJob\Event\MonitoringEvent'));
                   
        $activity->monitor($before);
    }
    
    
}

/* End of File */