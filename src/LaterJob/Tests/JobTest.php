<?php
namespace LaterJob\Tests;

use LaterJob\Event\JobEventsMap;
use LaterJob\Event\JobTransitionEvent;
use LaterJob\Model\Queue\Storage;
use LaterJob\Job;
use LaterJob\Config\QueueConfig as QueueConfig;
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
        $mock_def       = $this->getMock('LaterJob\Config\QueueConfig');
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
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_START),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_START))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_START'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ADD);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->start($process_handle,new DateTime,'starting on cron');
        $this->assertEquals('LaterJob\Config\QueueConfig::STATE_START',$worker->getCurrentState());
        
    }
    
    
    public function testTransitionFromErrorToStart()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_START),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_START))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_START'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ERROR);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->start($process_handle,new DateTime,'starting on cron');
        $this->assertEquals('LaterJob\Config\QueueConfig::STATE_START',$worker->getCurrentState());
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\QueueConfig::STATE_START to LaterJob\Config\QueueConfig::STATE_START
      */
    public function testExceptionTransitionFromStartToStart()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->exactly(2))
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_START))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_START'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_START);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        $worker->start($process_handle,new DateTime,'starting on cron');
        
    }
    
    
    public function testTransitionFromStartToFinished()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_FINISH),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FINISH))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_FINISH'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_START);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->finish($process_handle,new DateTime,'finishing on cron');
        $this->assertEquals('LaterJob\Config\QueueConfig::STATE_FINISH',$worker->getCurrentState());
        
    }
    
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\QueueConfig::STATE_FINSH to LaterJob\Config\QueueConfig::STATE_FINSH
      */
    public function testExceptionTransitionFromFinishToFinish()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->exactly(2))
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FINISH))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_FINSH'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_FINISH);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        $worker->finish($process_handle,new DateTime,'FINISH on cron');
        
    }
    
    
    public function testTransitionStartToError()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_ERROR),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_ERROR))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_ERROR'));
                
        $mock_def->expects($this->once())
                ->method('getRetryTimer')
                ->will($this->returnValue(100));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_START);
        
        $job = new Job($storage,$mock_def,$mock_event);
        $when = new DateTime();
        
        $job->error($process_handle,$when,'error on cron');
        
        $this->assertEquals('LaterJob\Config\QueueConfig::STATE_ERROR',$job->getCurrentState());
        
        $timer_result = $job->getStorage()->getRetryLast()->getTimestamp();
        
        $this->assertEquals(($when->getTimestamp()+100),$timer_result);
        
        
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\QueueConfig::STATE_ERROR to LaterJob\Config\QueueConfig::STATE_ERROR
      */
    public function testExceptionTransitionFromErrorToError()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->exactly(2))
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_ERROR))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_ERROR'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ERROR);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        $worker->error($process_handle,new DateTime,'FINISH on cron');
    }
    
    
    public function testTransitonFromStartToFail()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_FAIL),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FAIL))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_FAIL'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_START);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->fail($process_handle,new DateTime,'error on cron');
        $this->assertEquals('LaterJob\Config\QueueConfig::STATE_FAIL',$worker->getCurrentState());
        
    }
    
    
    public function testTransitonFromErrorToFail()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_event->expects($this->once())
                 ->method('dispatch')
                 ->with($this->equalTo(JobEventsMap::STATE_FAIL),$this->isInstanceOf('LaterJob\Event\JobTransitionEvent'));
        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->once())
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FAIL))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_FAIL'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_ERROR);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        
        $worker->fail($process_handle,new DateTime,'error on cron');
        $this->assertEquals('LaterJob\Config\QueueConfig::STATE_FAIL',$worker->getCurrentState());
        
    }
    
    
     /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\QueueConfig::STATE_FAIL to LaterJob\Config\QueueConfig::STATE_FAIL
      */
    public function testExceptionTransitionFromFailToFail()
    {
        $process_handle     = 'a73491a6-ed50-3c17-8e0f-d7279e7a00d9';
        $id         = 'a job';
        $data       = new \stdClass();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');        
        $mock_def   = $this->getMock('LaterJob\Config\QueueConfig');
        $mock_def->expects($this->exactly(2))
                ->method('getLiteral')
                ->with($this->equalTo(QueueConfig::STATE_FAIL))
                ->will($this->returnValue('LaterJob\Config\QueueConfig::STATE_FAIL'));
        
        $storage = new Storage();
        $storage->setJobId($id);
        $storage->setJobData($data);
        $storage->setState(QueueConfig::STATE_FAIL);
        
        $worker = new Job($storage,$mock_def,$mock_event);
        $worker->fail($process_handle,new DateTime,'FAIL on cron');
    }
    
}
/* End of File */