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
class HandlerMonitorQueryTest extends  TestsWithFixture
{
    
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'query_handler_seed.xml');
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
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();      
        
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
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();      
        
        return new StatsGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
    
        
    public function testImplementsSubscriberInterface()
    {
        $gateway = $this->getMonitorTableGateway();
        $transition_gateway = $this->getTransitionTableGateway();
        $handler = new MonitorSubscriber($gateway,$transition_gateway);
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
   
    
    
    public function testOnMonitorQueryNoOffset()
    {
        
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        $start              = new DateTime('11-12-2012 01:00:00');
        $end                = new DateTime('11-12-2012 02:00:00');
        $limit              = 2;
        $offset             = 0;
        $locked             = false;
        
        $event              = new MonitoringQueryEvent($offset,$limit,'ASC',$start,$end,$locked);
        
        # run the event
        $handler->onMonitorQuery($event);
        
        # test the results
        $results = $event->getResult();
        
        $result = $event->getResult();
        
        $this->assertEquals(2,count($result));
        $this->assertEquals(1,$result[0]->getMonitorId());
        $this->assertEquals(2,$result[1]->getMonitorId());
    }
    
    
    public function testOnMonitorQueryWithOffset()
    {
        
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        $start              = new DateTime('11-12-2012 01:00:00');
        $end                = new DateTime('11-12-2012 02:00:00');
        $limit              = 2;
        $offset             = 1;
        
        $event              =new MonitoringQueryEvent($offset,$limit,'ASC',$start,$end);
        
        # run the event
        $handler->onMonitorQuery($event);
        
        # test the results
        $results = $event->getResult();
        
        $result = $event->getResult();
        
        # only fetch single result the 2nd record
        # if true date range limit and offset are correctly set
        $this->assertEquals(1,count($result));
        $this->assertEquals(2,$result[0]->getMonitorId());
    }
    
    public function testOnMonitorQueryOnlyUnlocked()
    {
        
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        $start              = new DateTime('11-12-2012 01:00:00');
        $end                = new DateTime('13-12-2012 02:00:00');
        $limit              = 30;
        $offset             = 0;
        
        $event              = new MonitoringQueryEvent($offset,$limit,'ASC',$start,$end);
        
        # run the event
        $handler->onMonitorQuery($event);
        
        # test the results
        $results = $event->getResult();
        
        $result = $event->getResult();
        
        # 24 results 2 which are locked, using locked = false
        # should exclude them from result set
        $this->assertEquals(22,count($result));
        
    }
    
    
    public function testOnMonitorQueryDescOrder()
    {
        
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        $start              = new DateTime('11-12-2012 01:00:00');
        $end                = new DateTime('13-12-2012 02:00:00');
        $limit              = 30;
        $offset             = 0;
        
        $event              = new MonitoringQueryEvent($offset,$limit,'DESC',$start,$end);
        
        # run the event
        $handler->onMonitorQuery($event);
        
        # test the results
        $results = $event->getResult();
        
        $result = $event->getResult();
        
        # 24 results 2 which are locked, using locked = false
        # should exclude them from result set
        $this->assertEquals(22,count($result));
        $this->assertEquals(22,$result[0]->getMonitorId());
        
    }
    
     
    public function testOnMonitorQueryAllRows()
    {
        
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        $limit              = 30;
        $offset             = 0;
        $event              = new MonitoringQueryEvent($offset,$limit,'DESC',null,null,true);
        
        # run the event
        $handler->onMonitorQuery($event);
        
        # test the results
        $results = $event->getResult();
        
        $result = $event->getResult();
        
        # 24 results 2 which are locked, using locked = false
        # should exclude them from result set
        $this->assertEquals(24,count($result));
        $this->assertEquals(24,$result[0]->getMonitorId());
        
    }
    
}
/* End of File */