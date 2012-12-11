<?php
namespace LaterJob\Model\Activity;

use DBALGateway\Table\AbstractTable;
use DateTime;
use LaterJob\Config\Queue as QueueConfig;
use LaterJob\Config\Worker as WorkerConfig;
use DBALGateway\Exception as DBALGatewayException;
use LaterJob\Exception as LaterJobException;

/**
  *  Table Gateway for transitions 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class TransitionGateway extends AbstractTable
{
    
    
    /**
      *  Create a new instance of the querybuilder
      *
      *  @access public
      *  @return LaterJob\Model\Activity\TransitionQuery
      */
    public function newQueryBuilder()
    {
        return new TransitionQuery($this->getAdapater(),$this);
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
            $query = $this->newQueryBuilder();
            
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
            $query->from($this->meta->getName(),'');
            
            $stm = $query->execute();

            $result = array();
            
            while($data = $stm->fetch(\PDO::FETCH_ASSOC)) {
                
                $this->convertToPhp($data);
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


            
            $stm = $this->adapter->prepare($sql);
            $stm->bindValue('dte_occured_after', $now,$this->meta->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->meta->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',QueueConfig::STATE_FINISH,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_fail'  ,QueueConfig::STATE_FAIL  ,$this->meta->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['mean_jobs_processed'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;
        
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

            
            $stm = $this->adapter->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->meta->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->meta->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',WorkerConfig::STATE_FINISH,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_error' ,WorkerConfig::STATE_ERROR ,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_start' ,WorkerConfig::STATE_START ,$this->meta->getColumn('state_id')->getType());
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

            
            $stm = $this->adapter->prepare($sql);
            $stm->bindValue('dte_occured_after' ,$now,$this->meta->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->meta->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',WorkerConfig::STATE_FINISH,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_error' ,WorkerConfig::STATE_ERROR ,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_start' ,WorkerConfig::STATE_START ,$this->meta->getColumn('state_id')->getType());
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

            
            $stm = $this->adapter->prepare($sql);
            $stm->bindValue('dte_occured_after',$now,$this->meta->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->meta->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',WorkerConfig::STATE_FINISH,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_error', WorkerConfig::STATE_ERROR ,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_start', WorkerConfig::STATE_START ,$this->meta->getColumn('state_id')->getType());
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


            
            $stm = $this->adapter->prepare($sql);
            $stm->bindValue('dte_occured_after' ,$now,$this->meta->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->meta->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',QueueConfig::STATE_FINISH,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_fail'  ,QueueConfig::STATE_FAIL  ,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_start' ,QueueConfig::STATE_ADD   ,$this->meta->getColumn('state_id')->getType());
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


            
            $stm = $this->adapter->prepare($sql);
            $stm->bindValue('dte_occured_after' ,$now,$this->meta->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->meta->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',QueueConfig::STATE_FINISH,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_fail'  ,QueueConfig::STATE_FAIL  ,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_start' ,QueueConfig::STATE_ADD   ,$this->meta->getColumn('state_id')->getType());
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


            
            $stm = $this->adapter->prepare($sql);
            $stm->bindValue('dte_occured_after', $now,$this->meta->getColumn('dte_occured')->getType());
            $stm->bindValue('dte_occured_before',$end,$this->meta->getColumn('dte_occured')->getType());
            
            $stm->bindValue('state_finish',QueueConfig::STATE_FINISH,$this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_fail'  ,QueueConfig::STATE_FAIL,  $this->meta->getColumn('state_id')->getType());
            $stm->bindValue('state_start' ,QueueConfig::STATE_ADD,   $this->meta->getColumn('state_id')->getType());
            $stm->execute();        
            
            $data = $stm->fetch(\PDO::FETCH_ASSOC);
            $result = (double) $data['mean_service_time'];
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $result;
        
    }
        
}
/* End of File */