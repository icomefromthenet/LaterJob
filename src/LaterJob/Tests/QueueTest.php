<?php
namespace LaterJob\Tests;

use LaterJob\Queue;
use LaterJob\Event\QueueEventsMap;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for Queue API object
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueTest extends PHPUnit_Framework_TestCase
{
    
    public function testQueueBootStrap()
    {
        $uuid           = $this->getMockBuilder('LaterJob\UUID')->disableOriginalConstructor()->getMock();
        $logger         = $this->getMock('LaterJob\Log\LogInterface');
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');  
        $config_loder   = $this->getMock('LaterJob\Loader\LoaderInterface');
        $model_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        $event_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        
        $config_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $model_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $event_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        
        $options = array();
        
        $queue          = new Queue($mock_event,$logger,$options,$uuid,$config_loder,$model_loder,$event_loder);
        
        $this->assertEquals($mock_event,$queue->getDispatcher());
        $this->assertEquals($logger,$queue->getLogger());
        $this->assertEquals($uuid,$queue->getUUID());
        $this->assertEquals($queue['options'],$options);
    }
    
    
    public function testSendJob()
    {
        $uuid           = $this->getMockBuilder('LaterJob\UUID')->disableOriginalConstructor()->getMock();
        
        $uuid->expects($this->once())
             ->method('v3')
             ->will($this->returnValue('a73491a6-ed50-3c17-8e0f-d7279e7a00d9'));
             
        $uuid->expects($this->once())
             ->method('v4')
             ->will($this->returnValue('85acf452-4e61-3a2f-b1b9-4486f24edeb6'));     
        
        
        $logger         = $this->getMock('LaterJob\Log\LogInterface');
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');  
        $config_loder   = $this->getMock('LaterJob\Loader\LoaderInterface');
        $model_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        $event_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        
        
        $mock_event->expects($this->once())
                    ->method('dispatch')
                    ->with($this->equalTo(QueueEventsMap::QUEUE_REC),$this->isInstanceOf('LaterJob\Event\QueueReceiveEvent'));
        
        $config_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $model_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $event_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        
        $options = array();
        
        $queue          = new Queue($mock_event,$logger,$options,$uuid,$config_loder,$model_loder,$event_loder);
        
        # params for the send operation
        $now = new DateTime();
        $job_data = array('options' => 'a');
        $retry_count = 5;
        
        # setup queue options
        $queue_options = $this->getMock('LaterJob\Config\Queue');
        $queue_options->expects($this->once())
                      ->method('getMaxRetry')
                      ->will($this->returnValue($retry_count));
        $queue['config.queue'] = $queue_options;      
        
        # call send              
        $queue->send($now,$job_data);
        
    }
    
    public function testQuery()
    {
        $uuid           = $this->getMockBuilder('LaterJob\UUID')->disableOriginalConstructor()->getMock();
        $logger         = $this->getMock('LaterJob\Log\LogInterface');
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');  
        $config_loder   = $this->getMock('LaterJob\Loader\LoaderInterface');
        $model_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        $event_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        
        $config_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $model_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $event_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        
        $options = array();
        
        $queue          = new Queue($mock_event,$logger,$options,$uuid,$config_loder,$model_loder,$event_loder);
        
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_LIST),$this->isInstanceOf('LaterJob\Event\QueueListEvent'));
        
        $before = new DateTime();
        $after  = new DateTime();
        $order  = 'ASC';
        $limt   = 10;
        $offset = 20;
        $state  = 1; 
        
        $queue->query($offset,$limt,$state,$order,$before,$after);
        
    }
    
    public function testRemove()
    {
        $uuid           = $this->getMockBuilder('LaterJob\UUID')->disableOriginalConstructor()->getMock();
        $logger         = $this->getMock('LaterJob\Log\LogInterface');
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');  
        $config_loder   = $this->getMock('LaterJob\Loader\LoaderInterface');
        $model_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        $event_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        
        $config_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $model_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $event_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        
        $options = array();
        
        $queue          = new Queue($mock_event,$logger,$options,$uuid,$config_loder,$model_loder,$event_loder);
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_REMOVE),$this->isInstanceOf('LaterJob\Event\QueueRemoveEvent'));
        
        $queue->remove('a73491a6-ed50-3c17-8e0f-d7279e7a00d9',new DateTime());
        
    }
    
    
    public function testPurge()
    {
        $uuid           = $this->getMockBuilder('LaterJob\UUID')->disableOriginalConstructor()->getMock();
        $logger         = $this->getMock('LaterJob\Log\LogInterface');
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');  
        $config_loder   = $this->getMock('LaterJob\Loader\LoaderInterface');
        $model_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        $event_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        
        $config_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $model_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $event_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        
        $options = array();
        
        $queue          = new Queue($mock_event,$logger,$options,$uuid,$config_loder,$model_loder,$event_loder);
        
        $mock_event->expects($this->once())
                   ->method('dispatch')
                   ->with($this->equalTo(QueueEventsMap::QUEUE_PURGE),$this->isInstanceOf('LaterJob\Event\QueuePurgeEvent'));
        
        $queue->purge(new DateTime());
        
    }


    public function testWorker()
    {
        $uuid           = $this->getMockBuilder('LaterJob\UUID')->disableOriginalConstructor()->getMock();
        $logger         = $this->getMock('LaterJob\Log\LogInterface');
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');  
        $config_loder   = $this->getMock('LaterJob\Loader\LoaderInterface');
        $model_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        $event_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        
          
        $uuid->expects($this->once())
             ->method('v3')
             ->will($this->returnValue('a73491a6-ed50-3c17-8e0f-d7279e7a00d9'));
             
        $uuid->expects($this->once())
             ->method('v4')
             ->will($this->returnValue('85acf452-4e61-3a2f-b1b9-4486f24edeb6'));     
        
        $config_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $model_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $event_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        
        $options = array();
        
        $queue          = new Queue($mock_event,$logger,$options,$uuid,$config_loder,$model_loder,$event_loder);
        
        $queue['config.worker'] = $this->getMock('LaterJob\Config\Worker');
        $queue['config.worker']->expects($this->once())
                               ->method('getWorkerName')
                               ->will($this->returnValue('myworker'));
        
        $queue['config.queue']  = $this->getMock('LaterJob\Config\Queue');
        
        $worker = $queue->worker();
        
        $this->assertInstanceOf('LaterJob\Worker',$worker);
        
    }
    
    
    public function testReturnsActivityInterface()
    {
        $uuid           = $this->getMockBuilder('LaterJob\UUID')->disableOriginalConstructor()->getMock();
        $logger         = $this->getMock('LaterJob\Log\LogInterface');
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');  
        $config_loder   = $this->getMock('LaterJob\Loader\LoaderInterface');
        $model_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        $event_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        
        $config_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $model_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $event_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        
        $options = array();
        
        $queue          = new Queue($mock_event,$logger,$options,$uuid,$config_loder,$model_loder,$event_loder);
        
        $this->assertInstanceOf('LaterJob\Activity',$queue->activity());
    
    }
    
    
    public function testSchedule()
    {
        $uuid           = $this->getMockBuilder('LaterJob\UUID')->disableOriginalConstructor()->getMock();
        $logger         = $this->getMock('LaterJob\Log\LogInterface');
        $mock_event     = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');  
        $config_loder   = $this->getMock('LaterJob\Loader\LoaderInterface');
        $model_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        $event_loder    = $this->getMock('LaterJob\Loader\LoaderInterface');
        
        $config_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $model_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        $event_loder->expects($this->once())
                     ->method('boot')
                     ->with($this->isInstanceOf('Pimple'));
        
        
        $mock_worker_config = $this->getMock('LaterJob\Config\Worker');
        
        $mock_worker_config->expects($this->once())
                            ->method('getCronDefinition')
                            ->will($this->returnValue('*/20 * * * *'));
        
        $options = array();
        
        $queue          = new Queue($mock_event,$logger,$options,$uuid,$config_loder,$model_loder,$event_loder);
        
        $queue['config.worker'] = $mock_worker_config;
        
        $next = $queue->schedule(new DateTime(), 20);
        
        $this->assertEquals(20,count($next));
        
    }
    
}
/* End of File */