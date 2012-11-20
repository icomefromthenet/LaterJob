<?php
namespace LaterJob\Tests\Model\Queue;

use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Model\Queue\QueueSubscriber;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Queue\StorageBuilder;
use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueueListEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use DateTime;
use DBALGateway\Feature\StreamQueryLogger;

/**
  *  Unit Tests for Model List Event Handler
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class HandlerListEventTest extends TestsWithFixture
{
    
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'list_handler_seed.xml');
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
      *  @return LaterJob\Model\Transition\TransitionGateway
      */   
    protected function getTableGateway()
    {
        $doctrine   = $this->getDoctrineConnection();   
        $metadata   = $this->getTableMetaData()->getQueueTable(); 
        $table_name = $this->getTableMetaData()->getQueueTableName();
        $builder    = new StorageBuilder();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');      
        
        return new  StorageGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testImplementsSubscriberInterface()
    {
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
    
    
    public function testListOffsetLimit()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        $job_ids      = array(
          'ac449c37-91e1-333d-8c38-9e0d1b7d31ac',
          'c3443b0f-1adc-32df-b375-0c77b47e0e63',
          '367e813d-57ae-3d7b-b521-4dc9fc10970b',
          '343a6fb6-e5fd-3795-b3ad-60127b540427',
          '155811ee-3c0b-31a6-80bb-73b0cbad3d0d'
        );
        
        $event = new QueueListEvent(3,5);
        
        $handler->onList($event);
        
        $results = $event->getResults();
        
        
        $this->assertEquals(5,count($results));
        
        foreach($results as $index => $result){
            $this->assertEquals($result->getJobId(),$job_ids[$index]);
        } 
        
    }
    
     public function testListDescDateOrder()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        $job_ids      = array(
         '19ba586d-6c0f-3d33-8e02-9554f1e525e0',
         '10d91f0d-ae66-39a7-9677-a7cd34a9bf00',
         '54ad2feb-d630-3004-adad-e2fc5992db95',
         '16f80e86-55af-3b2a-b225-102581ada47d',
         'a76456a3-f92e-3784-a568-697d6bc7cd1b'
         
        );
        
        $event = new QueueListEvent(0,5,null,'DESC');
        $handler->onList($event);
        $results = $event->getResults();
        
        $this->assertEquals(5,count($results));
        
        foreach($results as $index => $result){
            $this->assertContains($result->getJobId(),$job_ids);
        } 
        
    }
    
    
   public function testListStateQuery()
   {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
       
        $event        = new QueueListEvent(0,20,QueueConfig::STATE_FINISH);
        $handler->onList($event);
        $results      = $event->getResults();
        
        # there are 10 finished jobs in the queue
        $this->assertEquals(10,count($results));
        
    }
    
    
    public function testBeforeAndAfter()
    {
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
        
        $before  = new DateTime('2013-11-01 01:35:00');
        $after   = new DateTime('2013-11-01 01:00:00');
        
        $event   = new QueueListEvent(0,10,null,null,$before,$after);
        $handler->onList($event);
        $results = $event->getResults();
        
        $this->assertEquals(7,count($results));
    }
    
        
}
/* End of File */