<?php
namespace LaterJobApi\Tests;



class MonitorControllerTest extends TestsWithFixture
{
    
    public function createApplication()
    {
        GLOBAL $app;
        //$app['exception_handler']->disable();
        $app['session.test'] = true;
        
        return $app;
    }
    
    
    
    public function testNoParams()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring');

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        # check if response set to json
        $client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(22,count($results->result));
        $this->assertEquals(true,$results->msg);
    }
    
    
    public function testWithNegativeLimitParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('limit' => -1));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[limit] This value should be 1 or more.',$results->msg);
        
    }
    
    
    public function testWithAboveMaxLimitParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('limit' => 10000000));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[limit] This value should be 1000 or less.',$results->msg);
        
    }
    
    public function testWithNegativeOffsetParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('offset' => -1));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[offset] This value should be 0 or more.',$results->msg);
        
    }
    
    public function testWithAboveMaxOffsetParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('offset' =>9999999999999999999));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[offset] This value should be '.PHP_INT_MAX.' or less.',$results->msg);
        
    }
    
    
    public function testWithBadOrderChoice()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('order' =>'none'));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[order] The value you selected is not a valid choice.',$results->msg);
        
    }
    
    
    public function testWithBeforeBadStamp()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('before' =>'2012'));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[before] This value is not a valid datetime.',$results->msg);
        
    }
    
    public function testWithAfterBadStamp()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('after' =>'2012'));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[after] This value is not a valid datetime.',$results->msg);
        
    }
    
    
    public function testQueryWithOffsetAndLimit()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('offset' =>5,'limit' => 10));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        # only 95 as 100 rows in db with offset 5
        $this->assertEquals(10,count($results->result));
        
    }
    
    
    public function testQueryWithOffsetAndLimitAndDescOrder()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('offset' =>5,'limit' => 10,'order' => 'desc'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        # only 95 as 100 rows in db with offset 5
        $this->assertEquals(10,count($results->result));
        $this->assertEquals(17,$results->result[0]->monitorId);
        
    }
    
    
    public function testQueryWithOffsetAndLimitAndDescOrderBeforeANDAfter()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/monitoring',array('offset' =>5,'limit' => 100,'order' => 'desc','before' => '2012-12-19 03:00:00', 'after' => '2012-12-18 16:00:00'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        
        # date range constrains result to 14 rows - 5 offset =9 
        $this->assertEquals(7,count($results->result));
        $this->assertEquals(9,$results->result[0]->monitorId);
        $this->assertEquals(3,$results->result[6]->monitorId);
        
    } 
    
   
}

/* End of File */