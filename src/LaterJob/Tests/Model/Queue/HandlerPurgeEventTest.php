<?php
namespace LaterJob\Tests\Model\Queue;

use LaterJob\Model\Queue\QueueSubscriber;
use LaterJob\Model\Queue\StorageBuilder;
use LaterJob\Model\Queue\StorageQuery;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Queue\Storage;
use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueuePurgeEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use DateTime;

/**
  *  Unit Tests for Model Queue Lock Event Handler
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class HandlerPurgeEventTest extends  TestsWithFixture
{
    
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'purge_handler_seed.xml');
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
    
    
    public function testPurgeHandler()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        
        $added        = new DateTime('1st November 2013 01:20:00');
        
        # create the transition
        $event = new QueuePurgeEvent($added);
        
        $handler->onPurge($event);
        
        $this->assertEquals(4,$event->getResult());
    }
    
    
    public function testPurgeHandlerNoneToRemove()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        $added        = new DateTime('1st October 2013 00:00:00');
        
        # create the transition
        $event = new QueuePurgeEvent($added);
        
        $handler->onPurge($event);
        
        $this->assertEquals(0,$event->getResult());
    }
    
    
    public function testReceiveHandlerDB()
    {
        $gateway      = $this->getTableGateway();
        $handler      = new QueueSubscriber($gateway);
        
        $added        = new DateTime('1st November 2013 01:20:00');
        
        # create the transition
        $event = new QueuePurgeEvent($added);
        
        $handler->onPurge($event);
        
        $this->assertEquals(4,$event->getResult());
        
        # assert save result set matches          
        $resulting_table = $this->getConnection()
                                ->createQueryTable("later_job_queue","SELECT * FROM later_job_queue ORDER BY dte_add");        
        $expected_table = $this->createXmlDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR ."purge_handler_result.xml")
                                ->getTable("later_job_queue");
        
        $this->assertTablesEqual($expected_table,$resulting_table); 
        
    }
    
    
}
/* End of File */