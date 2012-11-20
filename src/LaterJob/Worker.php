<?php
namespace LaterJob;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LaterJob\Event\WorkerEventsMap;
use LaterJob\Event\WorkerTransitionEvent;
use LaterJob\Config\Worker as WorkerConfig;
use LaterJob\Model\Transition\Transition;
use LaterJob\Exception as WorkerException;
use LaterJob\Exception as LaterJobException;
use DateTime;
/**
  *  Provide an API to manage the lifecycle of a work instance. 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Worker
{
    /**
      * @var worker id assigned by the manager, only valid for current session  
      */
    protected $id;
    
    /**
      *  @var DateTime the start time; 
      */
    protected $start;
    
    /**
      *  @var Symfony\Components\EventDispatcher\EventDispatcherInterface 
      */
    protected $event;
    
    /**
      *  @var  LaterJob\Config\Worker
      */
    protected $definition;
    
    /**
      *  @var LaterJob\Allocator 
      */
    protected $allocator;

    /**
      *  @var integer  LaterJob\Config\Worker::STATE_*
      */
    protected $current_state;

        
    /**
      *  Class Constructor
      *  
      *  @param string $id
      *  @param EventDispatcherInterface $event
      *  @param WorkerConfig $def
      *  @param Allocator $allocator
      *  @param integer $state_id the starting state
      */
    public function __construct($id, EventDispatcherInterface $event, WorkerConfig $def, Allocator $allocator, $state_id = null)
    {
        $this->id            = $id;
        $this->event         = $event;
        $this->definition    = $def;
        $this->allocator     = $allocator;
        $this->current_state = $state_id;
    }
    
    
    //------------------------------------------------------------------
    # Transitions
    
    /**
      *  Transition the worker to start state
      *
      *  @access public
      *  @param DateTime $start
      *  @param string $msg
      */
    public function start(DateTime $start , $msg = '')
    {
        # check if transiton is allowed
        if($this->current_state !== null) {
            throw new LaterJobException(sprintf('Can not transiton from %s to %s',
                                                    $this->getState(),
                                                    $this->definition->getLiteral(WorkerConfig::STATE_START)
                                                ));
        }
        
        $this->current_state = WorkerConfig::STATE_START;
        
        # assign the start time to this worker
        $this->start = $start;
        
        # create the transition entity
        $trans = new Transition();
        
        $trans->setWorker($this->getId());
        $trans->setState(WorkerConfig::STATE_START);
        $trans->setOccured($start);                
        $trans->setMessage($this->definition->getWorkerName() .' Worker STARTED :: '.$msg);
        
        # raise the start event        
        $this->event->dispatch(WorkerEventsMap::WORKER_START,new WorkerTransitionEvent($this,$trans));
    }
    
    /**
      *  Transtion to the finish state
      *
      *  @access public
      *  @param DateTime $finished
      *  @param string $msg
      */
    public function finish(DateTime $finished, $msg = '')
    {
        # check if transtion is allowed
        if($this->current_state !== WorkerConfig::STATE_START) {
            throw new LaterJobException(sprintf('Can not transiton from %s to %s',
                                                    $this->getState(),
                                                    $this->definition->getLiteral(WorkerConfig::STATE_FINISH)
                                                ));
        }
        
        $this->current_state = WorkerConfig::STATE_FINISH;
        
        # create the transition entity
        $trans = new Transition();
        
        $trans->setWorker($this->getId());
        $trans->setState(WorkerConfig::STATE_FINISH);
        $trans->setOccured($finished);
        $trans->setMessage($this->definition->getWorkerName() .' Worker FINISHED :: '.$msg);
        
        # raise the finish event
        $this->event->dispatch(WorkerEventsMap::WORKER_FINISH,new WorkerTransitionEvent($this,$trans));
    }
    
    /**
      *  Transition to the error state
      *
      *  @access public
      *  @param DateTime $when
      *  @param string $msg
      */
    public function error(DateTime $when , $msg = '')
    {
        $this->current_state = WorkerConfig::STATE_ERROR;
        
        # create the transition entity
        $trans = new Transition();
        
        $trans->setWorker($this->getId());
        $trans->setState(WorkerConfig::STATE_ERROR);
        $trans->setOccured($when);
        $trans->setMessage($this->definition->getWorkerName() .' Worker ERROR :: '.$msg);
        
        # unlock jobs 
        
        # raise the finish event
        $this->event->dispatch(WorkerEventsMap::WORKER_ERROR,new WorkerTransitionEvent($this,$trans));
    }
    
    //------------------------------------------------------------------
    # Properties
    
    /**
      *  Get the start time time this worker, time since start called
      *
      *  @return DateTime the start time
      *  @access public
      */
    public function getStartTime()
    {
        return $this->start;
    }
    
    /**
      *  Return the unique id of this worker
      *
      *  @access public
      *  @return string the id
      */
    public function getId()
    {
        return $this->id;
    }
    
    /**
      *  Return the entity object
      *
      *  @access public
      *  @return mixed
      */
    public function getConfig()
    {
        return $this->definition ;
    }
    
    /**
      *  Return the name of the current state
      *
      *  @access public
      *  @return string the current state of worker
      */
    public function getState()
    {
        return $this->definition->getLiteral($this->current_state);
    }
    
    //------------------------------------------------------------------
        
    /**
      *  Have the worker receive jobs.
      *
      *  @return LaterJob\Allocator
      *  @access public
      */
    public function receive(DateTime $now)
    {
        $jobs_to_process = $this->definition->getJobsToProcess();
        $lockout_time    = $this->definition->getJobLockoutTime();
        $worker_id       = $this->getId();
            
        # add the lockout to the date argument
        $lockout = clone $now;
        $lockout->modify('+'.$lockout_time.' seconds');  
            
        # call the receive on the allocator to lock a list of jobs
        $this->allocator->receive($jobs_to_process,$worker_id,$lockout,$now);
            
        # return the allocator so processing script can iterate over
        # the job list
        return $this->allocator;
    }
    
    
}
/* End of File */