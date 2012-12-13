<?php
namespace LaterJob\Model\Monitor;

use LaterJob\Event\MonitoringEventsMap;
use LaterJob\Event\MonitoringQueryEvent;
use LaterJob\Event\MonitoringEvent;
use LaterJob\Config\QueueConfig as QueueConfig;
use LaterJob\Config\WorkerConfig as WorkerConfig;
use LaterJob\Model\Activity\TransitionGateway;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DBALGateway\Exception as DBALGatewayException;
use DBALGateway\Table\AbstractTable;
use LaterJob\Exception as LaterJobException;
use DateTime;

/**
  *  Handle events found in \LaterJob\Event\WorkEventsMap 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class MonitorSubscriber implements EventSubscriberInterface
{
    /**
      *  @var StatsGateway 
      */
    protected $gateway;

    /**
      *  @var TransitionGateway 
      */
    protected $activity_gateway;
    
    
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
          MonitoringEventsMap::MONITOR_QUERY  => array('onMonitorQuery'),
          MonitoringEventsMap::MONITOR_RUN    => array('onMonitorRun'),
          MonitoringEventsMap::MONITOR_LOCK   => array('onMonitorLock'),
          MonitoringEventsMap::MONITOR_COMMIT => array('onMonitorCommit')
        );
    }
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param AbstractTable $gateway
      */
    public function __construct(AbstractTable $gateway,AbstractTable $activity)
    {
        $this->gateway          = $gateway;
        $this->activity_gateway = $activity;
    }
    
    
   
    /**
      *  Handles the event MonitoringEventsMap::MONITOR_LOCK
      *
      *  @access public
      *  @return void
      *  @param MonitoringEvent $event
      */
    public function onMonitorLock(MonitoringEvent $event)
    {
        try {
            $result = $this->gateway->insertQuery()
                ->start()
                    ->addColumn('monitor_dte',$event->getStats()->getMonitorDate())
                    ->addColumn('monitor_complete',false)
                ->end()
            ->insert();
            
            $event->getStats()->setMonitorId($this->gateway->lastInsertId());
            
            # completed successfuly 
            $event->setResult($result);            
            
        } catch(DBALGatewayException $e) {
            $event->setResult(false);
            throw new LaterJobException($e->getMessage(),0,$e);
        }
                
        return $event;
    }
    
     /**
      *  Handles the event MonitoringEventsMap::MONITOR_COMMIT
      *
      *  @access public
      *  @return void
      *  @param MonitoringEvent $event
      */
    public function onMonitorRun(MonitoringEvent $event)
    {
        try {
     
            $result = $event->getStats();
         
            # Get Queue Job Counts
            $counts = $this->activity_gateway->countQueueJobStates($result->getMonitorDate(),$event->getInterval());
             
            foreach($counts as $state_count) {
             
             switch($state_count['state_id']) {
                 case QueueConfig::STATE_ADD :
                     $result->setQueueJobsAdded($state_count['state_count']);
                 break;
                 case QueueConfig::STATE_START :
                     $result->setQueueJobsProcessing($state_count['state_count']);
                 break;
                 case QueueConfig::STATE_FINISH :
                     $result->setQueueJobsCompleted($state_count['state_count']);
                 break;
                 case QueueConfig::STATE_ERROR :
                     $result->setQueueJobsError($state_count['state_count']);
                 break;
                 case QueueConfig::STATE_FAIL :
                     $result->setQueueJobsFailed($state_count['state_count']);
                 break;
                 default:
                     throw new LaterJobException('Unknown job state can not continue');
             }
             
            }
            
            # get the queue Mean Max and Min Service Times
             
            $result->setJobMeanServiceTime($this->activity_gateway->getMeanServiceTime($result->getMonitorDate(),$event->getInterval()));
            $result->setJobMaxServiceTime($this->activity_gateway->getMaxServiceTime($result->getMonitorDate(),$event->getInterval()));
            $result->setJobMinServiceTime($this->activity_gateway->getMinServiceTime($result->getMonitorDate(),$event->getInterval()));
    
            
            # calculate worker stats
            $result->setWorkerMaxTime($this->activity_gateway->getWorkerMaxRunningTime($result->getMonitorDate(),$event->getInterval()));
            $result->setWorkerMinTime($this->activity_gateway->getWorkerMinRunningTime($result->getMonitorDate(),$event->getInterval()));
            $result->setWorkerMeanTime($this->activity_gateway->getWorkerMeanRunningTime($result->getMonitorDate(),$event->getInterval()));
            
            $result->setWorkerMeanThroughput($this->activity_gateway->getWorkerMeanThroughput($result->getMonitorDate(),$event->getInterval()));
            
            # calculate Utilization
            $result->setWorkerMeanUtilization($result->getWorkerMeanThroughput() / $result->getWorkerMaxThroughput());
            
            
            # completed successfuly 
            $event->setResult(true);            
            
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
                
        return $event;
        
    }
   
    
    /**
      *  Handles the event MonitoringEventsMap::MONITOR_COMMIT
      *
      *  @access public
      *  @return void
      *  @param MonitoringEvent $event
      */
    public function onMonitorCommit(MonitoringEvent $event)
    {
        try {
        
            $stats = $event->getStats();
        
           # save the results into db
           $success = $this->gateway->updateQuery()
                ->start()
                    ->addColumn('worker_max_time',$stats->getWorkerMaxTime())
                    ->addColumn('worker_min_time',$stats->getWorkerMinTime())
                    ->addColumn('worker_mean_time',$stats->getWorkerMeanTime())
                    ->addColumn('worker_mean_throughput',$stats->getWorkerMeanThroughput())
                    ->addColumn('worker_max_throughput',$stats->getWorkerMaxThroughput())
                    ->addColumn('worker_mean_utilization',$stats->getWorkerMeanUtilization())
                    ->addColumn('queue_no_waiting_jobs',$stats->getQueueJobsAdded())
                    ->addColumn('queue_no_failed_jobs',$stats->getQueueJobsFailed())
                    ->addColumn('queue_no_error_jobs',$stats->getQueueJobsError())
                    ->addColumn('queue_no_completed_jobs',$stats->getQueueJobsCompleted())
                    ->addColumn('queue_no_processing_jobs',$stats->getQueueJobsProcessing())
                    ->addColumn('queue_mean_service_time',$stats->getJobMeanServiceTime())
                    ->addColumn('queue_min_service_time',$stats->getJobMinServiceTime())
                    ->addColumn('queue_max_service_time',$stats->getJobMaxServiceTime())
                    ->addColumn('monitor_complete',true)
                ->where()
                    ->filterByMonitor($event->getStats()->getMonitorId())
                ->end()
            ->update();
           
           if($success === true) {
             # completed successfuly 
             $event->setResult(true);             
           }
            
        }catch(DBALGatewayException $e) {
            $event->setResult(false);
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $event;
        
    }
    
    
   
    /**
      *  Handles the event MonitoringEventsMap::MONITOR_QUERY
      *
      *  @access public
      *  @return void
      *  @param MonitoringQueryEvent $event
      */
    public function onMonitorQuery(MonitoringQueryEvent $event)
    {
         try {
            
            $query = $this->gateway->selectQuery()->start();
            
            if($event->getStart() instanceof DateTime) {
                $query->andWhereCoverDateAfter($event->getStart());
            }
            
            if($event->getEnd() instanceof DateTime) {
                $query->andWhereCoverDateBefore($event->getEnd());
            }
            
            if($event->getLimit() !== null) {
                $query->limit($event->getLimit());
            }
            
            if($event->getOffset() !== null) {
                $query->offset($event->getOffset());
            }
            
            if($event->getIncludeCalculating() === false) {
                # only going to restrict if to not include calculating
                $query->andWhereCalculated();
            } 
            
            $query->orderMonitorDate($event->getOrder());
            
            $event->setResult($query->end()->find());
        
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
    }
    
}
/* End of File */