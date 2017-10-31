<?php
namespace LaterJob\Tests\Model\Queue;

use LaterJob\Model\Queue\JobSubscriber;
use LaterJob\Model\Queue\StorageBuilder;
use LaterJob\Model\Queue\StorageQuery;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Queue\Storage;
use LaterJob\Config\QueueConfig as QueueConfig;
use LaterJob\Event\JobTransitionEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\UUID;
use DateTime;

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
      *  @return LaterJob\Model\Activity\TransitionGateway
      */   
    protected function getTableGateway()
    {
        $doctrine   = $this->getDoctrineConnection();   
        $metadata   = $this->getTableMetaData()->getQueueTable(); 
        $table_name = $this->getTableMetaData()->getQueueTableName();
        $builder    = new StorageBuilder();
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();      
        
        return new  StorageGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testImplementsSubscriberInterface()
    {
        $gateway = $this->getTableGateway();
        $handler = new JobSubscriber($gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
    
    /*
    public function testTransitionAddHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new JobSubscriber($gateway);
        $mock_job     = $this->getMockBuilder('LaterJob\Job')->disableOriginalConstructor()->getMock();
        $uuid         = new UUID(new LaterJob\Util\MersenneRandom());   
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
        
    } */
    
    
}
/* End of File */