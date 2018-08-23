<?php
namespace LaterJob\Tests;

use LaterJob\Event\WorkerTransitionEvent;
use LaterJob\Event\WorkerEventsMap;

use LaterJob\Worker;
use LaterJob\Config\WorkerConfig as WorkerConfig;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
  *  Unit Tests for Event objects
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class WorkerTest extends TestCase
{
    
    public function testWorkerProperties()
    {
        
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();        
       
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                               ->disableOriginalConstructor()
                               ->getMock();
                               
        $mock_def       = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                               ->setMethods(array('getLiteral'))
                               ->getMock();
        
        $id             = 'a worker';
        
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,WorkerConfig::STATE_START);
        
        $this->assertEquals($mock_def,$worker->getConfig());
        $this->assertEquals($id,$worker->getId());
        $this->assertEquals(null,$worker->getStartTime());
        
    }


    public function testTransitionStart()
    {
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalto(WorkerEventsMap::WORKER_START),$this->isInstanceOf('\LaterJob\Event\WorkerTransitionEvent'));
        
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                               ->disableOriginalConstructor()
                               ->getMock();
        
        $mock_def = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                         ->setMethods(array('getLiteral'))
                         ->getMock();
        
        $mock_def->expects($this->once())
                 ->method('getLiteral')
                 ->with($this->equalTo(1))
                 ->will($this->returnValue('LaterJob\Config\WorkerConfig::STATE_START'));
        
        $id = 'a worker';
        $start = new DateTime();
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,null);
        
        $worker->start($start,'starting from cron');
        
        $this->assertEquals('LaterJob\Config\WorkerConfig::STATE_START',$worker->getState());
        $this->assertEquals($start,$worker->getStartTime());
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\WorkerConfig::STATE_START to LaterJob\Config\WorkerConfig::STATE_START
      */    
    public function testExceptionTransitionStartFromStart()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock(); 
        
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                                ->disableOriginalConstructor()
                                ->getMock();
                                
        $mock_def       = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                                ->setMethods(array('getLiteral'))
                                ->getMock();
        
        $mock_def->expects($this->exactly(2))
                 ->method('getLiteral')
                 ->with($this->equalTo(1))
                 ->will($this->returnValue('LaterJob\Config\WorkerConfig::STATE_START'));
                   
        
        $id             = 'a worker';
        
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,WorkerConfig::STATE_START);
        $worker->start(new DateTime(),'starting from cron');
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\WorkerConfig::STATE_FINISH to LaterJob\Config\WorkerConfig::STATE_START
      */    
    public function testExceptionTransitionStartFromFinish()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                                ->disableOriginalConstructor()
                                ->getMock();
                                
        $mock_def       = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                                ->setMethods(array('getLiteral'))
                                ->getMock();
                                
        $mock_def->expects($this->exactly(2))
                 ->method('getLiteral')
                 ->with($this->anything(1))
                 ->will($this->onConsecutiveCalls('LaterJob\Config\WorkerConfig::STATE_FINISH', 'LaterJob\Config\WorkerConfig::STATE_START'));
                   
        
        $id             = 'a worker';
        
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,WorkerConfig::STATE_FINISH);
        $worker->start(new DateTime(),'starting from cron');
    }
    
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\WorkerConfig::STATE_ERROR to LaterJob\Config\WorkerConfig::STATE_START
      */    
    public function testExceptionTransitionStartFromError()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                               ->disableOriginalConstructor()
                               ->getMock();
                               
        $mock_def       = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                               ->setMethods(array('getLiteral'))
                               ->getMock();
                               
        $mock_def->expects($this->exactly(2))
                 ->method('getLiteral')
                 ->with($this->anything(1))
                 ->will($this->onConsecutiveCalls('LaterJob\Config\WorkerConfig::STATE_ERROR', 'LaterJob\Config\WorkerConfig::STATE_START'));
                   
        
        $id             = 'a worker';
        
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,WorkerConfig::STATE_ERROR);
        $worker->start(new DateTime(),'starting from cron');
    }
    
    public function testTransitionFinish()
    {
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalto(WorkerEventsMap::WORKER_FINISH),$this->isInstanceOf('\LaterJob\Event\WorkerTransitionEvent'));
        
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                               ->disableOriginalConstructor()
                               ->getMock();
        
        $mock_def = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                          ->setMethods(array('getLiteral'))
                          ->getMock();
                          
        $mock_def->expects($this->once())
                 ->method('getLiteral')
                 ->with($this->equalTo(2))
                 ->will($this->returnValue('LaterJob\Config\WorkerConfig::STATE_FINISH'));
        
        $id = 'a worker';
        
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,WorkerConfig::STATE_START);
        
        $worker->finish(new DateTime(),'finish from cron');
        
        $this->assertEquals('LaterJob\Config\WorkerConfig::STATE_FINISH',$worker->getState());
        
    }
    
    public function testTransitionError()
    {
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalto(WorkerEventsMap::WORKER_ERROR),$this->isInstanceOf('\LaterJob\Event\WorkerTransitionEvent'));
        
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                              ->disableOriginalConstructor()
                              ->getMock();
        
        $mock_def = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                         ->setMethods(array('getLiteral'))
                         ->getMock();
                         
        $mock_def->expects($this->once())
                 ->method('getLiteral')
                 ->with($this->equalTo(3))
                 ->will($this->returnValue('LaterJob\Config\WorkerConfig::STATE_ERROR'));
        
        $id = 'a worker';
        
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,WorkerConfig::STATE_START);
        
        $worker->error(new DateTime(),'error from cron');
        
        $this->assertEquals('LaterJob\Config\WorkerConfig::STATE_ERROR',$worker->getState());
        
    }
    
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage Can not transiton from LaterJob\Config\WorkerConfig::STATE_ERROR to LaterJob\Config\WorkerConfig::STATE_FINISH
      */ 
    public function testExceptionTransitionFinishFromError()
    {
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();     
        
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                               ->disableOriginalConstructor()
                               ->getMock();
                               
        $mock_def       = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                               ->setMethods(array('getLiteral'))
                               ->getMock();
        
        $mock_def->expects($this->exactly(2))
                 ->method('getLiteral')
                 ->with($this->anything())
                 ->will($this->onConsecutiveCalls('LaterJob\Config\WorkerConfig::STATE_ERROR', 'LaterJob\Config\WorkerConfig::STATE_FINISH'));
                   
        
        $id             = 'a worker';
        
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,WorkerConfig::STATE_ERROR);
        $worker->finish(new DateTime(),'finish from cron');
        
    }
    
    
    public function testReceive()
    {
        $total_jobs   = 5;
        $now          = new DateTime();
        $org          = $now->format('U');
        $id           = 'a worker';
        $lockout_time = 100;
        
        $mock_event     = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock(); 
        
        $mock_allocator = $this->getMockBuilder('LaterJob\Allocator')
                               ->disableOriginalConstructor()
                               ->getMock();
                               
        $mock_allocator->expects($this->once())
                       ->method('receive')
                       ->with($this->equalTo($total_jobs),$this->equalTo($id),$this->isInstanceOf('\DateTime'),$this->equalTo($now));
        
        $mock_def      = $this->getMockBuilder('LaterJob\Config\WorkerConfig')
                              ->setMethods(array('getJobsToProcess','getJobLockoutTime'))
                              ->getMock();
                              
        $mock_def->expects($this->once())
                 ->method('getJobsToProcess')
                 ->will($this->returnValue($total_jobs));
        
        $mock_def->expects($this->once())
                 ->method('getJobLockoutTime')
                 ->will($this->returnValue($lockout_time));
        
        
        $worker = new Worker($id,$mock_event,$mock_def,$mock_allocator,WorkerConfig::STATE_START);
        
        $this->assertEquals($mock_allocator,$worker->receive($now));
        
    }
    
}
/* End of File */