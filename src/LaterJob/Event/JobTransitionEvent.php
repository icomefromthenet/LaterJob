<?php
namespace LaterJob\Event;

use LaterJob\Job;
use LaterJob\Model\Transition\Transition;
use Symfony\Component\EventDispatcher\Event;

/**
  *  Event that occurs when job tansitions to another state
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class JobTransitionEvent extends Event
{
    /**
      *  @var LaterJob\Job 
      */
    protected $job;

    /**
      *  @var  LaterJob\Model\Transition\Transition
      */
    protected $transition;

    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param LaterJob\Job $job
      *  @param LaterJob\Model\Transition\Transition $trans
      */
    public function __construct(Job $job, Transition $trans)
    {
        $this->job     = $job;
        $this->transition = $trans;
    }

   /**
      *  Return the job this event relates
      *
      *  @access public
      *  @return LaterJob\Job
      */
    public function getJob()
    {
        return $this->job;    
    }
    
    /**
      *  Return the transition to take place
      *
      *  @access public
      *  @return LaterJob\Model\Transition\Transition
      */    
    public function getTransition()
    {
        return $this->transition;    
    }
    
}

/* End of File */