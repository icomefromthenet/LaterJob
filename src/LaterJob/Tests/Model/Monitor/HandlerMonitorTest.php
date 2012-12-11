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
use LaterJob\Config\Queue as QueueConfig;
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
class HandlerMonitorTest extends  TestsWithFixture
{
    
    public function getDataSet()
    {
        return  $this->createMySQLXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'activity_seed.xml');
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
   
   
    public function testCountJobStates()
    {
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $date               = new DateTime('27-11-2012 13:00:00');
        
        $this->assertEquals(array( 0 => array(
            'state_count' => 60,
            'state_id' =>   1
            ))
        ,$transition_gateway->countQueueJobStates($date));
        
        $this->assertEquals(array(
            0 => array(
                'state_count' => 100,
                'state_id' =>   1  
            ),
            1 => array(
                'state_count' => 56,
                'state_id' =>   2  
            ),
            2 => array(
                'state_count' => 53,
                'state_id' =>   3  
            ),
            3 => array(
                'state_count' => 2,
                'state_id' =>   5  
            )
            
        ),$transition_gateway->countQueueJobStates($date,'+5 hours'));
        
    }
   
   
    public function testMeanServiceTime()
    {
        $transition_gateway = $this->getTransitionTableGateway();
        $date               = new DateTime('27-11-2012 13:00:00');
        
        $result = $transition_gateway->getMeanServiceTime($date,'+ 5 hours');
        
        $this->assertEquals($result,9244.7660);
    }
    
    
    public function testMaxServiceTime()
    {
        $transition_gateway = $this->getTransitionTableGateway();
        $date               = new DateTime('27-11-2012 13:00:00');
        
        $result = $transition_gateway->getMaxServiceTime($date,'+ 5 hours');
        
        $this->assertEquals($result,10439);
        
    }
    
    public function testMinServiceTime()
    {
        $transition_gateway = $this->getTransitionTableGateway();
        $date               = new DateTime('27-11-2012 13:00:00');
        
        $result = $transition_gateway->getMinServiceTime($date,'+ 5 hours');
        $this->assertEquals($result,8038);
    }
   
    public function testWorkerMaxRunningTime()
    {
        $transition_gateway = $this->getTransitionTableGateway();
        $date               = new DateTime('27-11-2012 13:00:00');
        
        $result = $transition_gateway->getWorkerMaxRunningTime($date,'+ 5 hours');
        $this->assertEquals($result,495);
    }
    
    
    public function testWorkerMinRunningTime()
    {
        $transition_gateway = $this->getTransitionTableGateway();
        $date               = new DateTime('27-11-2012 13:00:00');
        
        $result = $transition_gateway->getWorkerMinRunningTime($date,'+ 5 hours');
        $this->assertEquals($result,459);
    }
    
    
    public function testWorkerMeanRunningTime()
    {
        $transition_gateway = $this->getTransitionTableGateway();
        $date               = new DateTime('27-11-2012 13:00:00');
        
        $result = $transition_gateway->getWorkerMeanRunningTime($date,'+ 5 hours');
        $this->assertEquals($result,482.3333);
    }
   
   
    public function testWorkerMeanThroughput()
    {
        $transition_gateway = $this->getTransitionTableGateway();
        $date               = new DateTime('27-11-2012 13:00:00');
        
        $result = $transition_gateway->getWorkerMeanThroughput($date,'+ 5 hours');
        $this->assertEquals($result,27.5);
    }
   
    public function testOnMonitorRun()
    {
        $date               = new DateTime('27-11-2012 13:00:00');
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        # create stats container for results of the monitoring event
        $stats              = new Stats();
        $stats->setMonitorDate($date);
        $stats->setWorkerMaxThroughput(50);
        
        # create event object to pass to handle
        $event              = new MonitoringEvent($stats);
        $event->setInterval('+ 5 hours');
                
        # pass event to the handler        
        $handler->onMonitorRun($event);
        
        $this->assertEquals(100,$event->getStats()->getQueueJobsAdded());
        $this->assertEquals(2  ,$event->getStats()->getQueueJobsFailed());
        $this->assertEquals(53 ,$event->getStats()->getQueueJobsCompleted());
        $this->assertEquals(56 ,$event->getStats()->getQueueJobsProcessing());
        $this->assertEquals(null,$event->getStats()->getQueueJobsError());
        
        $this->assertEquals(8038, $event->getStats()->getJobMinServiceTime());
        $this->assertEquals(10439, $event->getStats()->getJobMaxServiceTime());
        $this->assertEquals(9244.7660, $event->getStats()->getJobMeanServiceTime());
        
        $this->assertEquals(495, $event->getStats()->getWorkerMaxTime());
        $this->assertEquals(459, $event->getStats()->getWorkerMinTime());
        $this->assertEquals(482.3333, $event->getStats()->getWorkerMeanTime());
        $this->assertEquals(27.5,$event->getStats()->getWorkerMeanThroughput());
        $this->assertEquals(0.55,$event->getStats()->getWorkerMeanUtilization());
    } 
   
   
    public function testOnMonitorLock()
    {
        $date               = new DateTime('27-11-2012 13:00:00');
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        # create stats container for results of the monitoring event
        $stats              = new Stats();
        $stats->setMonitorDate($date);
        $stats->setWorkerMaxThroughput(50);
        
        # create event object to pass to handle
        $event              = new MonitoringEvent($stats);
        $event->setInterval('+ 5 hours');
        
        
        $handler->onMonitorLock($event);
        
        $this->assertTrue($event->getResult());
        $this->assertEquals(1,$event->getStats()->getMonitorId(1));
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '2012-11-27 13:00:00' for key
      */
    public function testOnMonitorLockAlreadyHasException()
    {
        $date               = new DateTime('27-11-2012 13:00:00');
        $transition_gateway = $this->getTransitionTableGateway();
        $monitor_gateway    = $this->getMonitorTableGateway();
        $handler            = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        # create stats container for results of the monitoring event
        $stats              = new Stats();
        $stats->setMonitorDate($date);
        $stats->setWorkerMaxThroughput(50);
        
        $stats_repeat              = new Stats();
        $stats_repeat->setMonitorDate($date);
        $stats_repeat->setWorkerMaxThroughput(50);
        
        # create event object to pass to handle
        $event              = new MonitoringEvent($stats);
        $event->setInterval('+ 5 hours');
        
        $event_repeat              = new MonitoringEvent($stats_repeat);
        $event_repeat->setInterval('+ 5 hours');
        
        # first call work as normal
        $handler->onMonitorLock($event);
        $this->assertTrue($event->getResult());
        $this->assertEquals(1,$event->getStats()->getMonitorId(1));
        
        # lock in place should FAIL
        $handler->onMonitorLock($event_repeat);
        
    }
    
    
   
}
/* End of File */