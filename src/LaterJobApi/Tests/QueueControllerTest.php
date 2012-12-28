<?php
namespace LaterJobApi\Tests;



class QueueControllerTest extends TestsWithFixture
{
    
    public function createApplication()
    {
        $_SERVER["APP_ENVIRONMENT"] = "development";
        $app = require __DIR__.'/../app.php';
        $app['exception_handler']->disable();
        $app['session.test'] = true;
        
        return $app;
    }
    
    
    public function testFetchSingleJob()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs/71be5b87-4d3f-3a90-8cb8-68bd96f9e41b');

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $job     = $results->result[0];
        
        $this->assertEquals('71be5b87-4d3f-3a90-8cb8-68bd96f9e41b',$job->jobId);
            
    }
    
    public function testFetchSingleJobMissing()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs/dae45ce0-48d1-11e2-bcfd-0800200c9a66');

        # request returned 200 ok
        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
            
    }
    
    
    public function testDeleteSingleJob()
    {
         $client = $this->createClient();
        $crawler = $client->request('DELETE', '/queue/jobs/71b55e88-7728-3e6c-ac70-4b7760cf3c48');

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        
        $this->assertEquals(1,$results->result);
        
    }
    
    public function testDeleteSingleJobNotFound()
    {
        $client = $this->createClient();
        $crawler = $client->request('DELETE', '/queue/jobs/dae45ce0-48d1-11e2-bcfd-0800200c9a66');

        # request returned 200 ok
        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
        
    }
    
    public function testPurgeWithBeforeBadStamp()
    {
        $client = $this->createClient();
        $crawler = $client->request('DELETE', '/queue/jobs',array('before' =>'2012'));

        # request returned 200 ok
        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[before] This value is not a valid datetime.',$results->msg);
        
    }
    
    
    public function testPurge()
    {
        $client = $this->createClient();
        $crawler = $client->request('DELETE', '/queue/jobs',array('before' =>'2012-12-18 13:05:00'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        
        # only purge finished job of which are none in fixture.
        $this->assertEquals(0,$results->result);
        
    }
    
    
    public function testNoParams()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs');

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
        $this->assertEquals(500,count($results->result));
        $this->assertEquals(true,$results->msg);
    }
    
    
    public function testWithNegativeLimitParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('limit' => -1));

        # request returned 200 ok
        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[limit] This value should be 1 or more.',$results->msg);
        
    }
    
    
    public function testWithAboveMaxLimitParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('limit' => 10000000));

        # request returned 200 ok
        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[limit] This value should be 500 or less.',$results->msg);
        
    }
    
    public function testWithNegativeOffsetParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('offset' => -1));

        # request returned 200 ok
        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[offset] This value should be 0 or more.',$results->msg);
        
    }
    
    public function testWithAboveMaxOffsetParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('offset' =>9999999999999999999));

        # request returned 200 ok
        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[offset] This value should be 2147483647 or less.',$results->msg);
        
    }
    
    
    public function testWithBadOrderChoice()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('order' =>'none'));

        # request returned 200 ok
        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[order] The value you selected is not a valid choice.',$results->msg);
        
    }
    
    
    public function testWithBeforeBadStamp()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('before' =>'2012'));

        # request returned 200 ok
        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[before] This value is not a valid datetime.',$results->msg);
        
    }
    
    public function testWithAfterBadStamp()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('after' =>'2012'));

        # request returned 200 ok
        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[after] This value is not a valid datetime.',$results->msg);
        
    }
    
    
    public function testQueryWithOffsetAndLimit()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('offset' =>5,'limit' => 10));

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
        $crawler = $client->request('GET', '/queue/jobs',array('offset' =>5,'limit' => 10,'order' => 'desc'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        # only 95 as 100 rows in db with offset 5
        $this->assertEquals(10,count($results->result));
        $this->assertEquals('8394fa82-ef63-36b3-a9cd-8b9dde7a10b4',$results->result[0]->jobId);
        
    }
    
    
    public function testQueryWithOffsetAndLimitAndDescOrderBeforeANDAfter()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/jobs',array('offset' =>0,'limit' => 100,'order' => 'desc','before' => '2012-12-19 05:39:00', 'after' => '2012-12-19 05:32:00'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        
        # date range constrains result to 14 rows - 5 offset =9 
        $this->assertEquals(8,count($results->result));
        $this->assertEquals('cf79e521-401d-37b8-acd9-dcb4edb5fa51',$results->result[0]->jobId);
        $this->assertEquals('71b55e88-7728-3e6c-ac70-4b7760cf3c48',$results->result[6]->jobId);
        
    } 
    
   
   
   
}

/* End of File */