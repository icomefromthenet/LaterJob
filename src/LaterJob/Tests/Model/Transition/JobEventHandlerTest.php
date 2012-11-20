<?php
namespace LaterJob\Tests\Model\Transition;

use LaterJob\Model\Transition\Transition;
use LaterJob\Model\Transition\TransitionBuilder;
use LaterJob\Model\Transition\TransitionQuery;
use LaterJob\Model\Transition\TransitionGateway;
use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Event\JobTransitionEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\UUID;
use DateTime;
use LaterJob\Model\Transition\JobSubscriber;
use LaterJob\Util\MersenneRandom;


/**
  *  Unit Tests for Model Transition Query and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class JobEventHandlerTest extends  TestsWithFixture
{
    
    /**
      *  Fetches a new insance of the gateway
      *
      *  @return LaterJob\Model\Transition\TransitionGateway
      */   
    protected function getTableGateway()
    {
        $doctrine   = $this->getDoctrineConnection();   
        $metadata   = $this->getTableMetaData()->getTransitionTable(); 
        $table_name = $this->getTableMetaData()->getTransitionTableName();
        $builder    = new TransitionBuilder();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');      
        
        return new TransitionGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testImplementsSubscriberInterface()
    {
        $gateway = $this->getTableGateway();
        $handler = new JobSubscriber($gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
    
    
    public function testTransitionAddHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new JobSubscriber($gateway);
        $mock_job  = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_job_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_ADD);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job added');
        
        # create the event object
        $transition_event = new JobTransitionEvent($mock_job,$transition);
        
        # run through handler
        $handler->onJobAdd($transition_event);
        
        $this->assertGreaterThan(0,$transition->getTransitionId());
        
    }
    
    public function testTransitionStartHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new JobSubscriber($gateway);
        $mock_job  = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_job_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_START);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job started');
        
        # create the event object
        $transition_event = new JobTransitionEvent($mock_job,$transition);
        
        # run through handler
        $handler->onJobStart($transition_event);
        
        $this->assertGreaterThan(0,$transition->getTransitionId());
    }
    
    public function testTransitionFinishHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new JobSubscriber($gateway);
        $mock_job  = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_job_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_FINISH);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job finished');
        
        # create the event object
        $transition_event = new JobTransitionEvent($mock_job,$transition);
        
        # run through handler
        $handler->onJobFinish($transition_event);
        
        $this->assertGreaterThan(0,$transition->getTransitionId());
    }
    
    public function testTransitionFailHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new JobSubscriber($gateway);
        $mock_job  = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_job_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_FAIL);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job has failed');
        
        # create the event object
        $transition_event = new JobTransitionEvent($mock_job,$transition);
        
        # run through handler
        $handler->onJobFail($transition_event);
        
        $this->assertGreaterThan(0,$transition->getTransitionId());
    }
    
    public function testTransitionErrorHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new JobSubscriber($gateway);
        $mock_job  = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_job_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_ERROR);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job encountered error');
        
        # create the event object
        $transition_event = new JobTransitionEvent($mock_job,$transition);
        
        # run through handler
        $handler->onJobError($transition_event);
        
        $this->assertGreaterThan(0,$transition->getTransitionId());
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage An exception occurred while executing 'INSERT INTO later_job_transition (job_id, state_id, dte_occured, transition_msg) VALUES (?, ?, ?, ?)'
      */
    public function testExceptionStartHandlerMissingDateColumn()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new JobSubscriber($gateway);
        $mock_job  = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_job_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_START);
        $transition->setMessage('job started');
        
        # create the event object
        $transition_event = new JobTransitionEvent($mock_job,$transition);
        
        # run through handler
        $handler->onJobStart($transition_event);
    }
    
}
/* End of File */