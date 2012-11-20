<?php
namespace LaterJob\Tests\Event;

use LaterJob\Event\JobTransitionEvent;
use LaterJob\Event\MonitoringEvent;
use LaterJob\Event\WorkerTransitionEvent;
use LaterJob\Event\JobEventsMap;
use LaterJob\Event\MonitoringEventsMap;
use LaterJob\Event\WorkerEventsMap;

use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for Event objects
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class EventTest extends PHPUnit_Framework_TestCase
{
    
    public function testJobTransitionEvent()
    {
        $mock_transition = $this->getMock('LaterJob\Model\Transition\Transition');
        $mock_job        = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        
        $event = new JobTransitionEvent($mock_job,$mock_transition);
        
        $this->assertEquals($mock_transition,$event->getTransition());
        $this->assertEquals($mock_job,$event->getJob());
    }
    
    
    public function testMonitoringEvent()
    {
        $mock_results = $this->getMock('LaterJob\Model\Monitor\Stats');
        
        $event = new MonitoringEvent($mock_results);
        
        $this->assertEquals($mock_results,$event->getResults());
        
    }
    
    public function testWorkerTransitionEvent()
    {
        $mock_transition = $this->getMock('LaterJob\Model\Transition\Transition');
        $mock_worker =  $this->getMockBuilder('LaterJob\Worker')->disableOriginalConstructor()->getMock();
        
        $event = new WorkerTransitionEvent($mock_worker,$mock_transition);
        
        $this->assertEquals($mock_worker,$event->getWorker());
        $this->assertEquals($mock_transition,$event->getTransition());
        
    }
    
    
    public function testMapsParserErrors()
    {
        $worker_map  = new WorkerEventsMap();
        $monitor_map = new MonitoringEventsMap();
        $job_map     = new JobEventsMap();
        
        $this->assertTrue(true);
    }
    
}
/* End of File */