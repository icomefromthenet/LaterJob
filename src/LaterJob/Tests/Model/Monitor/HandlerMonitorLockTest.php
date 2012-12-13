<?php
namespace LaterJob\Tests\Model\Monitor;

use LaterJob\Model\Activity\Transition;
use LaterJob\Model\Activity\TransitionBuilder;
use LaterJob\Model\Activity\TransitionQuery;
use LaterJob\Model\Activity\TransitionGateway;
use LaterJob\Model\Monitor\MonitorSubscriber;
use LaterJob\Model\Monitor\StatsBuilder;
use LaterJob\Model\Monitor\StatsGateway;
use LaterJob\Model\Monitor\Stats;
use LaterJob\Event\MonitoringEvent;
use LaterJob\Event\MonitoringQueryEvent;
use LaterJob\Event\MonitoringEventsMap;
use LaterJob\Event\QueuePurgeActivityEvent;
use LaterJob\Config\QueueConfig as QueueConfig;
use LaterJob\Tests\Base\TestsWithFixture;
use LaterJob\Util\MersenneRandom;
use LaterJob\UUID;
use DateTime;

use DBALGateway\Feature\StreamQueryLogger;


/**
  *  Unit Tests for Model Monitor Query
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class HandlerMonitorLockTest extends  TestsWithFixture
{
    
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'lock_handler_seed.xml');
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
    protected function getTransitionTableGateway()
    {
        $doctrine   = $this->getDoctrineConnection();   
        $metadata   = $this->getTableMetaData()->getTransitionTable(); 
        $table_name = $this->getTableMetaData()->getTransitionTableName();
        $builder    = new TransitionBuilder();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');      
        
        return new TransitionGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
     /**
      *  Fetches a new insance of the gateway
      *
      *  @return LaterJob\Model\Activity\TransitionGateway
      */   
    protected function getMonitorTableGateway()
    {
        $doctrine   = $this->getDoctrineConnection();   
        $metadata   = $this->getTableMetaData()->getMonitorTable(); 
        $table_name = $this->getTableMetaData()->getMonitorTableName();
        $builder    = new StatsBuilder();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');      
        
        return new StatsGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
    
        
    public function testImplementsSubscriberInterface()
    {
        $gateway = $this->getMonitorTableGateway();
        $transition_gateway = $this->getTransitionTableGateway();
        $handler = new MonitorSubscriber($gateway,$transition_gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
   
    
    
    public function testOnMonitorLock()
    {
        $date               = new DateTime('10-12-2012 01:00:00');
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        $stats              = new Stats();
        $event              = new MonitoringEvent($stats);

        $stats->setMonitorDate($date);
        
        $handler->onMonitorLock($event);
        
        $resulting_table = $this->getConnection()->createQueryTable("later_job_monitor","SELECT * FROM later_job_monitor LIMIT 1");        
        $expected_table = $this->createXmlDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR ."lock_handler_result.xml")->getTable("later_job_monitor");
            
        
        $this->assertEquals(true,$event->getResult());
        $this->assertTablesEqual($expected_table,$resulting_table);
        
    }
    
}
/* End of File */