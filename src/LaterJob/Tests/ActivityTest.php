<?php
namespace LaterJob\Tests;

use LaterJob\Activity;
use LaterJob\Event\QueueEventsMap;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Test for the Activity API 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class ActivityTest extends PHPUnit_Framework_TestCase
{
    
    public function testActivityQuery()
    {
        $offset = 3;
        $limit = 100;
        $before = new DateTime();
        $after = new DateTime();
        $order = 'asc';
        
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        
        $activity = new Activity($mock_event);
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_QUERY_ACTIVITY),$this->isInstanceOf('LaterJob\Event\QueueQueryActivityEvent'));
        
        $activity->query($offset,$limit,$order,$before,$after);
    }
    
    
    public function testActivityPurge()
    {
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $before = new DateTime();
        
        $activity = new Activity($mock_event);
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_PURGE_ACTIVITY),$this->isInstanceOf('LaterJob\Event\QueuePurgeActivityEvent'));
                   
        $activity->purge($before);
    }
    
    
}

/* End of File */