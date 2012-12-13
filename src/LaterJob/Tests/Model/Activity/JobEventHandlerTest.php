<?php
namespace LaterJob\Tests\Model\Activity;

use LaterJob\Model\Activity\Transition;
use LaterJob\Model\Activity\TransitionBuilder;
use LaterJob\Model\Activity\TransitionQuery;
use LaterJob\Model\Activity\TransitionGateway;
use LaterJob\Config\QueueConfig as QueueConfig;
use LaterJob\Event\JobTransitionEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\UUID;
use DateTime;
use LaterJob\Model\Activity\JobSubscriber;
use LaterJob\Util\MersenneRandom;
use DBALGateway\Feature\StreamQueryLogger;


/**
  *  Unit Tests for Model Transition Query and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class JobEventHandlerTest extends TestsWithFixture
{
    
    protected $streamed_query_logger = null;
    
    
    /**
    * Creates the application.
    *
    */
    public function createApplication()
    {
        if($this->streamed_query_logger === null) {
            $logger  = new StreamQueryLogger($this->getMonolog());
            $this->getDoctrineConnection()->getConfiguration()->setSQLLogger($logger);
            $this->streamed_query_logger = $logger;
        }
        
        return null;
    }
    
    
    /**
      *  Fetches a new insance of the gateway
      *
      *  @return LaterJob\Model\Activity\TransitionGateway
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
        $process_handler = '01b89534-cf6e-3a63-b7fe-8e6b5a729483';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_ADD);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job added');
        $transition->setProcessHandle($process_handler);
        
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
        $process_handler = '01b89534-cf6e-3a63-b7fe-8e6b5a729483';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_START);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job started');
        $transition->setProcessHandle($process_handler);
        
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
        $process_handler = '01b89534-cf6e-3a63-b7fe-8e6b5a729483';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_FINISH);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job finished');
        $transition->setProcessHandle($process_handler);
        
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
        $process_handler = '01b89534-cf6e-3a63-b7fe-8e6b5a729483';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_FAIL);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job has failed');
        $transition->setProcessHandle($process_handler);
        
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
        $process_handler = '01b89534-cf6e-3a63-b7fe-8e6b5a729483';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_ERROR);
        $transition->setOccured(new DateTime());
        $transition->setMessage('job encountered error');
        $transition->setProcessHandle($process_handler);
        
        # create the event object
        $transition_event = new JobTransitionEvent($mock_job,$transition);
        
        # run through handler
        $handler->onJobError($transition_event);
        
        $this->assertGreaterThan(0,$transition->getTransitionId());
    }
    
    /**
      *  @expectedException LaterJob\Exception
      */
    public function testExceptionStartHandlerMissingDateColumn()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new JobSubscriber($gateway);
        $mock_job  = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_job_1';
        $process_handler = '01b89534-cf6e-3a63-b7fe-8e6b5a729483';
        
        # create the transition
        $transition = new Transition();
        $transition->setJob($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(QueueConfig::STATE_START);
        $transition->setMessage('job started');
        $transition->setProcessHandle($process_handler);
        
        # create the event object
        $transition_event = new JobTransitionEvent($mock_job,$transition);
        
        # run through handler
        $handler->onJobStart($transition_event);
    }
    
}
/* End of File */