<?php
namespace LaterJob\Tests\Loader;

use Pimple\Container;
use LaterJob\Loader\EventSubscriber;
use PHPUnit\Framework\TestCase;

/**
  *  Test config loading. 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class EventSubscriberTest extends TestCase
{
    
    
    public function testSubscribeLogHandlers()
    {
        $loader   = new EventSubscriber();
        $event    = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
        $event->expects($this->once())
              ->method('addSubscriber')
              ->with($this->isInstanceOf('LaterJob\Log\LogSubscriber'));
        
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        
        $loader->subscribeLogHandlers($event,$logger);
        
    }
    
    
    public function testSubscribeStorageHandlers()
    {
        $loader   = new EventSubscriber();
        $event    = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
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
        $event    = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        
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
    
    
    
    public function testPimpleBoot()
    {
        $pimple                     = new Container();
        $pimple['dispatcher']       = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        $pimple['logger']           = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $pimple['model.queue']      = $this->getMockBuilder('DBALGateway\Table\AbstractTable')->disableOriginalConstructor()->getMock();
        $pimple['model.transition'] = $this->getMockBuilder('DBALGateway\Table\AbstractTable')->disableOriginalConstructor()->getMock();
        $pimple['model.monitor']    = $this->getMockBuilder('DBALGateway\Table\AbstractTable')->disableOriginalConstructor()->getMock();
        
        $loader  = new EventSubscriber();
        $loader->boot($pimple);
        
        $this->assertTrue(true);
        
    }
}
/* End of File */