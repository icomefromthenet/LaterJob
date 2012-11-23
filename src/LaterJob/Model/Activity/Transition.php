<?php
namespace LaterJob\Model\Activity;

use DateTime;

/**
  *  Entity to represent a single transition
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Transition
{
    /**
      *  @var integer the db row id  
      */
    protected $transition_id;
    
    /**
      *  @var string the UUID of the worker if this relates to a worker 
      */
    protected $worker_id;
    
    /**
      *  @var string the UUID of the job if this realates to a job 
      */
    protected $job_id;
    
    /**
      *  @var integer the state_id  of the transition
      */
    protected $state_id;
    
    /**
      *  @var DateTime when the transition occured 
      */
    protected $occured;
    
    /**
      *  @var string a transition message 
      */
    protected $message;
    
    /**
      *  @var string id of the processer (worker_id) 
      */
    protected $process_handle;
    
    
    public function getProcessHandle()
    {
        return $this->process_handle;
    }
    
    
    public function setProcessHandle($handle)
    {
        $this->process_handle = $handle;
    }
    
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function setMessage($msg)
    {
        $this->message = $msg;
    }
    
    
    public function getOccured()
    {
        return $this->occured;
    }
    
    public function setOccured(DateTime $occured)
    {
        $this->occured = $occured;
    }
    
    public function getState()
    {
        return $this->state_id;
    }
    
    public function setState($id)
    {
        return $this->state_id = $id;
    }
    
    public function getJob()
    {
        return $this->job_id;
    }
    
    public function setJob($job)
    {
        $this->job_id = $job;
    }
    
    public function getWorker()
    {
        return $this->worker_id;
    }
    
    public function setWorker($id)
    {
        $this->worker_id = $id;
    }
    
    public function getTransitionId()
    {
        return $this->transition_id;
    }
    
    public function setTransitionId($id)
    {
        $this->transition_id = $id;
    }
    
}
/* End of File */