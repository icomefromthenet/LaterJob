<?php
namespace LaterJob\Tests\Config;

use LaterJob\Config\DbMetaConfig;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for DbMetaConfig Component
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class DbMetaConfigTest extends  PHPUnit_Framework_TestCase
{
    
    public function testMetaConfigParse()
    {
        
        $transition_table = 't_table';
        $queue_table      = 'q_table';
        $monitor_table    = 'm_table';
        
        $config = new DbMetaConfig();
        $names = array('db' => array(
            'transition_table' => $transition_table,
            'queue_table'      => $queue_table,
            'monitor_table'    => $monitor_table
        ));
        
        $config->parse($names);
        
        $this->assertEquals($transition_table,$config->getTransitionTableName());
        $this->assertEquals($queue_table,$config->getQueueTableName());
        $this->assertEquals($monitor_table,$config->getMonitorTableName());
        
        
        # test for default values
        $config = new DbMetaConfig();
        $config->parse(array('db' => array())); 
         
        $this->assertEquals('later_job_transition',$config->getTransitionTableName());
        $this->assertEquals('later_job_queue',$config->getQueueTableName());
        $this->assertEquals('later_job_monitor',$config->getMonitorTableName()); 
         
    }
    
    
}
/* End of File */