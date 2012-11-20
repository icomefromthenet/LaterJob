<?php
namespace LaterJob\Tests\Config;

use LaterJob\Config\Queue;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for Queue Config Component
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueTest extends  PHPUnit_Framework_TestCase
{
    
    public function testQueueConfigParse()
    {
        $config = new Queue();

        $data = array('queue' => array(
            'mean_service_time' => (60*60*1),
            'max_retry'         => 4,
            'retry_timer'       => (60*60*1)
            
        ));
        
        $config->parse($data);        
        
        $this->assertEquals($data['queue']['mean_service_time'],$config->getMeanServiceTime());
        $this->assertEquals($data['queue']['max_retry'],$config->getMaxRetry());
        $this->assertEquals($data['queue']['retry_timer'],$config->getRetryTimer());
        
         
    }
    
    
    public function testQueueDefaults()
    {
        $config = new Queue();

        $data = array('queue' => array(
            'mean_service_time' => (60*60*1),
            'max_retry'         => 1,
            'retry_timer'       => (60*60*1),
            
        ));
        
        $config->parse($data);        
        
        $this->assertEquals($data['queue']['mean_service_time'],$config->getMeanServiceTime());
        $this->assertEquals($data['queue']['max_retry'],$config->getMaxRetry());
        $this->assertEquals($data['queue']['retry_timer'],$config->getRetryTimer());
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage  Invalid configuration for path "queue.max_retry": Max Job Retry must an integer with a value of 0 or more
      */
    public function testMaxRetryNotInteger()
    {
        $config = new Queue();

        $data = array('queue' => array(
            'mean_service_time' => (60*60*1),
            'max_retry'         => 'aaaa',
            
        ));
        
        $config->parse($data);        
        
        
    }
    
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage  Invalid configuration for path "queue.mean_service_time": Mean Job Service Time must be an integer with a value greater than 0
      */
    public function testMaxServiceTimeNotInteger()
    {
        $config = new Queue();

        $data = array('queue' => array(
            'mean_service_time' => 'sdsds',
            'max_retry'         => 5,
            
        ));
        
        $config->parse($data);        
        
        
    }
    
     /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage  The child node "retry_timer" at path "queue" must be configured.
      */
    public function testRetryTimerNotSet()
    {
        $config = new Queue();

        $data = array('queue' => array(
            'mean_service_time' => 60,
            'max_retry'         => 5,
            
        ));
        
        $config->parse($data);        
        
        
    }
    
    /**
      *  @expectedException LaterJob\Exception
      *  @expectedExceptionMessage  Invalid configuration for path "queue.retry_timer": Job Retry Timmer must be an integer with a value greater than 0
      */
    public function testRetryTimerNotInteger()
    {
        $config = new Queue();

        $data = array('queue' => array(
            'mean_service_time' => 60,
            'max_retry'         => 5,
            'retry_timer'       => 'aaa'
            
        ));
        
        $config->parse($data);        
        
        
    }
    
    
    public function testgetLiternal()
    {
       $config = new Queue();
 
       $this->assertEquals('LaterJob\Config\Queue::STATE_ADD',$config->getLiteral(1));
       $this->assertEquals('LaterJob\Config\Queue::STATE_START',$config->getLiteral(2));
       $this->assertEquals('LaterJob\Config\Queue::STATE_FINISH',$config->getLiteral(3));
       $this->assertEquals('LaterJob\Config\Queue::STATE_ERROR',$config->getLiteral(4));
       $this->assertEquals('LaterJob\Config\Queue::STATE_FAIL',$config->getLiteral(5));
    }
    
    
}
/* End of File */