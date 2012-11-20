<?php
namespace LaterJob\Tests\Model\Transition;

use LaterJob\Model\Transition\Transition;
use LaterJob\Model\Transition\TransitionBuilder;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for Model Transition Builder and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class BuilderTest extends  PHPUnit_Framework_TestCase
{
    
    public function testEntityProperties()
    {
        $transition = new Transition();
        
        $transition_id = 2;
        $worker_id = 'efgh-ijhg-kjhf-kjjd';
        $job_id = 'abcd-efgh-ijkl-mnop';
        $state_id = 1;
        $occured = new DateTime();
        $message = 'a transition msg';
        
        $transition->setTransitionId($transition_id);
        $transition->setJob($job_id);
        $transition->setWorker($worker_id);
        $transition->setOccured($occured);
        $transition->setMessage($message);
        $transition->setState($state_id);
        
        $this->assertEquals($transition_id,$transition->getTransitionId());
        $this->assertEquals($message,$transition->getMessage());
        $this->assertEquals($state_id,$transition->getState());
        $this->assertEquals($worker_id,$transition->getWorker());
        $this->assertEquals($job_id,$transition->getJob());
        $this->assertEquals($occured,$transition->getOccured());
    }
    
    
    
    public function testEntityBuilderBuild()
    {
        $builder = new TransitionBuilder();
        
        $transition_id = 2;
        $worker_id = 'efgh-ijhg-kjhf-kjjd';
        $job_id = 'abcd-efgh-ijkl-mnop';
        $state_id = 1;
        $occured = new DateTime();
        $message = 'a transition msg';
        
        $data = array(
            'transition_id'  => $transition_id,
            'worker_id'      => $worker_id,
            'job_id'         => $job_id,
            'state_id'       => $state_id,
            'dte_occured'    => $occured,
            'transition_msg' => $message
        );
        
        
        $transition = $builder->build($data);
        
        $this->assertEquals($transition_id,$transition->getTransitionId());
        $this->assertEquals($message,$transition->getMessage());
        $this->assertEquals($state_id,$transition->getState());
        $this->assertEquals($worker_id,$transition->getWorker());
        $this->assertEquals($job_id,$transition->getJob());
        $this->assertEquals($occured,$transition->getOccured());
        
    }
    
    
    
    public function testEntityBuilderDemolish()
    {
        $transition = new Transition();
        $builder = new TransitionBuilder();
        
        $transition_id = 2;
        $worker_id = 'efgh-ijhg-kjhf-kjjd';
        $job_id = 'abcd-efgh-ijkl-mnop';
        $state_id = 1;
        $occured = new DateTime();
        $message = 'a transition msg';
        
        $transition->setTransitionId($transition_id);
        $transition->setJob($job_id);
        $transition->setWorker($worker_id);
        $transition->setOccured($occured);
        $transition->setMessage($message);
        $transition->setState($state_id);
        
         $data = array(
            'transition_id'  => $transition_id,
            'worker_id'      => $worker_id,
            'job_id'         => $job_id,
            'state_id'       => $state_id,
            'dte_occured'    => $occured,
            'transition_msg' => $message
        );
         
        $converted = $builder->demolish($transition);
        
        $this->assertEquals($converted,$data);
        
    }
}
/* End of File */