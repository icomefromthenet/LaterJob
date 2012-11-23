<?php
namespace LaterJob\Event;

use LaterJob\Worker;
use LaterJob\Model\Activity\Transition;
use Symfony\Component\EventDispatcher\Event;

/**
  *  Event extension For Worker Transitions events
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class WorkerTransitionEvent extends Event
{
    
    /**
      *  @var LaterJob\Worker 
      */
    protected $worker;
    
    /**
      *  @var LaterJob\Model\Activity\Transition that taken place 
      */
    protected $transition;
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param LaterJob\Worker $worker
      *  @param LaterJob\Model\Activity\Transition $trans
      */
    public function __construct(Worker $worker, Transition $trans)
    {
        $this->worker     = $worker;
        $this->transition = $trans;
    }
    
    
    /**
      *  Return the worker this event relates
      *
      *  @access public
      *  @return LaterJob\Worker
      */
    public function getWorker()
    {
        return $this->worker;    
    }
    
    /**
      *  Return the transition to take place
      *
      *  @access public
      *  @return LaterJob\Model\Activity\Transition
      */    
    public function getTransition()
    {
        return $this->transition;    
    }
    
}

/* End of File */