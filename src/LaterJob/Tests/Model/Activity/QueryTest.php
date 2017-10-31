<?php
namespace LaterJob\Tests\Model\Activity;

use LaterJob\Model\Activity\Transition;
use LaterJob\Model\Activity\TransitionBuilder;
use LaterJob\Model\Activity\TransitionQuery;
use LaterJob\Model\Activity\TransitionGateway;
use LaterJob\Config\WorkerConfig as WorkerConfig;
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
      *  @return LaterJob\Model\Activity\TransitionGateway
      */   
    protected function getTableGateway()
    {
        $doctrine   = $this->getDoctrineConnection();   
        $metadata   = $this->getTableMetaData()->getTransitionTable(); 
        $table_name = $this->getTableMetaData()->getTransitionTableName();
        $builder    = new TransitionBuilder();
        $mock_event = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();      

        return new TransitionGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testGatewayHandsBackQuery()
    {
        $gateway = $this->getTableGateway();
        $query = $gateway->newQueryBuilder();
        $this->assertInstanceOf('LaterJob\Model\Activity\TransitionQuery',$query);
    }
    
    public function testFilterByWorker()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByWorker(1);
                
        $this->assertRegExp('/WHERE worker_id = :worker_id/',$query->getSql());
        $this->assertEquals(1,$query->getParameter('worker_id'));
    }
    
    public function testFilterByJob()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByJob(1);
                
        $this->assertRegExp('/WHERE job_id = :job_id/',$query->getSql());
        $this->assertEquals(1,$query->getParameter('job_id'));
        
    }
    
    
    public function testFilterByTransition()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByTransition(1);
                
        $this->assertRegExp('/WHERE transition_id = :transition_id/',$query->getSql());
        $this->assertEquals(1,$query->getParameter('transition_id'));
        
        
    }
    
    
    public function testFilterByStates()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByTransition(1)
                    ->filterByStates(1,2);
                
        $this->assertRegExp('/WHERE \(transition_id = :transition_id\) AND \(\(state_id = :state_id_1\) OR \(state_id = :state_id_2\)\)/',$query->getSql());
        $this->assertEquals(1,$query->getParameter('state_id_1'));
        $this->assertEquals(2,$query->getParameter('state_id_2'));
        
        
    }
    
    
    public function testfilterOccuredAfter()
    {
        $gateway = $this->getTableGateway();
        $dte     = new DateTime();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterOccuredAfter($dte);
                
        $this->assertRegExp('/WHERE dte_occured >= :dte_occured_after/',$query->getSql());
        $this->assertEquals($dte,$query->getParameter('dte_occured_after'));
        $this->assertEquals($dte,$query->getParameter('dte_occured_after'));
    }
    
    
    public function testfilterOccuredBefore()
    {
        $gateway = $this->getTableGateway();
        $dte     = new DateTime();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterOccuredBefore($dte);
                
        $this->assertRegExp('/WHERE dte_occured <= :dte_occured_before/',$query->getSql());
        $this->assertEquals($dte,$query->getParameter('dte_occured_before'));
    }
    
    
    public function testfilterDateRange()
    {
        $gateway = $this->getTableGateway();
        $dte_a     = new DateTime();
        $dte_b     = new DateTime();
    
        $query = $gateway->selectQuery()
                ->start()
                    ->filterOccuredBefore($dte_b)
                    ->filterOccuredAfter($dte_a);
                
        $this->assertRegExp('/WHERE \(dte_occured <= :dte_occured_before\) AND \(dte_occured >= :dte_occured_after\)/',$query->getSql());
        $this->assertEquals($dte_b,$query->getParameter('dte_occured_before'));
        $this->assertEquals($dte_a,$query->getParameter('dte_occured_after'));
    }
    
    
    public function testFilterOnlyWorkers()
    {
        
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterOnlyWorkers();
                
        $this->assertRegExp('/WHERE worker_id IS NOT NULL/',$query->getSql());
    }
    
    public function testFilterOnlyJobs()
    {
        
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterOnlyJobs();
                
        $this->assertRegExp('/WHERE job_id IS NOT NULL/',$query->getSql());
    }
    
    
    public function testGroupByState()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->groupByState();
                
        $this->assertRegExp('/GROUP BY state_id/',$query->getSql());
        
    }
    
    
    public function testOrderByState()
    {
        
         $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->orderByState();
                
        $this->assertRegExp('/ORDER BY state_id/',$query->getSql());
        
    }
    
    
    public function testOrderByOccured()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->orderByOccured();
                
        $this->assertRegExp('/ORDER BY dte_occured/',$query->getSql());
    }
    
        
    
}
/* End of File */