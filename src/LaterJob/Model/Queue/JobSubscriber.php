<?php
namespace LaterJob\Model\Queue;

use LaterJob\Event\JobEventsMap;
use LaterJob\Event\JobTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DBALGateway\Exception as DBALGatewayException;
use DBALGateway\Table\AbstractTable;
use LaterJob\Exception as LaterJobException;

/**
  *  Handle events found in \LaterJob\Event\JobEventsMap
  *  to save the changes to the job state.
  *
  *  This handler does not insert new jobs only updates
  *  their state values.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class JobSubscriber implements EventSubscriberInterface
{
    /**
      *  @var StorageGateway 
      */
    protected $gateway;

    
    /**
      *  Bind event handlers to the dispatcher
      *
      *  @access public
      *  @static 
      *  @return array a binding to event handlers
      */
    static public function getSubscribedEvents()
    {
        return array(
            JobEventsMap::STATE_START  => array('onJobStart'),
            JobEventsMap::STATE_FAIL   => array('onJobFail'),
            JobEventsMap::STATE_ERROR  => array('onJobError'),
            JobEventsMap::STATE_FINISH => array('onJobFinish'),
        );
    }
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param AbstractTable $gateway
      */
    public function __construct(AbstractTable $gateway)
    {
        $this->gateway = $gateway;
    }
    
    
    /**
      *  Handles the event JobEventsMap::STATE_ERROR
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobError(JobTransitionEvent $event)
    {
        $this->save($event);
    }
    
    /**
      *  Handles the event JobEventsMap::STATE_FINISH
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobFinish(JobTransitionEvent $event)
    {
        $this->save($event);
    }
    
    /**
      *  Handles the event JobEventsMap::STATE_START
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobStart(JobTransitionEvent $event)
    {
        $this->save($event);
    }
    
    /**
      *  Handles the event JobEventsMap::STATE_FAIL
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobFail(JobTransitionEvent $event)
    {
        $this->save($event);
    }
    
     /**
      *  Handles the event JobEventsMap::STATE_ADD
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobAdd(JobTransitionEvent $event)
    {
        throw new LaterJobException('This is the default state, can not transition to it');
    }
    
    /**
      *  Save the job to the database
      *
      *  @access public
      *  @return boolean
      *  @param JobTransitionEvent $event
      */
    protected function save(JobTransitionEvent $event)
    {
        try {
            $obj    = $event->getTransition();
            $job    = $event->getJob();
            $result = $this->gateway->updateQuery()
                            ->start()
                                ->addColumn('state_id',$obj->getState())
                                ->addColumn('retry_count',$job->getRetryCount())
                                ->addColumn('retry_last',$job->getStorage()->getRetryLast())
                            ->where()
                                ->filterByJob($obj->getJob())
                            ->end()
                        ->update();
            
            # assign the id to the object
            $obj->getTransitionId($this->gateway->lastInsertId());
            
            if($result === false) {
                throw new LaterJobException('Unable to save transition for Job:: '.$obj->getJob() .' It already exists');    
            }
            
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
}
/* End of File */