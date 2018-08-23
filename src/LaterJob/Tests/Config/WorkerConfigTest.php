<?php
namespace LaterJob\Tests\Config;

use LaterJob\Config\WorkerConfig;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
  *  Unit Tests for WorkerConfig Config Component
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class WorkerConfigTest extends  TestCase
{
    
    public function testQueueConfigParse()
    {
        $config = new WorkerConfig();

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
        $config = new WorkerConfig();

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
        $config = new WorkerConfig();

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
        $config = new WorkerConfig();

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
        $config = new WorkerConfig();

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
    public function testQueueConfigEmptyWorkerConfigName()
    {
        $config = new WorkerConfig();

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
       $config = new WorkerConfig();
       $this->assertEquals('LaterJob\Config\WorkerConfig::STATE_START',$config->getLiteral(1));
       $this->assertEquals('LaterJob\Config\WorkerConfig::STATE_FINISH',$config->getLiteral(2));
       $this->assertEquals('LaterJob\Config\WorkerConfig::STATE_ERROR',$config->getLiteral(3));
    }
    
    
}
/* End of File */