<?php
namespace LaterJob\Tests\Loader;

use Pimple;
use LaterJob\Loader\ConfigLoader;
use LaterJob\Config\DbMetaConfig;
use LaterJob\Config\QueueConfig;
use LaterJob\Config\WorkerConfig;
use PHPUnit_Framework_TestCase;

/**
  *  Test config loading. 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class ConfigLoaderTest extends PHPUnit_Framework_TestCase
{
    
    
    public function testDatabaseConfigLoading()
    {
        $options =  array('db' => array(
            'transition_table' => 't_table',
            'queue_table'      => 'q_table',
            'monitor_table'    => 'm_table'
        ));
    
        $loader = new ConfigLoader(); 
        $this->assertInstanceOf('LaterJob\Config\DbMetaConfig',$loader->parseDatabaseOptions($options,new DBMetaConfig()));
    }
    
    public function testQueueConfigLoading()
    {
        $options = array('queue' => array(
            'mean_service_time' => (60*60*1),
            'max_retry'         => 4,
            'retry_timer'       => (60*60*1)
            
        ));
        
        $loader = new ConfigLoader(); 
        $this->assertInstanceOf('LaterJob\Config\QueueConfig',$loader->parseQueueOptions($options,new QueueConfig()));
        
    }
    
    public function testWorkerConfigLoading()
    {
        $options = array('worker' => array(
            'jobs_process'      => 4,
            'mean_runtime'      => (60*60*1),
            'cron_script'       => '* * * * *',
            'job_lock_timeout'  => (60*60*4),
            'worker_name'       => 'bobemail'
            
        ));
        
        $loader = new ConfigLoader(); 
        $this->assertInstanceOf('LaterJob\Config\WorkerConfig',$loader->parseWorkerOptions($options,new WorkerConfig()));
    }
    
    
    public function testPimpleBoot()
    {
        $pimple  = new Pimple();
        $loader  = new ConfigLoader(); 
        
        $pimple['options'] = array(
            'worker' => array(
                'jobs_process'      => 4,
                'mean_runtime'      => (60*60*1),
                'cron_script'       => '* * * * *',
                'job_lock_timeout'  => (60*60*4),
                'worker_name'       => 'bobemail'
            ),
            'queue' => array(
                'mean_service_time' => (60*60*1),
                'max_retry'         => 4,
                'retry_timer'       => (60*60*1)
            ),
            'db' => array(
                'transition_table' => 't_table',
                'queue_table'      => 'q_table',
                'monitor_table'    => 'm_table'
            )
        );
        
        $loader->boot($pimple);
        
        $this->assertInstanceOf('LaterJob\Config\DbMetaConfig',$pimple['config.database']);
        $this->assertInstanceOf('LaterJob\Config\QueueConfig',$pimple['config.queue']);
        $this->assertInstanceOf('LaterJob\Config\WorkerConfig',$pimple['config.worker']);
        
    }
    
    
        
}
/* End of File */