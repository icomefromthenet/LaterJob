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
class HandlerLockEventTest extends  TestsWithFixture
{
    
    
    protected $streamed_query_logger = null;
    
        
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'lock_handler_seed.xml');
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
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();      
        
        return new  StorageGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testImplementsSubscriberInterface()
    {
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
    
    
    public function testLockHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        
        $now     = new DateTime('1-11-2013 13:39:00');
        $timeout = new DateTime('1-11-2013 18:00:00');
        $handle = 'f6045c88-f527-4648-9464-4c56b5f3dd11';
        $limit   = 10;
        
        $event = new QueueLockEvent($handle,$timeout,$limit,$now);
        
        $handler->onLock($event);
        
        $resulting_table = $this->getConnection()->createQueryTable("later_job_queue","SELECT * FROM later_job_queue ORDER BY dte_add");        
        
        $expected_table = $this->createXmlDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR ."lock_handler_result.xml")->getTable("later_job_queue");
        $this->assertTablesEqual($expected_table,$resulting_table); 
    }
    
    
    public function testUnlockHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        
        $now     = new DateTime('1-11-2013 13:30:00');
        $timeout = new DateTime('1-11-2013 15:00:00');
        $handle = '550e8400-e29b-41d4-a716-446655440000';
        $limit   = 10;
        
        $event = new QueueLockEvent($handle,$timeout,$limit,$now);
        
        $handler->onUnlock($event);
        
        $resulting_table = $this->getConnection()->createQueryTable("later_job_queue","SELECT * FROM later_job_queue ORDER BY dte_add ASC");        
        
        $expected_table = $this->createXmlDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR ."unlock_handler_result.xml")->getTable("later_job_queue");
        $this->assertTablesEqual($expected_table,$resulting_table);
        
        
    }
    
}
/* End of File */