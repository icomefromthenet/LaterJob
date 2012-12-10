<?php
namespace LaterJob\Tests\Model\Monitor;

use LaterJob\Model\Monitor\Stats;
use LaterJob\Model\Monitor\StatsBuilder;
use LaterJob\Model\Monitor\StatsQuery;
use LaterJob\Model\Monitor\StatsGateway;
use LaterJob\Config\Worker as WorkerConfig;
use LaterJob\Tests\Base\TestsWithFixture;
use DateTime;

/**
  *  Unit Tests for Model Transition Query and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueryTest extends  TestsWithFixture
{
    
    /**
      *  Fetches a new insance of the gateway
      *
      *  @return LaterJob\Model\Monitor\StatsGateway
      */   
    protected function getTableGateway()
    {
        $doctrine   = $this->getDoctrineConnection();   
        $metadata   = $this->getTableMetaData()->getMonitorTable(); 
        $table_name = $this->getTableMetaData()->getMonitorTableName();
        $builder    = new StatsBuilder();
        $mock_event = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');      
        
        return new StatsGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testGatewayHandsBackQuery()
    {
        $gateway = $this->getTableGateway();
        $query = $gateway->newQueryBuilder();
        $this->assertInstanceOf('LaterJob\Model\Monitor\StatsQuery',$query);
    }
    
    public function testFilterByWorker()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByMonitor('1');
                
        $this->assertRegExp('/WHERE monitor_id = :monitor_id/',$query->getSql());
        $this->assertEquals(1,$query->getParameter('monitor_id'));
    }
    
    public function testFilterByCoverDate()
    {
        $gateway = $this->getTableGateway();
        $date    = new DateTime();
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByCoverDate($date);
                
        $this->assertRegExp('/WHERE monitor_dte = :monitor_dte/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('monitor_dte'));
    }
    
    public function testAndWhereCoverDateBefore()
    {
        $gateway = $this->getTableGateway();
        $date    = new DateTime();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByMonitor(1)
                    ->andWhereCoverDateBefore($date);
                    
        $this->assertRegExp('/AND \(monitor_dte <= :monitor_dte_before\)/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('monitor_dte_before'));            
    }
    
    public function testAndWhereCoverDateAfter()
    {
        $gateway = $this->getTableGateway();
        $date    = new DateTime();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByMonitor(1)
                    ->andWhereCoverDateAfter($date);
                    
        $this->assertRegExp('/AND \(monitor_dte >= :monitor_dte_after\)/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('monitor_dte_after'));   
        
    }
    
    public function testAndWhereLocked()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByMonitor(1)
                    ->andWhereLocked();
                    
        $this->assertRegExp('/AND \(monitor_complete = :monitor_complete\)/',$query->getSql());
        $this->assertEquals(true,$query->getParameter('monitor_complete'));   
    }
    
    
    public function testAndWhereUnlocked()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByMonitor(1)
                    ->andWhereUnLocked();
                    
        $this->assertRegExp('/AND \(monitor_complete = :monitor_complete\)/',$query->getSql());
        $this->assertEquals(false,$query->getParameter('monitor_complete'));   
    }
    
}
/* End of File */