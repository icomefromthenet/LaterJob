<?php
namespace LaterJob\Tests\Model\Activity;

use LaterJob\Model\Activity\Transition;
use LaterJob\Model\Activity\TransitionBuilder;
use LaterJob\Model\Activity\TransitionQuery;
use LaterJob\Model\Activity\TransitionGateway;
use LaterJob\Config\QueueConfig as QueueConfig;
use LaterJob\Event\QueueQueryActivityEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\UUID;
use DateTime;
use LaterJob\Model\Activity\QueueSubscriber;
use LaterJob\Util\MersenneRandom;
use DBALGateway\Feature\StreamQueryLogger;


/**
  *  Unit Tests for Model Transition Query and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class HandlerQueryActivityTest extends  TestsWithFixture
{
    
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'purgehistory_handler_seed.xml');
    }
    
    
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
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();      
        
        return new TransitionGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testImplementsSubscriberInterface()
    {
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
   
   
   
    public function testQueryNoDate()
    {
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
        
        $event = new QueueQueryActivityEvent(4,5,'DESC',null,null);
        
        $handler->onQueryActivity($event);
       
        $result = $event->getResult();
       
        $this->assertEquals(5,count($result));
        
        $expected  = array(
            96,
            95,
            94,
            93,
            92
        );
        
        foreach($result as $activity) {
            $this->assertContains($activity->getTransitionId(),$expected);
        }
       
    }
    
    public function testQueryWithDate()
    {
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
        
        $before = new DateTime('2015-04-01 14:39:00');
        $after  = new DateTime('2015-04-01 14:36:00');
        
        $event = new QueueQueryActivityEvent(0,100,'DESC',$before,$after);
        
        $handler->onQueryActivity($event);
       
        $result = $event->getResult();
       
        $this->assertEquals(4,count($result));
        
        $expected  = array(
            99,
            98,
            97,
            96
        );
        
        foreach($result as $activity) {
            $this->assertContains($activity->getTransitionId(),$expected);
        }
        
    }
    
    
    public function testQueryWithWorker() 
    {
        $this->createApplication();
        
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
      
        
        $worker_id = '2bbb01c1-b394-34ae-8c74-e3ab7dd4a17a';
        
        $event = new QueueQueryActivityEvent(0,100,'DESC',null,null,null,$worker_id);
        
        $handler->onQueryActivity($event);
       
        $result = $event->getResult();
       
        $this->assertEquals(1,count($result));
        
        $expected  = array(95);
        
        foreach($result as $activity) {
            $this->assertContains($activity->getTransitionId(),$expected);
        }
    }
    
    
    public function testQueryWithJob() 
    {
        $this->createApplication();
        
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
      
        
        $job_id = '4f5bdee5-3812-34cc-8031-245dcec8787e';
        
        $event = new QueueQueryActivityEvent(0,100,'DESC',null,null,$job_id,null);
        
        $handler->onQueryActivity($event);
       
        $result = $event->getResult();
       
        $this->assertEquals(1,count($result));
        
        
        $expected  = array(95);
        
        foreach($result as $activity) {
            $this->assertContains($activity->getTransitionId(),$expected);
        }
        
        
    }
    
}
/* End of File */