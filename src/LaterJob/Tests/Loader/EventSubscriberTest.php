<?php
namespace LaterJob\Tests\Loader;

use Pimple;
use LaterJob\Loader\EventSubscriber;
use PHPUnit_Framework_TestCase;

/**
  *  Test config loading. 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class EventSubscriberTest extends PHPUnit_Framework_TestCase
{
    
    
    public function testSubscribeLogHandlers()
    {
        $loader   = new EventSubscriber();
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        
        $event->expects($this->once())
              ->method('addSubscriber')
              ->with($this->isInstanceOf('LaterJob\Log\LogSubscriber'));
        
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        
        $loader->subscribeLogHandlers($event,$logger);
        
    }
    
    
    public function testSubscribeStorageHandlers()
    {
        $loader   = new EventSubscriber();
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        
        $event->expects($this->at(0))
              ->method('addSubscriber')
              ->with($this->isInstanceOf('LaterJob\Model\Queue\QueueSubscriber'));
              
        $event->expects($this->at(1))
              ->method('addSubscriber')
              ->with($this->isInstanceOf('LaterJob\Model\Queue\JobSubscriber'));
        
        $table = $this->getMockBuilder('DBALGateway\Table\AbstractTable')->disableOriginalConstructor()->getMock();
        
        $loader->subscribeStorageHandlers($event,$table);
        
    }
    
    
    public function testSubscribeTransitionHandlers()
    {
        $loader   = new EventSubscriber();
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        
        $event->expects($this->at(0))
              ->method('addSubscriber')
              ->with($this->isInstanceOf('LaterJob\Model\Activity\JobSubscriber'));
              
        $event->expects($this->at(1))
              ->method('addSubscriber')
              ->with($this->isInstanceOf('LaterJob\Model\Activity\WorkerSubscriber'));
              
         $event->expects($this->at(2))
              ->method('addSubscriber')
              ->with($this->isInstanceOf('LaterJob\Model\Activity\QueueSubscriber'));
        
        $table = $this->getMockBuilder('DBALGateway\Table\AbstractTable')->disableOriginalConstructor()->getMock();
        
        $loader->subscribeTransitionHandlers($event,$table);
        
        
        
    }
    
    public function testMonitorHandlers()
    {
        $loader  = new EventSubscriber();
        
    }
    
    public function testPimpleBoot()
    {
        $pimple                     = new Pimple();
        $pimple['dispatcher']       = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $pimple['logger']           = $this->getMock('Psr\Log\LoggerInterface');
        $pimple['model.queue']      = $this->getMockBuilder('DBALGateway\Table\AbstractTable')->disableOriginalConstructor()->getMock();
        $pimple['model.transition'] = $this->getMockBuilder('DBALGateway\Table\AbstractTable')->disableOriginalConstructor()->getMock();
        $pimple['model.monitor']    = $this->getMockBuilder('DBALGateway\Table\AbstractTable')->disableOriginalConstructor()->getMock();
        
        $loader  = new EventSubscriber();
        $loader->boot($pimple);
        
    }
}
/* End of File */