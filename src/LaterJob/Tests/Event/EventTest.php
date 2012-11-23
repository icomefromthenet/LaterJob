<?php
namespace LaterJob\Tests\Event;

use LaterJob\Event\JobTransitionEvent;
use LaterJob\Event\MonitoringEvent;
use LaterJob\Event\WorkerTransitionEvent;
use LaterJob\Event\JobEventsMap;
use LaterJob\Event\MonitoringEventsMap;
use LaterJob\Event\WorkerEventsMap;
use LaterJob\Event\QueueListEvent;
use LaterJob\Event\QueueLockEvent;
use LaterJob\Event\QueuePurgeEvent;
use LaterJob\Event\QueuePurgeActivityEvent;
use LaterJob\Event\QueueReceiveEvent;
use LaterJob\Event\QueueRemoveEvent;
use LaterJob\Event\QueueSendEvent;
use LaterJob\Event\QueueQueryActivityEvent;
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
        $mock_transition = $this->getMock('LaterJob\Model\Activity\Transition');
        $mock_job        = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        
        $event = new JobTransitionEvent($mock_job,$mock_transition);
        
        $this->assertEquals($mock_transition,$event->getTransition());
        $this->assertEquals($mock_job,$event->getJob());
    }
    
    
    public function testMonitoringEvent()
    {
        $mock_results = $this->getMock('LaterJob\Model\Monitor\Stats');
        
        $event = new MonitoringEvent($mock_results);
        $event->setResult(false);
    
        $this->assertEquals($mock_results,$event->getStats());
        $this->assertEquals(false,$event->getResult());    
    }
    
    public function testWorkerTransitionEvent()
    {
        $mock_transition = $this->getMock('LaterJob\Model\Activity\Transition');
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
    
    
    public function testQueueListEvent()
    {
        $before = new DateTime();
        $after  = new DateTime();
        $state  = 1;
        $limit  = 5;
        $offset = 3;
        $order  = 'DESC';
        $result = new \ArrayIterator(array());
        
        $list = new QueueListEvent($offset,$limit,$state,$order,$before,$after);
        $list->setResult($result);
        
        $this->assertEquals($before,$list->getBefore());
        $this->assertEquals($after ,$list->getAfter());
        $this->assertEquals($state ,$list->getState());
        $this->assertEquals($limit ,$list->getLimit());
        $this->assertEquals($offset,$list->getOffset());
        $this->assertEquals($order ,$list->getOrder());
        $this->assertEquals($result,$list->getResult());
    }
    
    public function testQueueLockEvent()
    {
        $handle  = '4b336e15-cac0-3307-8b81-f1de26e6c383';
        $now     = new DateTime();
        $timeout = new DateTime();
        $limit   = 20;
        $result  = 10;
        $lock    = new QueueLockEvent($handle,$timeout,$limit,$now);
        $lock->setResult($result);
        
        $this->assertEquals($handle,$lock->getHandle());
        $this->assertEquals($result,$lock->getResult());
        $this->assertEquals($timeout,$lock->getTimeout());
        $this->assertEquals($limit,$lock->getLimit());
        $this->assertEquals($now,$lock->getNow());
    }
    
    public function testQueuePurgeEvent()
    {
        $before = new DateTime();
        $result = true;
        
        $purge = new QueuePurgeEvent($before);
        $purge->setResult($result);
        
        $this->assertEquals($result,$purge->getResult());
        $this->assertEquals($before,$purge->getBeforeDate());
    }
    
    public function testQueuePurgeActivityEvent()
    {
        $before = new DateTime();
        $result = true;
        
        $purge = new QueuePurgeActivityEvent($before);
        $purge->setResult($result);
        
        $this->assertEquals($result,$purge->getResult());
        $this->assertEquals($before,$purge->getBeforeDate());
    }
    
    
    public function testQueueReceiveEvent()
    {
        $storage = $this->getMock('LaterJob\Model\Queue\Storage');
        $result  = true;
        
        $receive = new QueueReceiveEvent($storage);
        $receive->setResult($result);
        
        $this->assertEquals($storage,$receive->getStorage());
        $this->assertEquals($result,$receive->getResult());
    }
    
    
    public function testQueueRemoveEvent()
    {
        $now    = new DateTime();
        $job_id = '4b336e15-cac0-3307-8b81-f1de26e6c383';
        $result = true;
        
        $remove = new QueueRemoveEvent($job_id,$now);
        $remove->setResult(true);
        
        $this->assertEquals($result,$remove->getResult());
        $this->assertEquals($now,$remove->getNow());
        $this->assertEquals($job_id,$remove->getJobId());
    }
    
    
    public function testQueueSendEvent()
    {
        $handle  = '4b336e15-cac0-3307-8b81-f1de26e6c383';
        $timeout = new DateTime();
        $limit   = 5;
        $result  = new \ArrayIterator(array());
        
        $send = new QueueSendEvent($handle,$timeout,$limit);
        $send->setResult($result);
        
        $this->assertEquals($handle,$send->getHandle());
        $this->assertEquals($timeout,$send->getTimeout());
        $this->assertEquals($limit,$send->getLimit());
        $this->assertEquals($result,$send->getResult());
    }
    
    
    public function testQueueActivtyEvent()
    {
        $offset = 3;
        $limit = 100;
        $before = new DateTime();
        $after = new DateTime();
        $order = 'asc';
        
        $event = new QueueQueryActivityEvent($offset,$limit,$order,$before,$after);
        
        $this->assertEquals($offset,$event->getOffset());
        $this->assertEquals($limit,$event->getLimit());
        $this->assertEquals($before,$event->getBefore());
        $this->assertEquals($after,$event->getAfter());
        $this->assertEquals($order,$event->getOrder());
        
    }
    
}
/* End of File */