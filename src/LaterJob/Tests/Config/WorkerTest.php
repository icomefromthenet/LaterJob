<?php
namespace LaterJob\Tests\Config;

use LaterJob\Config\Worker;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for Worker Config Component
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class WorkerTest extends  PHPUnit_Framework_TestCase
{
    
    public function testQueueConfigParse()
    {
        $config = new Worker();

        $data = array('worker' => array(
            'jobs_process'      => 4,
            'mean_runtime'      => (60*60*1),
            'cron_script'       => '* * * * *',
            'job_lock_timeout'  => (60*60*4),
            'worker_name'       => 'bobemail'
            
        ));
        
        $config->parse($data);        
        
        $this->assertEquals($data['worker']['jobs_process'],$config->getJobsToProcess());
        $this->assertEquals($data['worker']['mean_runtime'],$config->getMeanRuntime());
        $this->assertEquals($data['worker']['cron_script'],$config->getCronDefinition());
        $this->assertEquals($data['worker']['job_lock_timeout'],$config->getJobLockoutTime());
        $this->assertEquals($data['worker']['worker_name'],$config->getWorkerName());
         
    }
    
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExpectionMessage Invalid configuration for path "worker.cron_script": Cron Script "* * * *" Failed to Parse 
      */
    public function testQueueConfigBadCron()
    {
        $config = new Worker();

        $data = array('worker' => array(
            'jobs_process'      => 4,
            'mean_runtime'      => (60*60*1),
            'cron_script'       => '* * * *',
            'job_lock_timeout'  => (60*60*4),
            'worker_name'       => 'bobemail'
            
        ));
        
        $config->parse($data);   
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExpectionMessage Invalid configuration for path "worker.jobs_process": Jobs to Process must be an integer with value greater than 0
      */
    public function testQueueConfigJobProcessNotInteger()
    {
        $config = new Worker();

        $data = array('worker' => array(
            'jobs_process'      => 'aaaa',
            'mean_runtime'      => (60*60*1),
            'cron_script'       => '* * * * *',
            'job_lock_timeout'  => (60*60*4),
            'worker_name'       => 'bobemail'
            
        ));
        
        $config->parse($data); 
        
    }
    
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExpectionMessage Invalid configuration for path "worker.mean_runtime": Run Time must be an integer with value greater than 0
      */
    public function testQueueConfigMeanRuntimeNotInteger()
    {
        $config = new Worker();

        $data = array('worker' => array(
            'jobs_process'      => 5,
            'mean_runtime'      => 'aaaa',
            'cron_script'       => '* * * * *',
            'job_lock_timeout'  => (60*60*4),
            'worker_name'       => 'bobemail'
            
        ));
        
        $config->parse($data); 
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExpectionMessage Invalid configuration for path "worker.job_lock_timeout": Job Lockout Time must be an integer with value greater than 0
      */
    public function testQueueConfigLockoutTimerNotInteger()
    {
        $config = new Worker();

        $data = array('worker' => array(
            'jobs_process'      => 5,
            'mean_runtime'      => (60*60*4),
            'cron_script'       => '* * * * *',
            'job_lock_timeout'  => 'aaaa',
            'worker_name'       => 'bobemail'
            
        ));
        
        $config->parse($data); 
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExpectionMessage The path "worker.worker_name" cannot contain an empty value, but got ""
      */
    public function testQueueConfigEmptyWorkerName()
    {
        $config = new Worker();

        $data = array('worker' => array(
            'jobs_process'      => 5,
            'mean_runtime'      => (60*60*4),
            'cron_script'       => '* * * * *',
            'job_lock_timeout'  => (60*60*4),
            'worker_name'       => ''
            
        ));
        
        $config->parse($data); 
        
    }
    
    public function testgetLiternal()
    {
       $config = new Worker();
       $this->assertEquals('LaterJob\Config\Worker::STATE_START',$config->getLiteral(1));
       $this->assertEquals('LaterJob\Config\Worker::STATE_FINISH',$config->getLiteral(2));
       $this->assertEquals('LaterJob\Config\Worker::STATE_ERROR',$config->getLiteral(3));
    }
    
    
}
/* End of File */