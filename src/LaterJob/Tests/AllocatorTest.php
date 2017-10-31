<?php
namespace LaterJob\Tests;

use LaterJob\Allocator;
use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueueSendEvent;
use PHPUnit_Framework_TestCase;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
  *  Unit Tests for Job API object
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class AllocatorTest extends PHPUnit_Framework_TestCase
{
    
    public function testImplementsIterator()
    {
        
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();        
        $mock_def       = $this->getMockBuilder('LaterJob\Config\QueueConfig')->getMock();
        
        $allocator     = new  Allocator($mock_def,$mock_event);
        
        $this->assertInstanceOf('Iterator',$allocator);        
            
    }

    
    public function testReceieve()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();        
        $mock_def       = $this->getMockBuilder('LaterJob\Config\QueueConfig')->getMock();
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_LOCK),$this->isInstanceOf('LaterJob\Event\QueueLockEvent'));
        
        $allocator     = new  Allocator($mock_def,$mock_event);
        
        $worker_id   = '4b336e15-cac0-3307-8b81-f1de26e6c383';
        $number      = 10;
        $now         = new DateTime('01-11-2013 00:00:00');
        $timeout     = new DateTime('01-11-2013 03:00:00');
        
        $allocator->receive($number,$worker_id,$timeout,$now);
        
        $this->assertEquals($worker_id,$allocator->getWorkerId());
        $this->assertEquals($timeout,$allocator->getWorkerLockout());
        $this->assertEquals($now,$allocator->getAssignedTime());
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not return running time before self::receive() has been called
      */
    public function testExceptionEarlyCallIntervalTimer()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();        
        $mock_def       = $this->getMockBuilder('LaterJob\Config\QueueConfig')->getMock();
        $allocator      = new  Allocator($mock_def,$mock_event);
        $allocator->getRunningInterval();
        
    }
    
    public function testIntervalTimer()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();        
        $mock_def       = $this->getMockBuilder('LaterJob\Config\QueueConfig')->getMock();
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_LOCK),$this->isInstanceOf('LaterJob\Event\QueueLockEvent'));
        
        $allocator     = new  Allocator($mock_def,$mock_event);
        
        $worker_id   = '4b336e15-cac0-3307-8b81-f1de26e6c383';
        $number      = 10;
        $now         = new DateTime('01-11-2013 00:00:00');
        $timeout     = new DateTime('01-11-2013 03:00:00');
        
        $allocator->receive($number,$worker_id,$timeout,$now);
        
        $this->assertInstanceOf('DateInterval',$allocator->getRunningInterval());
    }
    
    
    public function testWhenNoJobsReturned()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();        
        $mock_def       = $this->getMockBuilder('LaterJob\Config\QueueConfig')->getMock();
        
        $mock_event->expects($this->at(0))
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_LOCK),$this->isInstanceOf('LaterJob\Event\QueueLockEvent'));
        
        $mock_event->expects($this->at(1))
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_SENT),$this->isInstanceOf('LaterJob\Event\QueueSendEvent'))
                   ->will($this->returnArgument(1)); 
        
        $allocator     = new  Allocator($mock_def,$mock_event);
        
        # call receive to setup allocator
        $worker_id   = '4b336e15-cac0-3307-8b81-f1de26e6c383';
        $number      = 10;
        $now         = new DateTime('01-11-2013 00:00:00');
        $timeout     = new DateTime('01-11-2013 03:00:00');
        
        $allocator->receive($number,$worker_id,$timeout,$now);
        
        # call valid to load next job
        $allocator->valid();        
        
    }
    
    public function testJobReturnedSetupCorrectly()
    {
        $mock_event      = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();        
        $mock_def        = $this->getMockBuilder('LaterJob\Config\QueueConfig')->getMock();
        $mock_collection = new ArrayCollection();
        $mock_result     = $this->getMockBuilder('LaterJob\Event\QueueSendEvent')->disableOriginalConstructor()->getMock();
        $mock_storage    = $this->getMockBuilder('LaterJob\Model\Queue\Storage')->getMock();
        
        $mock_result->expects($this->exactly(2))
                    ->method('getResult')
                    ->will($this->returnValue($mock_collection));
        
        $mock_collection->add($mock_storage);
        
        $mock_event->expects($this->at(0))
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_LOCK),$this->isInstanceOf('LaterJob\Event\QueueLockEvent'));
        
        $mock_event->expects($this->at(1))
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_SENT),$this->isInstanceOf('LaterJob\Event\QueueSendEvent'))
                   ->will($this->returnValue($mock_result)); 
        
        $allocator   = new  Allocator($mock_def,$mock_event);
        
        # call receive to setup allocator
        $worker_id   = '4b336e15-cac0-3307-8b81-f1de26e6c383';
        $number      = 10;
        $now         = new DateTime('01-11-2013 00:00:00');
        $timeout     = new DateTime('01-11-2013 03:00:00');
        
        $allocator->receive($number,$worker_id,$timeout,$now);
        
        # call valid to load next job
        $allocator->valid();  
        
        # fetch job and ensure that been setup correctly
        $job = $allocator->current();
        
        $this->assertEquals($mock_storage,$job->getStorage());
        $this->assertEquals($mock_def,$job->getConfig());
        
        
        # assert iterator used the job cache
        $allocator->rewind();
        $allocator->valid();
        $this->assertEquals($job,$allocator->current());
        
    }
    
    
}
/* End of File */