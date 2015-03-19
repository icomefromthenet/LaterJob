<?php
namespace LaterJobApi\Tests;

use Silex\WebTestCase;

class ScheduleControllerTest extends WebTestCase
{
    
    public function createApplication()
    {
       GLOBAL $app;
        $app['exception_handler']->disable();
        $app['session.test'] = true;
        
        return $app;
    }
    
    
    public function testNoParams()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/schedule');

        # request returned 200 ok
        //$this->assertEquals(
          //  400,
            //$client->getResponse()->getStatusCode()
        //);
        
        # check if response set to json
        $client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        );
        

    }
    
    public function testWithNegativeIterations()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/schedule', array('now'=>'2012-12-18 22:33:44' ,'iterations'=>-100 ));
        
        
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );

        $result = json_decode($client->getResponse()->getContent());
        $this->assertEquals('[iterations] This value should be 1 or more.',$result->msg);
        $this->assertEquals(array(),$result->result);
    }
    
    public function testWithMoreThanMaxIterations()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/schedule', array('now'=>'2012-12-18 22:33:44' ,'iterations'=>10000 ));
        
        $result = json_decode($client->getResponse()->getContent());
        $this->assertEquals('[iterations] This value should be 100 or less.',$result->msg);
        $this->assertEquals(array(),$result->result);
        
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );

        
    }
    
    
    public function testWithBadDate()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/schedule', array('now'=>'2012-100-18 22:33:44' ,'iterations'=>5 ));
        
        $result = json_decode($client->getResponse()->getContent());
        $this->assertEquals('[now] This value is not a valid datetime.',$result->msg);
        $this->assertEquals(array(),$result->result);
        
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
    }
    
    public function testWithGoodParams()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/schedule', array('now'=>'2012-12-18 22:33:44' ,'iterations'=>5 ));
        
        $result = json_decode($client->getResponse()->getContent());
        $this->assertEquals('',$result->msg);
        $this->assertCount(5,$result->result);
        
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        
    }
    
    
}
/* End of File */