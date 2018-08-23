<?php
namespace LaterJob\Tests\Model\Activity;

use LaterJob\Model\Activity\Transition;
use LaterJob\Model\Activity\TransitionBuilder;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
  *  Unit Tests for Model Transition Builder and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class BuilderTest extends TestCase
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
        $process_handle = 'abcd-efgh-ijkl-mnop';
        
        $transition->setTransitionId($transition_id);
        $transition->setJob($job_id);
        $transition->setWorker($worker_id);
        $transition->setOccured($occured);
        $transition->setMessage($message);
        $transition->setState($state_id);
        $transition->setProcessHandle($process_handle);
        
        $this->assertEquals($transition_id,$transition->getTransitionId());
        $this->assertEquals($message,$transition->getMessage());
        $this->assertEquals($state_id,$transition->getState());
        $this->assertEquals($worker_id,$transition->getWorker());
        $this->assertEquals($job_id,$transition->getJob());
        $this->assertEquals($occured,$transition->getOccured());
        $this->assertEquals($process_handle,$transition->getProcessHandle());
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
        $process_handle = 'efgh-ijhg-kjhf-kjjda';
        
        $data = array(
            'transition_id'  => $transition_id,
            'worker_id'      => $worker_id,
            'job_id'         => $job_id,
            'state_id'       => $state_id,
            'dte_occured'    => $occured,
            'transition_msg' => $message,
            'process_handle' => $process_handle,
        );
        
        
        $transition = $builder->build($data);
        
        $this->assertEquals($transition_id,$transition->getTransitionId());
        $this->assertEquals($message,$transition->getMessage());
        $this->assertEquals($state_id,$transition->getState());
        $this->assertEquals($worker_id,$transition->getWorker());
        $this->assertEquals($job_id,$transition->getJob());
        $this->assertEquals($occured,$transition->getOccured());
        $this->assertEquals($process_handle,$transition->getProcessHandle());
        
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
        $process_handle = 'efgh-ijhg-kjhf-kjjda';
         
        $transition->setTransitionId($transition_id);
        $transition->setJob($job_id);
        $transition->setWorker($worker_id);
        $transition->setOccured($occured);
        $transition->setMessage($message);
        $transition->setState($state_id);
        $transition->setProcessHandle($process_handle);
        
         $data = array(
            'transition_id'  => $transition_id,
            'worker_id'      => $worker_id,
            'job_id'         => $job_id,
            'state_id'       => $state_id,
            'dte_occured'    => $occured,
            'transition_msg' => $message,
            'process_handle' => $process_handle
        );
         
        $converted = $builder->demolish($transition);
        
        $this->assertEquals($converted,$data);
        
    }
}
/* End of File */