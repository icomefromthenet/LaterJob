<?php
namespace LaterJob\Tests\Model\Monitor;

use LaterJob\Model\Monitor\Stats;
use LaterJob\Model\Monitor\StatsBuilder;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
  *  Unit Tests for Model Monitor StatsBuilder and Entity test 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class BuilderTest extends  PHPUnit_Framework_TestCase
{
    
    public function testEntityProperties()
    {
        $entity = new Stats();
        
        $monitor_id   = 1;
        $monitor_date = new DateTime();
        $worker_max_runtime = (60*5);
        $worker_min_runtime = (60*2);
        $worker_mean_runtime = (60*3);
        $worker_mean_throughput = 100;
        $worker_max_throughput = 100;
        $worker_mean_utilization = 50;
        $monitor_complete = false;
        
        $entity->setMonitorId($monitor_id);
        $entity->setMonitorDate($monitor_date);
        $entity->setWorkerMaxTime($worker_max_runtime);
        $entity->setWorkerMinTime($worker_min_runtime);
        $entity->setWorkerMeanTime($worker_mean_runtime);
        $entity->setWorkerMeanThroughput($worker_mean_throughput);
        $entity->setWorkerMaxThroughput($worker_max_throughput);
        $entity->setWorkerMeanUtilization($worker_mean_utilization);
        $entity->setComplete($monitor_complete);
        
        
        $this->assertEquals($monitor_complete,$entity->getComplete());
        $this->assertEquals($monitor_id,$entity->getMonitorId());
        $this->assertEquals($monitor_date,$entity->getMonitorDate());
        $this->assertEquals($worker_max_runtime,$entity->getWorkerMaxTime());
        $this->assertEquals($worker_min_runtime,$entity->getWorkerMinTime());
        $this->assertEquals($worker_mean_runtime,$entity->getWorkerMeanTime());
        $this->assertEquals($worker_mean_throughput,$entity->getWorkerMeanThroughput());
        $this->assertEquals($worker_max_throughput,$entity->getWorkerMaxThroughput());
        $this->assertEquals($worker_mean_utilization,$entity->getWorkerMeanUtilization());
        
        # set the queue properties
        
        $queue_no_waiting_jobs    = 100;
        $queue_no_completed_jobs  = 600;
        $queue_no_error_jobs      = 500;
        $queue_no_failed_jobs     = 400;
        $queue_no_processing_jobs = 300; 
           
        $entity->setQueueJobsAdded($queue_no_waiting_jobs);
        $entity->setQueueJobsCompleted($queue_no_completed_jobs);
        $entity->setQueueJobsError($queue_no_error_jobs);
        $entity->setQueueJobsFailed($queue_no_failed_jobs);
        $entity->setQueueJobsProcessing($queue_no_processing_jobs);
         
        $this->assertEquals($queue_no_waiting_jobs,$entity->getQueueJobsAdded());
        $this->assertEquals($queue_no_completed_jobs,$entity->getQueueJobsCompleted());
        $this->assertEquals($queue_no_error_jobs,$entity->getQueueJobsError());
        $this->assertEquals($queue_no_failed_jobs,$entity->getQueueJobsFailed());
        $this->assertEquals($queue_no_processing_jobs,$entity->getQueueJobsProcessing());
         
        # set the job runtime   
        $queue_max_service_time   = 350;
        $queue_min_service_time   = 375;
        $queue_mean_service_time  = 677;
        
        $entity->setJobMaxServiceTime($queue_max_service_time);
        $entity->setJobMinServiceTime($queue_min_service_time);
        $entity->setJobMeanServiceTime($queue_mean_service_time);
        
        $this->assertEquals($queue_max_service_time,$entity->getJobMaxServiceTime());
        $this->assertEquals($queue_min_service_time,$entity->getJobMinServiceTime());
        $this->assertEquals($queue_mean_service_time,$entity->getJobMeanServiceTime());
    }
    
    
    
    public function testBuild()
    {
        $monitor_id   = 1;
        $monitor_date = new DateTime();
        $worker_max_runtime = (60*5);
        $worker_min_runtime = (60*2);
        $worker_mean_runtime = (60*3);
        $worker_mean_throughput = 100;
        $worker_max_throughput = 100;
        $worker_mean_utilization = 50;
        $monitor_complete        = false;
        
        $queue_no_waiting_jobs    = 100;
        $queue_no_completed_jobs  = 600;
        $queue_no_error_jobs      = 500;
        $queue_no_failed_jobs     = 400;
        $queue_no_processing_jobs = 300;
        
        $queue_max_service_time   = 350;
        $queue_min_service_time   = 375;
        $queue_mean_service_time  = 677;
        
        
        $data = array (
           'monitor_id'              => $monitor_id,
           'monitor_complete'        => $monitor_complete,
           'monitor_dte'             => $monitor_date,
           'worker_max_time'         => $worker_max_runtime,
           'worker_min_time'         => $worker_min_runtime,
           'worker_mean_time'        => $worker_mean_runtime,
           'worker_max_throughput'   => $worker_max_throughput,
           'worker_mean_throughput'  => $worker_mean_throughput,
           'worker_mean_utilization' => $worker_mean_utilization,
           'queue_no_waiting_jobs'   => $queue_no_waiting_jobs,
           'queue_no_completed_jobs' => $queue_no_completed_jobs,
           'queue_no_error_jobs'     => $queue_no_error_jobs,
           'queue_no_failed_jobs'    => $queue_no_failed_jobs,
           'queue_no_processing_jobs'=> $queue_no_processing_jobs,
           'queue_max_service_time'  => $queue_max_service_time,
           'queue_min_service_time'  => $queue_min_service_time,
           'queue_mean_service_time' => $queue_mean_service_time
        );
        
        $builder = new StatsBuilder();
        
        $entity = $builder->build($data);
        
        $this->assertEquals($monitor_id,$entity->getMonitorId());
        $this->assertEquals($monitor_date,$entity->getMonitorDate());
        $this->assertEquals($worker_max_runtime,$entity->getWorkerMaxTime());
        $this->assertEquals($worker_min_runtime,$entity->getWorkerMinTime());
        $this->assertEquals($worker_mean_runtime,$entity->getWorkerMeanTime());
        $this->assertEquals($worker_mean_throughput,$entity->getWorkerMeanThroughput());
        $this->assertEquals($worker_max_throughput,$entity->getWorkerMaxThroughput());
        $this->assertEquals($worker_mean_utilization,$entity->getWorkerMeanUtilization());
        $this->assertEquals($monitor_complete,$entity->getComplete());
        $this->assertEquals($queue_no_waiting_jobs,$entity->getQueueJobsAdded());
        $this->assertEquals($queue_no_completed_jobs,$entity->getQueueJobsCompleted());
        $this->assertEquals($queue_no_error_jobs,$entity->getQueueJobsError());
        $this->assertEquals($queue_no_failed_jobs,$entity->getQueueJobsFailed());
        $this->assertEquals($queue_no_processing_jobs,$entity->getQueueJobsProcessing());
        $this->assertEquals($queue_max_service_time,$entity->getJobMaxServiceTime());
        $this->assertEquals($queue_min_service_time,$entity->getJobMinServiceTime());
        $this->assertEquals($queue_mean_service_time,$entity->getJobMeanServiceTime());
    }
    
    
    public function testDemolish()
    {
        $entity = new Stats();
        $monitor_id   = 1;
        $monitor_date = new DateTime();
        $worker_max_runtime = (60*5);
        $worker_min_runtime = (60*2);
        $worker_mean_runtime = (60*3);
        $worker_mean_throughput = 100;
        $worker_max_throughput = 100;
        $worker_mean_utilization = 50;
        $monitor_complete        = false;
        
        $queue_no_waiting_jobs    = 100;
        $queue_no_completed_jobs  = 600;
        $queue_no_error_jobs      = 500;
        $queue_no_failed_jobs     = 400;
        $queue_no_processing_jobs = 300;
        
        $queue_max_service_time   = 350;
        $queue_min_service_time   = 375;
        $queue_mean_service_time  = 677;
        
        
        $entity->setMonitorId($monitor_id);
        $entity->setMonitorDate($monitor_date);
        $entity->setWorkerMaxTime($worker_max_runtime);
        $entity->setWorkerMinTime($worker_min_runtime);
        $entity->setWorkerMeanTime($worker_mean_runtime);
        $entity->setWorkerMeanThroughput($worker_mean_throughput);
        $entity->setWorkerMaxThroughput($worker_max_throughput);
        $entity->setWorkerMeanUtilization($worker_mean_utilization);
        $entity->setComplete($monitor_complete);
        $entity->setQueueJobsAdded($queue_no_waiting_jobs);
        $entity->setQueueJobsCompleted($queue_no_completed_jobs);
        $entity->setQueueJobsError($queue_no_error_jobs);
        $entity->setQueueJobsFailed($queue_no_failed_jobs);
        $entity->setQueueJobsProcessing($queue_no_processing_jobs);
        $entity->setJobMaxServiceTime($queue_max_service_time);
        $entity->setJobMinServiceTime($queue_min_service_time);
        $entity->setJobMeanServiceTime($queue_mean_service_time);
        
        $builder = new StatsBuilder();
        
        $data = $builder->demolish($entity);
        
        $this->assertEquals($data, array (
           'monitor_id'              => $monitor_id,
           'monitor_complete'        => $monitor_complete,
           'monitor_dte'             => $monitor_date,
           'worker_max_time'         => $worker_max_runtime,
           'worker_min_time'         => $worker_min_runtime,
           'worker_mean_time'        => $worker_mean_runtime,
           'worker_max_throughput'   => $worker_max_throughput,
           'worker_mean_throughput'  => $worker_mean_throughput,
           'worker_mean_utilization' => $worker_mean_utilization,
           'queue_no_waiting_jobs'   => $queue_no_waiting_jobs,
           'queue_no_completed_jobs' => $queue_no_completed_jobs,
           'queue_no_error_jobs'     => $queue_no_error_jobs,
           'queue_no_failed_jobs'    => $queue_no_failed_jobs,
           'queue_no_processing_jobs'=> $queue_no_processing_jobs,
           'queue_max_service_time'  => $queue_max_service_time,
           'queue_min_service_time'  => $queue_min_service_time,
           'queue_mean_service_time' => $queue_mean_service_time
        ));
    }
    
    
}
/* End of File */