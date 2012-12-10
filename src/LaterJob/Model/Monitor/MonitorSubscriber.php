<?php
namespace LaterJob\Model\Monitor;

use LaterJob\Event\MonitoringEventsMap;
use LaterJob\Event\MonitoringQueryEvent;
use LaterJob\Event\MonitoringEvent;
use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Config\Worker as WorkerConfig;
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
          MonitoringEventsMap::MONITOR_QUERY => array('onMonitorQuery'),
          MonitoringEventsMap::MONITOR_RUN   => array('onMonitorRun')
        );
    }
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param AbstractTable $gateway
      */
    public function __construct(AbstractTable $gateway,TransitionGateway $activity)
    {
        $this->gateway          = $gateway;
        $this->activity_gateway = $activity;
    }
    
    
    /**
      *  Calculate the max time taken for a worker to execute
      *  to its final state (Finished,Failed)
      *
      *  @param DateTime $now the starting time
      *  @param string $interval the period to observe over
      */
    public function getWorkerMaxRunningTime(DateTime $now, $interval = '+1 hour')
    {
        $result = null;
        
        try {
            
            # setup the date ranged
            $start = clone $now;
            $end   = clone $now;
            $end->modify($interval);
        
            # fetch query instance and setup the temportal range
            $query = $this->activity_gateway->getAdapater();
            
            
            $sql = 'SELECT MAX(TIME_TO_SEC(g.time_between)) + MAX(TIME_TO_SEC(g.date_between)) as max_running_time
                    FROM(
                        SELECT 
                            o.worker_id,
                            o.state_id as finish_state,
                            f.state_id as start_state, 
                            o.dte_occured as finish_date,
                            f.dte_occured as start_date, 
                            DATEDIFF(o.dte_occured,f.dte_occured) as date_between,
                            TIMEDIFF(o.dte_occured,f.dte_occured) as time_between
                        FROM later_job_transition AS o
                        INNER JOIN later_job_transition AS f ON o.worker_id = f.worker_id AND f.state_id = :state_start
                        WHERE o.state_id IN (:state_finish,:state_error)
                            AND (o.dte_occured >= :dte_occured_after) 
                            AND (o.dte_occured <= :dte_occured_before)
                        GROUP BY o.worker_id
                    ) AS g;';

            
            $stm = $query->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',WorkerConfig::STATE_FINISH,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_error',WorkerConfig::STATE_ERROR,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_start',WorkerConfig::STATE_START,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['max_running_time'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;


    }
    
    /**
      *  Calculate the mean time taken for a worker to execute
      *  to its final state (Finished,Failed)
      *
      *  @param DateTime $now the starting time
      *  @param string $interval the period to observe over
      */
    public function getWorkerMeanRunningTime(DateTime $now, $interval = '+1 hour')
    {
        $result = null;
        
        try {
            
            # setup the date ranged
            $start = clone $now;
            $end   = clone $now;
            $end->modify($interval);
        
            # fetch query instance and setup the temportal range
            $query = $this->activity_gateway->getAdapater();
            
            
            $sql = 'SELECT AVG(TIME_TO_SEC(g.time_between)) + AVG(TIME_TO_SEC(g.date_between)) as mean_running_time
                    FROM(
                        SELECT 
                            o.worker_id,
                            o.state_id as finish_state,
                            f.state_id as start_state, 
                            o.dte_occured as finish_date,
                            f.dte_occured as start_date, 
                            DATEDIFF(o.dte_occured,f.dte_occured) as date_between,
                            TIMEDIFF(o.dte_occured,f.dte_occured) as time_between
                        FROM later_job_transition AS o
                        INNER JOIN later_job_transition AS f ON o.worker_id = f.worker_id AND f.state_id = :state_start
                        WHERE o.state_id IN (:state_finish,:state_error)
                            AND (o.dte_occured >= :dte_occured_after) 
                            AND (o.dte_occured <= :dte_occured_before)
                        GROUP BY o.worker_id
                    ) AS g;';

            
            $stm = $query->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',WorkerConfig::STATE_FINISH,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_error',WorkerConfig::STATE_ERROR,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_start',WorkerConfig::STATE_START,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['mean_running_time'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;


    }
    
    
    /**
      *  Calculate the min time taken for a worker to execute
      *  to its final state (Finished,Failed)
      *
      *  @param DateTime $now the starting time
      *  @param string $interval the period to observe over
      */
    public function getWorkerMinRunningTime(DateTime $now, $interval = '+1 hour')
    {
        $result = null;
        
        try {
            
            # setup the date ranged
            $start = clone $now;
            $end   = clone $now;
            $end->modify($interval);
        
            # fetch query instance and setup the temportal range
            $query = $this->activity_gateway->getAdapater();
            
            
            $sql = 'SELECT MIN(TIME_TO_SEC(g.time_between)) + MIN(TIME_TO_SEC(g.date_between)) as min_running_time
                    FROM(
                        SELECT 
                            o.worker_id,
                            o.state_id as finish_state,
                            f.state_id as start_state, 
                            o.dte_occured as finish_date,
                            f.dte_occured as start_date, 
                            DATEDIFF(o.dte_occured,f.dte_occured) as date_between,
                            TIMEDIFF(o.dte_occured,f.dte_occured) as time_between
                        FROM later_job_transition AS o
                        INNER JOIN later_job_transition AS f ON o.worker_id = f.worker_id AND f.state_id = :state_start
                        WHERE o.state_id IN (:state_finish,:state_error)
                            AND (o.dte_occured >= :dte_occured_after) 
                            AND (o.dte_occured <= :dte_occured_before)
                        GROUP BY o.worker_id
                    ) AS g;';

            
            $stm = $query->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',WorkerConfig::STATE_FINISH,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_error',WorkerConfig::STATE_ERROR,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_start',WorkerConfig::STATE_START,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['min_running_time'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;


    }
    
    
     /**
      *  Calculate the max time taken for a job to transition from added state
      *  to its final state (Finished,Failed)
      *
      *  @param DateTime $now the starting time
      *  @param string $interval the period to observe over
      */
    public function getMaxServiceTime(DateTime $now, $interval = '+1 hour')
    {
        $result = null;
        
        try {
            
            # setup the date ranged
            $start = clone $now;
            $end   = clone $now;
            $end->modify($interval);
        
            # fetch query instance and setup the temportal range
            $query = $this->activity_gateway->getAdapater();
            
            
            $sql = 'SELECT MAX(TIME_TO_SEC(g.time_between)) + MAX(TIME_TO_SEC(g.date_between)) as max_service_time
                    FROM(
                        SELECT 
                            o.job_id,
                            o.state_id as finish_state,
                            f.state_id as start_state, 
                            o.dte_occured as finish_date,
                            f.dte_occured as start_date, 
                            DATEDIFF(o.dte_occured,f.dte_occured) as date_between,
                            TIMEDIFF(o.dte_occured,f.dte_occured) as time_between
                        FROM later_job_transition AS o
                        INNER JOIN later_job_transition AS f ON o.job_id = f.job_id AND f.state_id = :state_start
                        WHERE o.state_id IN (:state_finish,:state_fail)
                            AND (o.dte_occured >= :dte_occured_after) 
                            AND (o.dte_occured <= :dte_occured_before)
                        GROUP BY o.job_id
                    ) AS g;';


            
            $stm = $query->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',QueueConfig::STATE_FINISH,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_fail',QueueConfig::STATE_FAIL,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_start',QueueConfig::STATE_ADD,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['max_service_time'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;
        
    }
    
    /**
      *  Calculate the min time taken for a job to transition from added state
      *  to its final state (Finished,Failed)
      *
      *  @param DateTime $now the starting time
      *  @param string $interval the period to observe over
      */
    public function getMinServiceTime(DateTime $now, $interval = '+1 hour')
    {
        $result = null;
        
        try {
            
            # setup the date ranged
            $start = clone $now;
            $end   = clone $now;
            $end->modify($interval);
        
            # fetch query instance and setup the temportal range
            $query = $this->activity_gateway->getAdapater();
            
            
            $sql = 'SELECT MIN(TIME_TO_SEC(g.time_between)) + MIN(TIME_TO_SEC(g.date_between)) as min_service_time
                    FROM(
                        SELECT 
                            o.job_id,
                            o.state_id as finish_state,
                            f.state_id as start_state, 
                            o.dte_occured as finish_date,
                            f.dte_occured as start_date, 
                            DATEDIFF(o.dte_occured,f.dte_occured) as date_between,
                            TIMEDIFF(o.dte_occured,f.dte_occured) as time_between
                        FROM later_job_transition AS o
                        INNER JOIN later_job_transition AS f ON o.job_id = f.job_id AND f.state_id = :state_start
                        WHERE o.state_id IN (:state_finish,:state_fail)
                            AND (o.dte_occured >= :dte_occured_after) 
                            AND (o.dte_occured <= :dte_occured_before)
                        GROUP BY o.job_id
                    ) AS g;';


            
            $stm = $query->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',QueueConfig::STATE_FINISH,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_fail',QueueConfig::STATE_FAIL,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_start',QueueConfig::STATE_ADD,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['min_service_time'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;
        
    }
    
    /**
      *  Calculate the average time taken for a job to transition from added state
      *  to its final state (Finished,Failed)
      *
      *  @param DateTime $now the starting time
      *  @param string $interval the period to observe over
      */
    public function getMeanServiceTime(DateTime $now, $interval = '+1 hour')
    {
        $result = null;
        
        try {
            
            # setup the date ranged
            $start = clone $now;
            $end   = clone $now;
            $end->modify($interval);
        
            # fetch query instance and setup the temportal range
            $query = $this->activity_gateway->getAdapater();
            
            
            $sql = 'SELECT AVG(TIME_TO_SEC(g.time_between)) + AVG(TIME_TO_SEC(g.date_between)) as mean_service_time
                    FROM(
                        SELECT 
                            o.job_id,
                            o.state_id as finish_state,
                            f.state_id as start_state, 
                            o.dte_occured as finish_date,
                            f.dte_occured as start_date, 
                            DATEDIFF(o.dte_occured,f.dte_occured) as date_between,
                            TIMEDIFF(o.dte_occured,f.dte_occured) as time_between
                        FROM later_job_transition AS o
                        INNER JOIN later_job_transition AS f ON o.job_id = f.job_id AND f.state_id = :state_start
                        WHERE o.state_id IN (:state_finish,:state_fail)
                            AND (o.dte_occured >= :dte_occured_after) 
                            AND (o.dte_occured <= :dte_occured_before)
                        GROUP BY o.job_id
                    ) AS g;';


            
            $stm = $query->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',QueueConfig::STATE_FINISH,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_fail',QueueConfig::STATE_FAIL,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_start',QueueConfig::STATE_ADD,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['mean_service_time'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;
        
    }
    
    /**
      *  Count the number of jobs for each state.
      *
      *  @return array
      *  @param DateTime $now starting time
      *  @param string $interval the length of time to observe
      */
    public function countQueueJobStates(DateTime $now, $interval = '+1 hour')
    {
        $result = null;
        
        try {
            
            # setup the date ranged
            $start = clone $now;
            $end   = clone $now;
            $end->modify($interval);
        
            # fetch query instance and setup the temportal range
            $query = $this->activity_gateway->newQueryBuilder();
            
            # set the monitor interval
            $query->filterOccuredAfter($now)->filterOccuredBefore($end); 
            
            # limit count to jobs not workers
            $query->filterOnlyJobs();
            
            # Group the result by state
            $query->groupByState();
            
            # select the state property to count
            $query->select('state_id');
            
            # add virtual column for count
            $query->calculateStateCount();
            
            # bind the from
            $query->from($this->activity_gateway->getMetaData()->getName(),'');
            
            $stm = $query->execute();

            $result = array();
            
            while($data = $stm->fetch(\PDO::FETCH_ASSOC)) {
                
                $this->activity_gateway->convertToPhp($data);
                $result[]  = $data;
            }
            
            
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;
    
    }
    
    
     /**
      *  Get the mean jobs processed by a worker.
      *
      *  @return array
      *  @param DateTime $now starting time
      *  @param string $interval the length of time to observe
      */
    public function getWorkerMeanThroughput(DateTime $now, $interval = '+1 hour')
    {
            
         $result = null;
        
        try {
            
            # setup the date ranged
            $start = clone $now;
            $end   = clone $now;
            $end->modify($interval);
        
            # fetch query instance and setup the temportal range
            $query = $this->activity_gateway->getAdapater();
            
            
            $sql = 'SELECT AVG(g.num_processed) as mean_jobs_processed
                    FROM(
                        SELECT 
                            COUNT(process_handle) as num_processed
                        FROM later_job_transition
                        WHERE state_id IN (:state_finish,:state_fail)
                            AND (process_handle IS NOT NULL)
                            AND (dte_occured >= :dte_occured_after) 
                            AND (dte_occured <= :dte_occured_before)
                        GROUP BY process_handle
                    ) AS g;';


            
            $stm = $query->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->activity_gateway->getMetaData()->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',QueueConfig::STATE_FINISH,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->bindValue('state_fail',QueueConfig::STATE_FAIL,$this->activity_gateway->getMetaData()->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['mean_jobs_processed'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;
        
    }
    
    /**
      *  Handles the event MonitoringEventsMap::MONITOR_RUN
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
           $counts = $this->countQueueJobStates($result->getMonitorDate(),$event->getInterval());
            
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
            
           $result->setJobMeanServiceTime($this->getMeanServiceTime($result->getMonitorDate(),$event->getInterval()));
           $result->setJobMaxServiceTime($this->getMaxServiceTime($result->getMonitorDate(),$event->getInterval()));
           $result->setJobMinServiceTime($this->getMinServiceTime($result->getMonitorDate(),$event->getInterval()));

           
           # calculate worker stats
           $result->setWorkerMaxTime($this->getWorkerMaxRunningTime($result->getMonitorDate(),$event->getInterval()));
           $result->setWorkerMinTime($this->getWorkerMinRunningTime($result->getMonitorDate(),$event->getInterval()));
           $result->setWorkerMeanTime($this->getWorkerMeanRunningTime($result->getMonitorDate(),$event->getInterval()));
           
           $result->setWorkerMeanThroughput($this->getWorkerMeanThroughput($result->getMonitorDate(),$event->getInterval()));
           
           # calculate Utilization
           $result->setWorkerMeanUtilization($result->getWorkerMeanThroughput() / $result->getWorkerMaxThroughput());
           
           
           # completed successfuly 
           $event->setResult(true);            
            
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

            $obj = $event->getTransition();
            $result = $this->gateway->insertQuery()
                            ->start()
                                ->addColumn('worker_id',$obj->getWorker())
                                ->addColumn('state_id',$obj->getState())
                                ->addColumn('dte_occured',$obj->getOccured())
                                ->addColumn('transition_msg',$obj->getMessage())
                                ->addColumn('process_handle',$obj->getProcessHandle())
                            ->end()
                        ->insert();
            
            # assign the id to the object
            $obj->setTransitionId($this->gateway->lastInsertId());
            
            if($result === false) {
                throw new LaterJobException('Unable to save transition for Worker:: '.$obj->getWorker() .' It already exists');    
            }
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
    }
    
}
/* End of File */