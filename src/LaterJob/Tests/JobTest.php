<?php
namespace LaterJob\Tests;

use LaterJob\Event\JobEventsMap;
use LaterJob\Event\JobTransitionEvent;
use LaterJob\Model\Queue\Storage;
use LaterJob\Job;
use LaterJob\Config\Queue as QueueConfig;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for Job API object
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class JobTest extends PHPUnit_Framework_TestCase
{
    
    public function testJobProperties()
    {
        $id             = 'a job';
        $data           = new \stdClass();
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def       = $this->getMock('LaterJob\Config\Queue');
        $storage        = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ADD);
        $storage->setRetryLeft(3);
        
        $job = new Job($storage,$mock_def,$mock_event);
        
        $this->assertEquals($mock_def,$job->getConfig());
        $this->assertEquals($id,$job->getId());
        $this->assertEquals($storage,$job->getStorage());
        $this->assertEquals($data,$job->getData());
        $this->assertEquals(3,$job->getRetryCount());
        
    }

    
    
    public function testTransitionFromAddToStart()
    {
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_START),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_START))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_START'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ADD);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->start(new DateTime,'starting on cron');
        $this->assertEquals('LaterJob\Config\Queue::STATE_START',$worker->getCurrentState());
        
    }
    
    
    public function testTransitionFromErrorToStart()
    {
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_START),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_START))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_START'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ERROR);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->start(new DateTime,'starting on cron');
        $this->assertEquals('LaterJob\Config\Queue::STATE_START',$worker->getCurrentState());
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\Queue::STATE_START to LaterJob\Config\Queue::STATE_START
      */
    public function testExceptionTransitionFromStartToStart()
    {
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->exactly(2))
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_START))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_START'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_START);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        $worker->start(new DateTime,'starting on cron');
        
    }
    
    
    public function testTransitionFromStartToFinished()
    {
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_FINISH),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FINISH))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_FINISH'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_START);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->finish(new DateTime,'finishing on cron');
        $this->assertEquals('LaterJob\Config\Queue::STATE_FINISH',$worker->getCurrentState());
        
    }
    
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\Queue::STATE_FINSH to LaterJob\Config\Queue::STATE_FINSH
      */
    public function testExceptionTransitionFromFinishToFinish()
    {
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->exactly(2))
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FINISH))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_FINSH'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_FINISH);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        $worker->finish(new DateTime,'FINISH on cron');
        
    }
    
    
    public function testTransitionStartToError()
    {
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_ERROR),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_ERROR))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_ERROR'));
                
        $mock_def->expects($this->once())
                ->method('getRetryTimer')
                ->will($this->returnValue(100));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_START);
        
        $job = new Job($storage,$mock_def,$mock_event);
        $when = new DateTime();
        
        $job->error($when,'error on cron');
        
        $this->assertEquals('LaterJob\Config\Queue::STATE_ERROR',$job->getCurrentState());
        
        $timer_result = $job->getStorage()->getRetryLast()->getTimestamp();
        
        $this->assertEquals(($when->getTimestamp()+100),$timer_result);
        
        
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\Queue::STATE_ERROR to LaterJob\Config\Queue::STATE_ERROR
      */
    public function testExceptionTransitionFromErrorToError()
    {
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->exactly(2))
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_ERROR))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_ERROR'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ERROR);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        $worker->error(new DateTime,'FINISH on cron');
    }
    
    
    public function testTransitonFromStartToFail()
    {
         $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_FAIL),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FAIL))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_FAIL'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_START);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->fail(new DateTime,'error on cron');
        $this->assertEquals('LaterJob\Config\Queue::STATE_FAIL',$worker->getCurrentState());
        
    }
    
    
    public function testTransitonFromErrorToFail()
    {
         $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_FAIL),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FAIL))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_FAIL'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ERROR);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->fail(new DateTime,'error on cron');
        $this->assertEquals('LaterJob\Config\Queue::STATE_FAIL',$worker->getCurrentState());
        
    }
    
    
     /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\Queue::STATE_FAIL to LaterJob\Config\Queue::STATE_FAIL
      */
    public function testExceptionTransitionFromFailToFail()
    {
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def   = $this->getMock('LaterJob\Config\Queue');
        $mock_def->expects($this->exactly(2))
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FAIL))
                ->will($this->returnValue('LaterJob\Config\Queue::STATE_FAIL'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_FAIL);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        $worker->fail(new DateTime,'FAIL on cron');
    }
    
}
/* End of File */