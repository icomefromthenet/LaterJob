<?php
namespace LaterJob\Tests\Config;

use LaterJob\Config\DbMeta;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for DbMeta Config Component
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class DbMetaTest extends  PHPUnit_Framework_TestCase
{
    
    public function testMetaConfigParse()
    {
        
        $transition_table = 't_table';
        $queue_table      = 'q_table';
        $monitor_table    = 'm_table';
        
        $config = new DbMeta();
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
        $config = new DbMeta();
        $config->parse(array('db' => array())); 
         
        $this->assertEquals('later_job_transition',$config->getTransitionTableName());
        $this->assertEquals('later_job_queue',$config->getQueueTableName());
        $this->assertEquals('later_job_monitor',$config->getMonitorTableName()); 
         
    }
    
    
}
/* End of File */