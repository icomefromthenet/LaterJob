<?php
namespace LaterJob\Tests\Model\Transition;

use LaterJob\Model\Transition\Transition;
use LaterJob\Model\Transition\TransitionBuilder;
use LaterJob\Model\Transition\TransitionQuery;
use LaterJob\Model\Transition\TransitionGateway;
use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Event\QueuePurgeActivityEvent;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\UUID;
use DateTime;
use LaterJob\Model\Transition\QueueSubscriber;
use LaterJob\Util\MersenneRandom;
use DBALGateway\Feature\StreamQueryLogger;


/**
  *  Unit Tests for Model Transition Query and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class HandlerPurgeHistoryTest extends  TestsWithFixture
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
      *  @return LaterJob\Model\Transition\TransitionGateway
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
        $handler = new QueueSubscriber($gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
   
   
   
    public function testPurge()
    {
        $gateway = $this->getTableGateway();
        $handler = new QueueSubscriber($gateway);
        
        $before = new DateTime('2012-11-22 13:10:00');
        $event = new QueuePurgeActivityEvent($before);
        
        $handler->onPurgeHistory($event);
        
        #assert the result
        $this->assertEquals(10,$event->getResult());
        
        # assert datasets match
        $resulting_table = $this->getConnection()->createQueryTable("later_job_transition","SELECT * FROM later_job_transition ORDER BY dte_occured ASC");        
        $expected_table = $this->createXmlDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR ."purgehistory_handler_result.xml")->getTable("later_job_transition");
        
        $this->assertTablesEqual($expected_table,$resulting_table);
    }
    
}
/* End of File */