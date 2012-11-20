<?php
namespace LaterJob\Tests\Model\Queue;

use LaterJob\Model\Queue\StorageQuery;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Queue\StorageBuilder;
use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Tests\Base\TestsWithFixture;
use DateTime;

/**
  *  Unit Tests for Model Queue\StorageQuery
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueryTest extends TestsWithFixture
{
    
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
        
        return new StorageGateway($table_name,$doctrine,$mock_event,$metadata,null,$builder);
        
    }
   
    
    public function testGatewayHandsBackQuery()
    {
        $gateway = $this->getTableGateway();
        $query = $gateway->newQueryBuilder();
        $this->assertInstanceOf('LaterJob\Model\Queue\StorageQuery',$query);
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
    
    
    
    public function testFilterByStateAdd()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByStateAdd();
                
        $this->assertRegExp('/WHERE state_id = :state_id_added/',$query->getSql());
        $this->assertEquals(QueueConfig::STATE_ADD,$query->getParameter('state_id_added'));
    }
    
    public function testFilterByStateStart()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByStateStart();
                
        $this->assertRegExp('/WHERE state_id = :state_id_started/',$query->getSql());
        $this->assertEquals(QueueConfig::STATE_START,$query->getParameter('state_id_started'));
    }
    
    
    public function testFilterByStateFinish()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByStateFinish();
                
        $this->assertRegExp('/WHERE state_id = :state_id_finished/',$query->getSql());
        $this->assertEquals(QueueConfig::STATE_FINISH,$query->getParameter('state_id_finished'));
    }
    
    public function testFilterByStateError()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByStateError();
                
        $this->assertRegExp('/WHERE state_id = :state_id_error/',$query->getSql());
        $this->assertEquals(QueueConfig::STATE_ERROR,$query->getParameter('state_id_error'));
    }
    
    public function testFilterByStateErrorPassedRetryWait()
    {
        $gateway = $this->getTableGateway();
        $now     = new DateTime();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByStateErrorPassedRetryWait($now);
                
        $this->assertRegExp('/WHERE \(state_id = :state_id_error\) AND \(retry_last <= :retry_last_ltenow\)/',$query->getSql());
        $this->assertEquals(QueueConfig::STATE_ERROR,$query->getParameter('state_id_error'));
        $this->assertEquals($now,$query->getParameter('retry_last_ltenow'));
    }
    
    
    
    
    public function testFilterByStateFail()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByStateFail();
                
        $this->assertRegExp('/WHERE state_id = :state_id_failed/',$query->getSql());
        $this->assertEquals(QueueConfig::STATE_FAIL,$query->getParameter('state_id_failed'));
        
    }
    
    public function testFilterByCombinationStates()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByStateFail()
                    ->filterByStateError();
                
        $this->assertRegExp('/WHERE \(state_id = :state_id_failed\) OR \(state_id = :state_id_error\)/',$query->getSql());
        $this->assertEquals(QueueConfig::STATE_FAIL,$query->getParameter('state_id_failed'));
        $this->assertEquals(QueueConfig::STATE_ERROR,$query->getParameter('state_id_error'));
    }
    
    
    
    public function testFilterByLockout()
    {
        
        $gateway = $this->getTableGateway();
        $hash = md5('aaaaaa');
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByLockout($hash);
        
        $this->assertRegExp('/WHERE handle = :lockout_handle/',$query->getSql());
        $this->assertEquals($hash,$query->getParameter('lockout_handle'));
        
    }
    
    
    public function testFilterByEmptyLockout()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByEmptyLockout();
        
        $this->assertRegExp('/WHERE handle IS NULL/',$query->getSql());
        
    }
    
    public function testFilterByExpiredLockout()
    {
        $gateway = $this->getTableGateway();
        $date = new DateTime();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByExpiredLockout($date);
        
        $this->assertRegExp('/WHERE lock_timeout <= :lock_timeout_exp/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('lock_timeout_exp'));
    }
    
    
    public function testFilterEmptyOrExpiredLockout()
    {
        $gateway = $this->getTableGateway();
        $date = new DateTime();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByJob(1)                
                    ->filterByExpiredOrEmptyLockout($date);
        
        $this->assertRegExp('/WHERE \(job_id = :job_id\) AND \(\(handle IS NULL\) OR \(lock_timeout <= :lock_timeout_exp\)\)/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('lock_timeout_exp'));
    }
    
    public function testFilterByLockTimmerAfter()
    {
        $date = new DateTime();

        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByLockTimmerAfter($date);
                    
                    
        $this->assertRegExp('/WHERE lock_timeout >= :lock_timeout_after/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('lock_timeout_after'));
    }
    
    
    
    public function testFilterByLockTimmerBefore()
    {
        $date = new DateTime();

        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByLockTimmerBefore($date);
                    
                    
        $this->assertRegExp('/WHERE lock_timeout <= :lock_timeout_before/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('lock_timeout_before'));
        
        
    }
    
    
    public function testFilterLockoutRange()
    {
        $date_a = new DateTime();
        $date_b = new DateTime();

        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByLockTimmerBefore($date_b)
                    ->filterByLockTimmerAfter($date_a);
                    
        $this->assertRegExp('/WHERE \(lock_timeout <= :lock_timeout_before\) AND \(lock_timeout >= :lock_timeout_after\)/',$query->getSql());
        $this->assertEquals($date_b,$query->getParameter('lock_timeout_before'));
        $this->assertEquals($date_a,$query->getParameter('lock_timeout_after'));
        
    }
    
    public function testFilterByAddedAfter()
    {
         $date = new DateTime();

        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByAddedAfter($date);
                    
                    
        $this->assertRegExp('/WHERE dte_add >= :dte_occured_after/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('dte_occured_after'));
        
        
    }
    
    
    public function testFilterByAddedBefore()
    {
        $date = new DateTime();

        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByAddedBefore($date);
                    
                    
        $this->assertRegExp('/WHERE dte_add <= :dte_occured_before/',$query->getSql());
        $this->assertEquals($date,$query->getParameter('dte_occured_before'));
        
        
    }
    
    public function testFilterAddedRange()
    {
        $date_a = new DateTime();
        $date_b = new DateTime();

        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->filterByAddedBefore($date_b)
                    ->filterByAddedAfter($date_a);
                    
        $this->assertRegExp('/WHERE \(dte_add <= :dte_occured_before\) AND \(dte_add >= :dte_occured_after\)/',$query->getSql());
        $this->assertEquals($date_b,$query->getParameter('dte_occured_before'));
        $this->assertEquals($date_a,$query->getParameter('dte_occured_after'));
        
    }
    
    
    public function testOrderByAdded()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->orderByDateAdded('DESC');
                    
        $this->assertRegExp('/ORDER BY dte_add DESC/',$query->getSql());
        
        
        $query = $gateway->selectQuery()
                ->start()
                    ->orderByDateAdded();
                    
        $this->assertRegExp('/ORDER BY dte_add ASC/',$query->getSql());
       
    }
    
    
    public function testGroupByState()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->groupByState();
        
        $this->assertRegExp('/GROUP BY state_id/',$query->getSql());
    }
    
    
    public function testGroupByLockoutHandle()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->groupByLockoutHandle();
         
        $this->assertRegExp('/GROUP BY handle/',$query->getSql());
    }
    
    
    public function testCalculateJobCount()
    {
        $gateway = $this->getTableGateway();
        
        $query = $gateway->selectQuery()
                ->start()
                    ->calculateJobCount();
        
        $this->assertRegExp('/COUNT\(job_id\) AS job_count/',$query->getSql());
        
        # test with a group by clause
        $query = $gateway->selectQuery()
                ->start()
                    ->calculateJobCount()
                    ->groupByState();
        
        $this->assertRegExp('/COUNT\(job_id\) AS job_count/',$query->getSql());
        $this->assertRegExp('/GROUP BY state_id/',$query->getSql());
        
    }
    
    
    
}
/* End of File */