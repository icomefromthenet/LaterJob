<?php
namespace LaterJob\Tests\Model\Queue;

use LaterJob\Model\Queue\Storage;
use LaterJob\Model\Queue\StorageBuilder;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for Model Queue StorageBuilder and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class BuilderTest extends  PHPUnit_Framework_TestCase
{
    
    public function testEntityProperties()
    {
        $storage = new Storage();
        
        $dte_added      = new DateTime();
        $job_data       = new \stdClass();
        $job_id         = '67b9a976-2edd-3e6e-adc3-22adeb5b3949';
        $lockout_handle = md5($job_id);
        $lockout_timer  = new DateTime();
        $retry          = 3;
        $state          = 1;
        $retry_last     = new DateTime();
        
        $storage->setDateAdded($dte_added);
        $storage->setJobData($job_data);
        $storage->setJobId($job_id);
        $storage->setLockoutHandle($lockout_handle);
        $storage->setLockoutTimer($lockout_timer);
        $storage->setRetryLeft($retry);
        $storage->setRetryLast($retry_last);
        $storage->setState($state);
        
        $this->assertEquals($dte_added,$storage->getDateAdded());
        $this->assertEquals($job_data, $storage->getJobData());
        $this->assertEquals($job_id,   $storage->getJobId());
        $this->assertEquals($lockout_handle, $storage->getLockoutHandle());
        $this->assertEquals($lockout_timer,  $storage->getLockoutTimer());
        $this->assertEquals($retry, $storage->getRetryLeft());
        $this->assertEquals($retry_last,$storage->getRetryLast());
        $this->assertEquals($state, $storage->getState());
    }
    
    
    public function testBuilder()
    {
        $builder = new StorageBuilder();
        
        $dte_added      = new DateTime();
        $job_data       = new \stdClass();
        $job_id         = '67b9a976-2edd-3e6e-adc3-22adeb5b3949';
        $lockout_handle = md5($job_id);
        $lockout_timer  = new DateTime();
        $retry          = 3;
        $state          = 1;
        $retry_last     = new DateTime();
        
        $data = array(
          'job_id'       => $job_id ,
          'state_id'     => $state ,
          'dte_add'      => $dte_added,
          'retry_count'  => $retry,
          'retry_last'   => $retry_last,
          'job_data'     => $job_data,
          'handle'       => $lockout_handle,
          'lock_timeout' => $lockout_timer,  
            
        );
        
        $storage = $builder->build($data);
        
        $this->assertEquals($dte_added,$storage->getDateAdded());
        $this->assertEquals($job_data, $storage->getJobData());
        $this->assertEquals($job_id,   $storage->getJobId());
        $this->assertEquals($lockout_handle, $storage->getLockoutHandle());
        $this->assertEquals($lockout_timer,  $storage->getLockoutTimer());
        $this->assertEquals($retry, $storage->getRetryLeft());
        $this->assertEquals($state, $storage->getState());
        $this->assertEquals($retry_last,$storage->getRetryLast());
        
        # test builder no handle
        
        $data = array(
          'job_id'       => $job_id ,
          'state_id'     => $state ,
          'dte_add'      => $dte_added,
          'retry_count'  => $retry,
          'job_data'     => $job_data,
          'handle'       => null,
          'lock_timeout' => null,
          'retry_last'   => null,
            
        );
        
        $storage = $builder->build($data);
        
        $this->assertEquals($dte_added,$storage->getDateAdded());
        $this->assertEquals($job_data, $storage->getJobData());
        $this->assertEquals($job_id,   $storage->getJobId());
        $this->assertEquals(null, $storage->getLockoutHandle());
        $this->assertEquals(null,  $storage->getLockoutTimer());
        $this->assertEquals($retry, $storage->getRetryLeft());
        $this->assertEquals(null,$storage->getRetryLast());
        $this->assertEquals($state, $storage->getState());
        
    }
    
    
    public function testDemolish()
    {
        
        $storage = new Storage();
        $builder = new StorageBuilder();
         
        $dte_added      = new DateTime();
        $job_data       = new \stdClass();
        $job_id         = '67b9a976-2edd-3e6e-adc3-22adeb5b3949';
        $lockout_handle = md5($job_id);
        $lockout_timer  = new DateTime();
        $retry          = 3;
        $state          = 1;
        $retry_last     = new DateTime();
        
        $storage->setDateAdded($dte_added);
        $storage->setJobData($job_data);
        $storage->setJobId($job_id);
        $storage->setLockoutHandle($lockout_handle);
        $storage->setLockoutTimer($lockout_timer);
        $storage->setRetryLeft($retry);
        $storage->setRetryLast($retry_last);
        $storage->setState($state);
        
        $this->assertEquals(array(
          'job_id'       => $job_id ,
          'retry_count'  => $retry,
          'dte_add'      => $dte_added,
          'state_id'     => $state ,
          'job_data'     => $job_data,
          'handle'       => $lockout_handle,
          'lock_timeout' => $lockout_timer,
          'retry_last'   => $retry_last
            
        ),$builder->demolish($storage));
        
    }
    
}
/* End of File */