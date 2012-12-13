<?php
namespace LaterJob\Tests\Model\Activity;

use LaterJob\Model\Activity\Transition;
use LaterJob\Model\Activity\TransitionBuilder;
use LaterJob\Model\Activity\TransitionQuery;
use LaterJob\Model\Activity\TransitionGateway;
use LaterJob\Config\WorkerConfig as WorkerConfig;
use LaterJob\Event\WorkerTransitionEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\UUID;
use DateTime;
use LaterJob\Model\Activity\WorkerSubscriber;
use LaterJob\Util\MersenneRandom;


/**
  *  Unit Tests for Model Transition Query and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class WorkEventHandlerTest extends  TestsWithFixture
{
    
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
        $handler = new WorkerSubscriber($gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
    
    
    public function testStartTransitionHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new WorkerSubscriber($gateway);
        $mock_worker  = $this->getMockBuilder('LaterJob\Worker')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_worker_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setWorker($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(WorkerConfig::STATE_START);
        $transition->setOccured(new DateTime());
        $transition->setMessage('worker starting');
        
        
        # create the event object
        $transition_event = new WorkerTransitionEvent($mock_worker,$transition);
        
        # run through handler
        $handler->onWorkerStart($transition_event);
        
        $this->assertGreaterThan(0,$transition->getTransitionId());
        
    }
    
    public function testFinishTransitionHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new WorkerSubscriber($gateway);
        $mock_worker  = $this->getMockBuilder('LaterJob\Worker')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_worker_1';
       
               
        # create the transition
        $transition = new Transition();
        $transition->setWorker($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(WorkerConfig::STATE_FINISH);
        $transition->setOccured(new DateTime());
        $transition->setMessage('worker finished');
        
        
        # create the event object
        $transition_event = new WorkerTransitionEvent($mock_worker,$transition);
        
        # run through handler
        $handler->onWorkerFinish($transition_event);
        
        # no exceptions raised by failed recording
        $this->assertGreaterThan(0,$transition->getTransitionId());
    }
    
    
     public function testErrorTransitionHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new WorkerSubscriber($gateway);
        $mock_worker  = $this->getMockBuilder('LaterJob\Worker')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_worker_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setWorker($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(WorkerConfig::STATE_ERROR);
        $transition->setOccured(new DateTime());
        $transition->setMessage('worker error');
        
        
        # create the event object
        $transition_event = new WorkerTransitionEvent($mock_worker,$transition);
        
        # run through handler
        $handler->onWorkerError($transition_event);
        
        # no exceptions raised by failed recording
        $this->assertGreaterThan(0,$transition->getTransitionId());
    }
    
    /**
      *  @expectedException LaterJob\Exception
      */
    public function testStartTranstionExceptionMissingOccuredColumn()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new WorkerSubscriber($gateway);
        $mock_worker  = $this->getMockBuilder('LaterJob\Worker')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new MersenneRandom());   
        $worker_name  = 'test_worker_1';
        
        # create the transition
        $transition = new Transition();
        $transition->setWorker($uuid->v3($uuid->v4(),$worker_name));
        $transition->setState(WorkerConfig::STATE_START);
        $transition->setMessage('worker starting');
        
        # create the event object
        $transition_event = new WorkerTransitionEvent($mock_worker,$transition);
        
        # run through handler
        $handler->onWorkerStart($transition_event);
        $handler->onWorkerStart($transition_event);
        
    }
    
}
/* End of File */