<?php
namespace LaterJob\Model\Monitor;

use DBALGateway\Builder\BuilderInterface;
use DateTime;

/**
  *  Builder for Stats Entities
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class StatsBuilder implements BuilderInterface
{
    
    /**
      *  Convert data array into entity
      *
      *  @return mixed
      *  @param array $data
      *  @access public
      */
    public function build($data)
    {
        $obj = new Stats();
        
        # bind the db id and date the monitor covers  
        $obj->setComplete($data['monitor_complete']);
        $obj->setMonitorId($data['monitor_id']);
        if($data['monitor_dte'] instanceof DateTime) {
            $obj->setMonitorDate($data['monitor_dte']);
        }
        
        # bind worker run times
        $obj->setWorkerMaxTime($data['worker_max_time']);
        $obj->setWorkerMinTime($data['worker_min_time']);
        $obj->setWorkerMeanTime($data['worker_mean_time']);
        
        # bind the worker throughput
        $obj->setWorkerMaxThroughput($data['worker_max_throughput']);
        $obj->setWorkerMeanThroughput($data['worker_mean_throughput']);
        $obj->setWorkerMeanUtilization($data['worker_mean_utilization']);
        
        # bind queue stats
        $obj->setQueueJobsAdded($data['queue_no_waiting_jobs']);
        $obj->setQueueJobsCompleted($data['queue_no_completed_jobs']);
        $obj->setQueueJobsError($data['queue_no_error_jobs']);
        $obj->setQueueJobsFailed($data['queue_no_failed_jobs']);
        $obj->setQueueJobsProcessing($data['queue_no_processing_jobs']);
        
        # set the job runtimes
        $obj->setJobMaxServiceTime($data['queue_max_service_time']);
        $obj->setJobMinServiceTime($data['queue_min_service_time']);
        $obj->setJobMeanServiceTime($data['queue_mean_service_time']);
        
        
        return $obj;
        
    }
    
    /**
      *  Convert and entity into a data array
      *
      *  @return array
      *  @access public
      */
    public function demolish($entity)
    {
        return array(
           'monitor_id'              => $entity->getMonitorId(),
           'monitor_complete'        => $entity->getComplete(),
           'monitor_dte'             => $entity->getMonitorDate(),
           'worker_max_time'         => $entity->getWorkerMaxTime(),
           'worker_min_time'         => $entity->getWorkerMinTime(),
           'worker_mean_time'        => $entity->getWorkerMeanTime(),
           'worker_max_throughput'   => $entity->getWorkerMaxThroughput(),
           'worker_mean_throughput'  => $entity->getWorkerMeanThroughput(),
           'worker_mean_utilization' => $entity->getWorkerMeanUtilization(),
           'queue_no_waiting_jobs'   => $entity->getQueueJobsAdded(),
           'queue_no_completed_jobs' => $entity->getQueueJobsCompleted(),
           'queue_no_error_jobs'     => $entity->getQueueJobsError(),
           'queue_no_failed_jobs'    => $entity->getQueueJobsFailed(),
           'queue_no_processing_jobs'=> $entity->getQueueJobsProcessing(),
           'queue_max_service_time'  => $entity->getJobMaxServiceTime(),
           'queue_min_service_time'  => $entity->getJobMinServiceTime(),
           'queue_mean_service_time' => $entity->getJobMeanServiceTime()
            
        );
        
    }
    
}

/* End of File */