<?php
namespace LaterJob\Tests\Loader;

use Pimple;
use LaterJob\Loader\ModelLoader;
use PHPUnit_Framework_TestCase;

/**
  *  Test config loading. 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class ModelLoaderTest extends PHPUnit_Framework_TestCase
{
    
    public function testTransitionModelLoader()
    {
        $loader   = new ModelLoader(); 
        $doctrine = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $meta     = $this->getMockBuilder('DBALGateway\Metadata\Table')->disableOriginalConstructor()->getMock();
        
        $meta->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('table'));
        
        $gateway = $loader->bootTransitionModel('table',$doctrine,$event,$meta);
        
        $this->assertInstanceOf('LaterJob\Model\Transition\TransitionGateway',$gateway);
    }
    
    public function testStorageModelLoader()
    {
        $loader   = new ModelLoader(); 
        $doctrine = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $meta     = $this->getMockBuilder('DBALGateway\Metadata\Table')->disableOriginalConstructor()->getMock();
        
        $meta->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('table'));
        
        $gateway = $loader->bootStorageModel('table',$doctrine,$event,$meta);
        
        $this->assertInstanceOf('LaterJob\Model\Queue\StorageGateway',$gateway);
        
    }
    
    public function testMonitorModelLoader()
    {
        $loader   = new ModelLoader(); 
        $doctrine = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $meta     = $this->getMockBuilder('DBALGateway\Metadata\Table')->disableOriginalConstructor()->getMock();
        
        $meta->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('table'));
        
        $gateway = $loader->bootMonitorModel('table',$doctrine,$event,$meta);
        
        $this->assertInstanceOf('LaterJob\Model\Monitor\StatsGateway',$gateway);
        
    }
    
    
    
    public function testPimpleBoot()
    {
        $loader                    = new ModelLoader(); 
        $pimple                    = new Pimple();
        $pimple['doctrine']        = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $pimple['dispatcher']      = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        
        $meta     = $this->getMockBuilder('DBALGateway\Metadata\Table')->disableOriginalConstructor()->getMock();
        
        $meta->expects($this->exactly(3))
             ->method('getName')
             ->will($this->returnValue('table'));
                
        $mocked_config             = $this->getMock('LaterJob\Config\DbMeta');
        
        $mocked_config->expects($this->once())
                      ->method('getTransitionTableName')
                      ->will($this->returnValue('table'));
        
        $mocked_config->expects($this->once())
                      ->method('getQueueTableName')
                      ->will($this->returnValue('table'));
                      
        $mocked_config->expects($this->once())
                      ->method('getMonitorTableName')
                      ->will($this->returnValue('table'));
                      
        $mocked_config->expects($this->once())
                      ->method('getTransitionTable')
                      ->will($this->returnValue($meta));
                      
        $mocked_config->expects($this->once())
                      ->method('getQueueTable')
                      ->will($this->returnValue($meta));
                      
        $mocked_config->expects($this->once())
                      ->method('getMonitorTable')
                      ->will($this->returnValue($meta));
                      
        
        $pimple['config.database'] = $mocked_config;
        
        $loader->boot($pimple);
        
        $this->assertInstanceOf('LaterJob\Model\Transition\TransitionGateway',$pimple['model.transition']);
        $this->assertInstanceOf('LaterJob\Model\Queue\StorageGateway',$pimple['model.queue']);
        $this->assertInstanceOf('LaterJob\Model\Monitor\StatsGateway',$pimple['model.monitor']);
        
    }
    
    
        
}
/* End of File */