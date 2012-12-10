<?php
namespace LaterJob\Model\Queue;

use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueueListEvent;
use LaterJob\Event\QueueLockEvent;
use LaterJob\Event\QueuePurgeEvent;
use LaterJob\Event\QueueReceiveEvent;
use LaterJob\Event\QueueRemoveEvent;
use LaterJob\Event\QueueSendEvent;
use LaterJob\Event\QueueLookupEvent;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Queue\Storage;
use LaterJob\Exception as LaterJobException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DBALGateway\Exception as DBALGatewayException;
use DBALGateway\Table\AbstractTable;
use Doctrine\Common\Collections\Collection;
use DateTime;


/**
  *  Handle events found in \LaterJob\Event\QueueEventsMap.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueSubscriber implements EventSubscriberInterface
{
    /**
      *  @var StorageGateway 
      */
    protected $gateway;

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
      *  Bind event handlers to the dispatcher
      *
      *  @access public
      *  @static 
      *  @return array a binding to event handlers
      */
    static public function getSubscribedEvents()
    {
        return array(
            QueueEventsMap::QUEUE_LOCK     => array('onLock'),
            QueueEventsMap::QUEUE_UNLOCK   => array('onUnlock'),
            QueueEventsMap::QUEUE_REC      => array('onReceive'),
            QueueEventsMap::QUEUE_SENT     => array('onSend'),
            QueueEventsMap::QUEUE_REMOVE   => array('onRemove'),
            QueueEventsMap::QUEUE_LIST     => array('onList'),
            QueueEventsMap::QUEUE_PURGE    => array('onPurge'),
            QueueEventsMap::QUEUE_LOOKUP   => array('onLookup'),
        );
    }
    
   
     public function onLookup(QueueLookupEvent $event)
    {
        
        try {
            $result = $this->gateway->selectQuery()
                ->start()
                    ->filterByJob($event->getJobId())    
                    ->limit(1)
                ->end()
            ->findOne();
            
            if(count($result) > 0) {
                $event->setResult($result);    
            }
            
            
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
   
   
    public function onLock(QueueLockEvent $event)
    {
        try {
            $this->gateway->updateQuery()
                            ->start()
                                ->addColumn('handle',$event->getHandle())
                                ->addColumn('lock_timeout',$event->getTimeout())
                            ->where()
                                ->filterByStateAdd()
                                ->filterByStateErrorPassedRetryWait($event->getNow())
                                ->filterByExpiredOrEmptyLockout($event->getNow())
                                ->limit($event->getLimit())
                                ->orderByDateAdded()
                            ->end()
                        ->update();
            
            # did we lock some rows
            $event->setResult($this->gateway->rowsAffected());
        
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
    
    public function onUnlock(QueueLockEvent $event)
    {
        try {
            $this->gateway->updateQuery()
                            ->start()
                                ->addColumn('handle',null)
                                ->addColumn('lock_timeout',null)
                            ->where()
                                ->filterByLockout($event->getHandle())
                            ->end()
                        ->update();
            
            # did we lock some rows
            $event->setResult($this->gateway->rowsAffected());
        
        } catch(DBALGatewayException $e) {
            $event->setLocked(0);
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
    public function onReceive(QueueReceiveEvent $event)
    {
        try {
            # save the job onto the queue
            $result = $this->gateway->insertQuery()
                            ->start()
                                ->addColumn('job_id',$event->getStorage()->getJobId())
                                ->addColumn('state_id',$event->getStorage()->getState())
                                ->addColumn('dte_add',$event->getStorage()->getDateAdded())
                                ->addColumn('retry_count',$event->getStorage()->getRetryLeft())
                                ->addColumn('job_data',$event->getStorage()->getJobData())
                            ->end()
                        ->insert();
            
            # did we lock some rows
            $event->setResult($result);
        
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
    public function onSend(QueueSendEvent $event)
    {
        
        try {
            $handle = $event->getHandle();
            $now    = $event->getTimeout();
    
            # restrict selection of to a locked job, with matching handle.
            # job who's lockout is valid (lockout time <= now)
            # only pick jobs that are in waiting state (ADD,ERROR)
            $result = $this->gateway->selectQuery()
                ->start()
                    ->filterByStateAdd()
                    ->filterByStateErrorPassedRetryWait($now)
                    ->filterByLockout($handle)
                    ->filterByLockTimmerAfter($now)
                    ->limit($event->getLimit())
                    ->orderByDateAdded()
                ->end()
            ->find();
            
            if(count($result) > 0) {
                $event->setResult($result);    
            }
            
            
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
    public function onList(QueueListEvent $event)
    {
        try {
        
            $query = $this->gateway->selectQuery()->start();
            
            if($event->getState() !== null) {
                $query->filterByState($event->getState());
            }
            
            if($event->getAfter() instanceof DateTime) {
                $query->filterByAddedAfter($event->getAfter());
            }
            
            if($event->getBefore() instanceof DateTime) {
                $query->filterByAddedBefore($event->getBefore());
            }
            
            if($event->getLimit() !== null) {
                $query->limit($event->getLimit());
            }
            
            if($event->getOffset() !== null) {
                $query->offset($event->getOffset());
            }
            
            # set the order by the date added
            $query->orderByDateAdded($event->getOrder());
            
            $event->setResult($query->end()->find());
        
        
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
    public function onRemove(QueueRemoveEvent $event)
    {
        try {
            
            $result = $this->gateway->deleteQuery()
                        ->start()
                            ->filterByJob($event->getJobId())
                            ->filterByExpiredOrEmptyLockout($event->getNow())
                        ->end()
                    ->delete();
                        
            $event->setResult($result);
        
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
    public function onPurge(QueuePurgeEvent $event)
    {
         try {
            
            # purge jobs that have been completed or failed
            # before x date 
            $result = $this->gateway->deleteQuery()
                        ->start()
                            ->filterByStateFail()
                            ->filterByStateFinish()
                            ->filterByAddedBefore($event->getBeforeDate())
                        ->end()
                    ->delete();
                        
            $event->setResult($this->gateway->rowsAffected());
        
        } catch(DBALGatewayException $e) {
            $event->setResult(false);
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
}
/* End of File */