<?php
namespace LaterJob\Model\Monitor;

use DateTime;

/**
  *  Base 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Stats
{
    
    /**
      *  @var integer monitor id 
      */
    protected $monitor_id;
    
    /**
      *  @var DateTime the period which been monitored 
      */
    protected $monitor_date;
    
    /**
      *  @var integer the max time of worker 
      */
    protected $worker_max_time;
    
    /**
      *  @var integer min time taken by worker
      */
    protected $worker_min_time;
    
    /**
      *  @var integer max time taken by worker 
      */
    protected $worker_mean_time;
    
    /**
      *  @var  integer the average processed jobs this last worker
      */
    protected $worker_mean_throughput;
    
    /**
      *  @var integer max throughput from config file maxium possible output from workers
      */
    protected $worker_max_throughput;
    
    /**
      *  @var integer mean utilization of the queue in last hour
      *  calculated throughput/maxium throughput 
      *  
      */
    protected $worker_mean_utilization;
    
    /**
      *  @var integer number of jobs added
      */        
    protected $queue_no_added_jobs;
    
    /**
      *  @var integer number of failed jobs in last hour
      */
    protected $queue_no_failed_jobs;
    
    /**
      *  @var integer number of jobs where error occured
      */
    protected $queue_no_error_jobs;
    
    /**
      *  @var integer number of jobs completed in last hours
      */
    protected $queue_no_completed_jobs;
    
    /**
      *  @var integer number of jobs in processing in last hour
      */
    protected $queue_no_processing_jobs;
    
    /**
      *  @var integer the mean service time in last hour
      */
    protected $queue_mean_service_time;
    
    /**
      *  @var integer the min service time in the last hour
      */
    protected $queue_min_service_time;
    
    /**
      *  @var integer the maxium service time in last hour
      */
    protected $queue_max_service_time;
    
    
    
    //------------------------------------------------------------------
    # Worker Accessors
    
    public function getMonitorId()
    {
        return $this->monitor_id;
    }
    
    public function setMonitorId($id)
    {
        $this->monitor_id = $id;
    }
    
    
    public function getMonitorDate()
    {
        return $this->monitor_date;
    }
    
    public function setMonitorDate(DateTime $det)
    {
        $this->monitor_date = $dte;
    }
    
    public function getWorkerMaxTime()
    {
        return $this->worker_max_time;
    }
    
    public function setWorkerMaxTime($time)
    {
        $this->worker_max_time = $time;
    }
    
    public function getWorkerMinTime()
    {
        return $this->worker_min_time;
    }
    
    public function setWorkerMinTime($time)
    {
        $this->worker_min_time = $time;
    }
    
    public function getWorkerMeanThroughput()
    {
        return $this->worker_mean_throughput;
    }
    
    public function setWorkerMeanThroughput($throughput)
    {
        $this->worker_mean_throughput = $throughput;
    }
    
    
    public function getWorkerMaxThroughput()
    {
        return $this->worker_max_throughput;
    }
    
    public function setWorkerMaxThroughput($max)
    {
        $this->worker_max_throughput = $max;
    }
    
    public function getWorkerMeanUtilization()
    {
        return $this->worker_mean_utilization;
    }
    
    public function setWorkerMeanUtilization($util)
    {
        $this->worker_mean_utilization = $util;
    }
    
    //------------------------------------------------------------------
    # Queue Accessors
    
    
    public function getQueueJobsAdded()
    {
        
    }
    
    
    public function setQueueJobsAdded($count)
    {
        
    }
    
    
    public function getQueueJobsFailed()
    {
        
    }
    
    public function setQueueJobsFailed($count)
    {
        
    }
    
    
    public function getQueueJobsCompleted()
    {
        
    }
    
    public function setQueueJobsCompleted($count)
    {
        
    }
    
    
    public function getQueueJobsError()
    {
        
    }
    
    public function setQueueJobsError($count)
    {
        
    }
    
    
    public function getQueueJobsProcessing()
    {
        
    }
    
    
    //public function setQueue
    
}

/* End of File */