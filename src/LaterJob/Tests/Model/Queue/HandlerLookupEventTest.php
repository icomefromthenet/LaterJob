<?php
namespace LaterJob\Tests\Model\Queue;

use LaterJob\Model\Queue\QueueSubscriber;
use LaterJob\Model\Queue\StorageBuilder;
use LaterJob\Model\Queue\StorageQuery;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Queue\Storage;
use LaterJob\Config\QueueConfig as QueueConfig;
use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueueLookupEvent;
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
class HandlerLookupEventTest extends TestsWithFixture
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
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');      
        
        return new  StorageGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testImplementsSubscriberInterface()
    {
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
    
    
    public function testLookupHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        
        $handle = '4b336e15-cac0-3307-8b81-f1de26e6c383';
        
        $event = new QueueLookupEvent($handle);
        
        $handler->onLookup($event);
        
        $this->assertInstanceOf('LaterJob\Model\Queue\Storage',$event->getResult());
        $this->assertEquals('4b336e15-cac0-3307-8b81-f1de26e6c383',$event->getResult()->getJobId());
    }
    
}
/* End of File */