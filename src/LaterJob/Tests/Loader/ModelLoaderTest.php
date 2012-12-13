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
        $doctrine = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $loader   = new ModelLoader($doctrine); 
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $meta     = $this->getMockBuilder('DBALGateway\Metadata\Table')->disableOriginalConstructor()->getMock();
        
        $meta->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('table'));
        
        $gateway = $loader->bootTransitionModel('table',$event,$meta);
        
        $this->assertInstanceOf('LaterJob\Model\Activity\TransitionGateway',$gateway);
    }
    
    public function testStorageModelLoader()
    {
        $doctrine = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $loader   = new ModelLoader($doctrine); 
        
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $meta     = $this->getMockBuilder('DBALGateway\Metadata\Table')->disableOriginalConstructor()->getMock();
        
        $meta->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('table'));
        
        $gateway = $loader->bootStorageModel('table',$event,$meta);
        
        $this->assertInstanceOf('LaterJob\Model\Queue\StorageGateway',$gateway);
        
    }
    
    public function testMonitorModelLoader()
    {
        $doctrine = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $loader   = new ModelLoader($doctrine); 
        $event    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $meta     = $this->getMockBuilder('DBALGateway\Metadata\Table')->disableOriginalConstructor()->getMock();
        
        $meta->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('table'));
        
        $gateway = $loader->bootMonitorModel('table',$event,$meta);
        
        $this->assertInstanceOf('LaterJob\Model\Monitor\StatsGateway',$gateway);
        
    }
    
    
    
    public function testPimpleBoot()
    {
        $doctrine                  = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $loader                    = new ModelLoader($doctrine); 
        $pimple                    = new Pimple();
        $pimple['dispatcher']      = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        
        $meta                      = $this->getMockBuilder('DBALGateway\Metadata\Table')->disableOriginalConstructor()->getMock();
        
        $meta->expects($this->exactly(3))
             ->method('getName')
             ->will($this->returnValue('table'));
                
        $mocked_config             = $this->getMock('LaterJob\Config\DbMetaConfig');
        
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
        
        $this->assertInstanceOf('LaterJob\Model\Activity\TransitionGateway',$pimple['model.transition']);
        $this->assertInstanceOf('LaterJob\Model\Queue\StorageGateway',$pimple['model.queue']);
        $this->assertInstanceOf('LaterJob\Model\Monitor\StatsGateway',$pimple['model.monitor']);
        
    }
    
    
        
}
/* End of File */