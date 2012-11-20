<?php
namespace LaterJob\Tests\Log;

use LaterJob\Log\MonologBridge;
use PHPUnit_Framework_TestCase;
use DateTime;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

/**
  *  Unit Tests for Monolog Bridge
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class MonologBridgeTest extends PHPUnit_Framework_TestCase
{
    
    
    public function testBridge()
    {
        # setup monolog
        $mono = new Logger('test');
        $handler = new TestHandler();
        $mono->pushHandler($handler);
        
        $bridge = new MonologBridge($mono);
        
        $this->assertInstanceOf('LaterJob\Log\LogInterface',$bridge);
        
        # setup some messages
        $debug  = 'this is a debug message';
        $info   = 'this is an info message';
        $notice = 'this is an notice message';
        $warn   = 'this is an warn message';
        $err    = 'this is an err message';
        $crit   = 'this is an crit message';
        $alert  = 'this is an alert message';
        $emerg  = 'this is an emerg message';
        
        # test LoggerInterface

        $bridge->debug($debug);
        $rec = $handler->getRecords();
        $this->assertEquals($debug,$rec[0]['message']);
        
        $bridge->info($info);
        $rec = $handler->getRecords();
        $this->assertEquals($info,$rec[1]['message']);
        
        $bridge->notice($info);
        $rec = $handler->getRecords();
        $this->assertEquals($info,$rec[2]['message']);
        
        $bridge->warn($info);
        $rec = $handler->getRecords();
        $this->assertEquals($info,$rec[3]['message']);
    
        $bridge->err($info);
        $rec = $handler->getRecords();
        $this->assertEquals($info,$rec[4]['message']);
        
        
        $bridge->crit($info);
        $rec = $handler->getRecords();
        $this->assertEquals($info,$rec[5]['message']);
        
        $bridge->alert($info);
        $rec = $handler->getRecords();
        $this->assertEquals($info,$rec[6]['message']);
    
        $bridge->emerg($info);
        $rec = $handler->getRecords();
        $this->assertEquals($info,$rec[7]['message']);
    
    }
    
    
    
}
/* End of File */