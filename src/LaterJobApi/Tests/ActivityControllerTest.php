<?php
namespace LaterJobApi\Tests;


class ActivityControllerTest extends TestsWithFixture
{
    
    public function createApplication()
    {
        GLOBAL $app;
        
        //$app['exception_handler']->disable();
        $app['session.test'] = true;
        
        $app->boot();
        
        return $app;
    }
    
    
    
    public function testNoParams()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities');

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
        $this->assertEquals(100,count($results->result));
        $this->assertEquals(true,$results->msg);
    }
    
    
    public function testWithNegativeLimitParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities',array('limit' => -1));

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
        $crawler = $client->request('GET', '/queue/activities',array('limit' => 10000000));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[limit] This value should be 500 or less.',$results->msg);
        
    }
    
    public function testWithNegativeOffsetParam()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities',array('offset' => -1));

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
        $crawler = $client->request('GET', '/queue/activities',array('offset' =>9999999999999999999));

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
        $crawler = $client->request('GET', '/queue/activities',array('order' =>'none'));

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
        $crawler = $client->request('GET', '/queue/activities',array('before' =>'2012'));

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
        $crawler = $client->request('GET', '/queue/activities',array('after' =>'2012'));

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
        $crawler = $client->request('GET', '/queue/activities',array('offset' =>5,'limit' => 100));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        # only 95 as 100 rows in db with offset 5
        $this->assertEquals(95,count($results->result));
        
    }
    
    
    public function testQueryWithOffsetAndLimitAndDescOrder()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities',array('offset' =>5,'limit' => 100,'order' => 'desc'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        # only 95 as 100 rows in db with offset 5
        $this->assertEquals(95,count($results->result));
        $this->assertEquals(95,$results->result[0]->transitionId);
        
    }
    
    public function testQueryWithOffsetAndLimitAndDescOrderBeforeANDAfter()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities',array('offset' =>5,'limit' => 100,'order' => 'desc','before' => '2012-12-18 14:25:00', 'after' => '2012-12-18 14:14:00'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        
        # date range constrains result to 12 rows - 5 offset =7 
        $this->assertEquals(7,count($results->result));
        $this->assertEquals(80,$results->result[0]->transitionId);
        $this->assertEquals(74,$results->result[6]->transitionId);
        
    } 
    
    public function testQueryWithWorker() 
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities',array('offset' =>0, 'limit' => 100,'order' => 'desc', 'worker_id' =>'8c195538-2d1b-3bae-a372-3bdf2cb6d9d3'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        
        $this->assertEquals(1,count($results->result));
        
    }
    
    public function testQueryWithWorkerBadUUID() 
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities',array('offset' =>0, 'limit' => 100,'order' => 'desc', 'worker_id' =>'8c195538-2d1b-3bae-a372-3bdf2cb6d9d37667676766776'));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[worker_id] This is not a valid UUID.',$results->msg);
        
    }
    
    
    public function testQueryWithJob() 
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities',array('offset' =>0,'limit' => 100,'order' => 'desc','job_id' =>'14a0f5a8-876d-3d9d-be97-92dfa8525bcc'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        

        $this->assertEquals(1,count($results->result));
        
    }
    
    public function testQueryWithJobBadUUID() 
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/queue/activities',array('offset' =>0, 'limit' => 100,'order' => 'desc', 'job_id' =>'8c195538-2d1b-3bae-a372-3bdf2cb6d9d37667676766776'));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[job_id] This is not a valid UUID.',$results->msg);
        
    }
    
    public function testDeleteWithBadStamp()
    {
         $client = $this->createClient();
        $crawler = $client->request('DELETE', '/queue/activities',array('before' =>'2012'));

        # request returned 200 ok
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0,count($results->result));
        $this->assertEquals('[before] This value is not a valid datetime.',$results->msg);
        
    }
    
    
    public function testDelete()
    {
         $client = $this->createClient();
        $crawler = $client->request('DELETE', '/queue/activities',array('before' =>'2012-12-18 14:28:00'));

        # request returned 200 ok
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
        
        $results = json_decode($client->getResponse()->getContent());
        $this->assertEquals(88,$results->result);
                
    } 
    
}

/* End of File */