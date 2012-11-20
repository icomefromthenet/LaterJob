<?php
namespace LaterJob\Tests\Model\Queue;

use LaterJob\Model\Queue\QueueSubscriber;
use LaterJob\Model\Queue\StorageBuilder;
use LaterJob\Model\Queue\StorageQuery;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Queue\Storage;
use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueueListEvent;
use LaterJob\Event\QueueLockEvent;
use LaterJob\Event\QueuePurgeEvent;
use LaterJob\Event\QueueReceiveEvent;
use LaterJob\Event\QueueRemoveEvent;
use LaterJob\Event\QueueSendEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\UUID;
use LaterJob\Util\MersenneRandom;
use DateTime;

/**
  *  Unit Tests for Model Queue Lock Event Handler
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class HandlerReceiveEventTest extends  TestsWithFixture
{
    
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'receive_handler_seed.xml');
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
    
    
    public function testReceiveHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        
        $uuid         = new UUID(new MersenneRandom(10000));   
        $job_name     = 'test_job_1';
        $added        = new DateTime('01-01-2014 00:00:00');
        
        
        # create the transition
        $storage = new Storage();
        $storage->setJobId($uuid->v3($uuid->v4(),$job_name));
        $storage->setDateAdded($added);
        $storage->setJobData(new \stdClass());
        $storage->setState(QueueConfig::STATE_ADD);
        $storage->setRetryLeft(3);
        
        $event = new QueueReceiveEvent($storage);
        $handler->onReceive($event);
        
        # assert save operation          
        $this->assertTrue($event->getResult());
    }
    
    
    public function testReceiveHandlerDB()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        
        $uuid         = new UUID(new MersenneRandom(10000));   
        $job_name     = 'test_job_1';
        $added        = new DateTime('01-01-2014 00:00:00');
        
        
        # create the transition
        $storage = new Storage();
        $storage->setJobId($uuid->v3($uuid->v4(),$job_name));
        $storage->setDateAdded($added);
        $storage->setJobData(new \stdClass());
        $storage->setState(QueueConfig::STATE_ADD);
        $storage->setRetryLeft(3);
        
        $event = new QueueReceiveEvent($storage);
        $handler->onReceive($event);
        
        # assert save result set matches          
        $resulting_table = $this->getConnection()
                                ->createQueryTable("later_job_queue","SELECT * FROM later_job_queue ORDER BY dte_add");        
        $expected_table = $this->createXmlDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR ."receive_handler_result.xml")
                                ->getTable("later_job_queue");
        
        $this->assertTablesEqual($expected_table,$resulting_table); 
        
    }
 
    
}
/* End of File */