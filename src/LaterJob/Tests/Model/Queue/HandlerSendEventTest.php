<?php
namespace LaterJob\Tests\Model\Queue;

use LaterJob\Model\Queue\QueueSubscriber;
use LaterJob\Model\Queue\StorageBuilder;
use LaterJob\Model\Queue\StorageQuery;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Queue\Storage;
use LaterJob\Config\QueueConfig as QueueConfig;
use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueueListEvent;
use LaterJob\Event\QueueLockEvent;
use LaterJob\Event\QueuePurgeEvent;
use LaterJob\Event\QueueReceiveEvent;
use LaterJob\Event\QueueRemoveEvent;
use LaterJob\Event\QueueSendEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\UUID;
use DateTime;
use DBALGateway\Feature\StreamQueryLogger;

/**
  *  Unit Tests for Model Queue Lock Event Handler
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class HandlerSendEventTest extends  TestsWithFixture
{
    
    
    protected $streamed_query_logger = null;
    
        
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'send_handler_seed.xml');
    }
    
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
    
    
    public function testSendHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        $now          = new DateTime('1-11-2013 13:36:00'); # only pickup 2 locked error jobs from 3 available
        $limit        = 10;
        $send_event   = new QueueSendEvent('550e8400-e29b-41d4-a716-446655440000',$now,$limit);
        $expecting    = array (
            # locked jobs where later timer is not expired
            # the third locked job , has a timer not expired and should not be
            # selected by the send operation till later
            '6f63ed2f-7950-351b-a15f-2b7d041a2fbf',
            '82e0bd38-ac04-3704-8638-855bde929739',
            
            # added jobs at bottom queue
            'fe7d72b0-7fb8-398e-b61d-121ca45efad4',
            'd856af16-85a2-32d0-991e-3f0c2c724996',
            '6082dee4-1002-3d08-97a6-de85085bf11b',
            'c9b32db1-61f7-3e21-a0a4-f807c84683c1',
            '795b6999-42d1-3188-87a9-757057a2d4c1',
            'bf4c5d72-27e6-3cf0-9410-85af67e3f6b2',
            '081ef04e-99d4-3824-9a2f-11c8f6caccb5',
            'ebebb817-5743-3ddb-9ede-25123c02e35d'
        );
        
        # run the handler with selection of 10 jobs
        # in operation only limit to 1 return per event, but testing we need 10.
        $handler->onSend($send_event);
        $results = $send_event->getResult();
        
        # match expected returns with actual returns
        for($i =0; $i < count($results); $i++) {
            $this->assertEquals($expecting[$i],$results->get($i)->getJobId());
        }
        
    }
    
    
    public function testSendHandlerNoReturn()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        $now          = new DateTime('1-11-2013 13:36:00'); # only pickup 2 locked error jobs from 3 available
        $limit        = 10;
        $send_event   = new QueueSendEvent('41d4-a716-446655440000',$now);

        $handler->onSend($send_event);
        $this->assertNull($send_event->getResult());
    }
}
/* End of File */