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
        
        $mock_config    = $this->getMockBuilder('LaterJob\Config\WorkerConfig')->getMock();
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
        $activity = new Monitor($mock_event,$mock_config);
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(MonitoringEventsMap::MONITOR_QUERY),$this->isInstanceOf('LaterJob\Event\MonitoringQueryEvent'));
        
        $activity->query(0,1,'ASC',$before,$after,true);
        
    }
    
    
    public function testMonitor()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        $mock_config    = $this->getMockBuilder('LaterJob\Config\WorkerConfig')->getMock();
        $before = new DateTime();
        
        $activity = new Monitor($mock_event,$mock_config);
        
        $mock_event->expects($this->at(0))
                   ->method('dispatch')
                   ->with($this->equalTo(MonitoringEventsMap::MONITOR_LOCK),$this->isInstanceOf('LaterJob\Event\MonitoringEvent'));

        $mock_event->expects($this->at(1))
                   ->method('dispatch')
                   ->with($this->equalTo(MonitoringEventsMap::MONITOR_RUN),$this->isInstanceOf('LaterJob\Event\MonitoringEvent'));
                   
        
        $mock_event->expects($this->at(2))
                   ->method('dispatch')
                   ->with($this->equalTo(MonitoringEventsMap::MONITOR_COMMIT),$this->isInstanceOf('LaterJob\Event\MonitoringEvent'));

        $mock_config->expects($this->once())
                    ->method('getJobsToProcess')
                    ->will($this->returnValue(5));
                   
        $stats = $activity->monitor($before);
        
        $this->assertInstanceOf('LaterJob\Model\Monitor\Stats',$stats);
        $this->assertEquals(5,$stats->getWorkerMaxThroughput());
        $this->assertEquals($before,$stats->getMonitorDate());
        
    }
    
    
}

/* End of File */