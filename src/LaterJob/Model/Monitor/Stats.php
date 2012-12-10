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
      *  @var  integer the average processed jobs
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
      *  @var integer number of jobs in processing ie not finished
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
      *  @var integer the maximum service time in last hour
      */
    protected $queue_max_service_time;
    
    /**
      *  @var boolean has the monitoring operation been completed or reserved 
      */
    protected $monitor_complete;
    
    //------------------------------------------------------------------
    # Worker Accessors
    
    /**
      *  Gets if the monitoring operation is complete
      *
      *  @access public
      *  @return boolean true if complete
      */
    public function getComplete()
    {
        return $this->monitor_complete;
    }
    
    /**
      *   Sets if the monitoring operation is complete
      *
      *   @access public
      *   @return boolean true if complete
      */
    public function setComplete($complete)
    {
        $this->monitor_complete = $complete;
    }
    
    
    /**
      *  Gets the Monitor Database id
      *
      *  @access public
      *  @return integer 
      */
    public function getMonitorId()
    {
        return $this->monitor_id;
    }
    
    /**
      *  Sets the monitor database id
      *
      *  @access public
      *  @param integer the db id
      */
    public function setMonitorId($id)
    {
        $this->monitor_id = $id;
    }
    
    /**
      *  DateTime the monitor covers, the hour where coverage starts
      *
      *  @access public
      *  @return DateTime 
      */
    public function getMonitorDate()
    {
        return $this->monitor_date;
    }
    
    /**
      *  Sets the DateTime the monitor covers
      *
      *  @access public
      *  @param DateTime
      */
    public function setMonitorDate(DateTime $date)
    {
        $this->monitor_date = $date;
    }
    
    /**
      *  Gets the Maximum time a worker has taken
      *
      *  @access public
      *  @return integer the time taken in seconds
      */
    public function getWorkerMaxTime()
    {
        return $this->worker_max_time;
    }
    
    /**
      *  Sets the Maximum time a worker has taken
      *
      *  @access public
      *  @param integer $time in seconds
      */
    public function setWorkerMaxTime($time)
    {
        $this->worker_max_time = $time;
    }
    
    /**
      *  Gets the minimum time taken by a worker
      *
      *  @access public
      *  @return integer the time in seconds
      */
    public function getWorkerMinTime()
    {
        return $this->worker_min_time;
    }
    
    /**
      *  Sets the minimum time taken by a worker
      *
      *  @access public
      *  @return integer the time in seconds
      */
    public function setWorkerMinTime($time)
    {
        $this->worker_min_time = $time;
    }
    
    /**
      *  Gets the mean runtime of the workers
      *
      *  @access public
      *  @return integer the mean runtime in seconds
      */
    public function getWorkerMeanTime()
    {
        return $this->worker_mean_time;
    }
    
    /**
      *  Sets the mean runtime of the workers
      *
      *  @access public
      *  @param integer $time the mean runtime in seconds
      */
    public function setWorkerMeanTime($time)
    {
        $this->worker_mean_time = $time;
    }
    
    
    /**
      *  Gets the worker throughput for the period, ie the
      *  mean number of jobs processed by the workers
      *
      *  @access public
      *  @return integer mean number of jobs processed
      */
    public function getWorkerMeanThroughput()
    {
        return $this->worker_mean_throughput;
    }
    
    /**
      *  Sets the worker throughput for the period, ie the
      *  mean number of jobs processed by the workers
      *
      *  @access public
      *  @param integer $throughput mean number of jobs processed
      */
    public function setWorkerMeanThroughput($throughput)
    {
        $this->worker_mean_throughput = $throughput;
    }
    
    /**
      *  Gets the maximum throughput of workers ie the
      *  max number of jobs processed by the workers
      *
      *  @access public
      *  @return integer the max jobs processed
      */
    public function getWorkerMaxThroughput()
    {
        return $this->worker_max_throughput;
    }
    
    /**
      *  Sets the maximum throughput of workers ie the
      *  max number of jobs processed by the workers
      *
      *  @access public
      *  @param integer $max the max jobs processed
      */
    public function setWorkerMaxThroughput($max)
    {
        $this->worker_max_throughput = $max;
    }
    
    /**
      *  Gets the mean utilization of the workers ie the
      *  mean throughput
      *
      *  @access public
      *  @return integer the mean throughput
      */
    public function getWorkerMeanUtilization()
    {
        return $this->worker_mean_utilization;
    }
    
    /**
      *  Gets the mean utilization of the workers ie the
      *  mean throughput
      *
      *  @access public
      *  @param integer $util the mean throughput
      */
    public function setWorkerMeanUtilization($util)
    {
        $this->worker_mean_utilization = $util;
    }
    
    //------------------------------------------------------------------
    # Queue Accessors
    
    /**
      *  Sets the number of jobs added
      *
      *  @access public
      *  @return integer the number of jobs
      */
    public function getQueueJobsAdded()
    {
        return $this->queue_no_added_jobs;
    }
    
    /**
      *  Sets the number og jobs added
      *
      *  @access public
      *  @param integer $count 
      */
    public function setQueueJobsAdded($count)
    {
        $this->queue_no_added_jobs = $count;
    }
    
    /**
      *  Gets the number of jobs that failed
      *
      *  @access public
      *  @return integer $count
      */
    public function getQueueJobsFailed()
    {
        return $this->queue_no_failed_jobs;
    }
    
    /**
      *  Sets the number of jobs failed
      *
      *  @access public
      *  @param integer $count
      */
    public function setQueueJobsFailed($count)
    {
        $this->queue_no_failed_jobs = $count;
    }
    
    /**
      *  Gets the number of jobs completed
      *
      *  @access public
      *  @return integer
      */
    public function getQueueJobsCompleted()
    {
        return $this->queue_no_completed_jobs;
    }
    
    /**
      *  Sets the number of jobs completed
      *
      *  @access public
      *  @r
      */
    public function setQueueJobsCompleted($count)
    {
        $this->queue_no_completed_jobs = $count;
    }
    
    /**
      *   Gets the number of jobs in error
      *
      *   @access public
      *   @return integer
      */
    public function getQueueJobsError()
    {
        return $this->queue_no_error_jobs;
    }
    
    /**
      *   Sets the number of jobs in error
      *
      *   @access public
      *   @param integer $count
      */
    public function setQueueJobsError($count)
    {
        $this->queue_no_error_jobs = $count;
    }
    
    /**
      *  Gets the number of jobs started in the period
      *
      *  @access public
      *  @return integer 
      */
    public function getQueueJobsProcessing()
    {
        return $this->queue_no_processing_jobs;
    }
    
    /**
      *  Sets the number of jobs started in the period
      *
      *  @access public
      *  @param integer
      */
    public function setQueueJobsProcessing($count)
    {
        $this->queue_no_processing_jobs = $count;
    }
    
     
    /**
      *   Gets the mean service time for a job
      *
      *   @access public
      *   @return integer time in seconds
      */
    public function getJobMeanServiceTime()
    {
        return $this->queue_mean_service_time;
    }
    
      /**
      *  Sets the mean service time for job
      *
      *  @access public
      *  @param integer $count time in seconds
      */
    public function setJobMeanServiceTime($count)
    {
        $this->queue_mean_service_time = $count;
    }
    
    /**
      *   Gets the minimum service time for a job
      *
      *   @access public
      *   @return integer time in seconds
      */
    public function getJobMinServiceTime()
    {
        return $this->queue_min_service_time;
    }
    
    /**
      *  Sets the minimum service time for job
      *
      *  @access public
      *  @param integer $count time in seconds
      */
    public function setJobMinServiceTime($count)
    {
        $this->queue_min_service_time = $count;
    }
    
     
    /**
      *   Gets the maximum service time for a job
      *
      *   @access public
      *   @return integer time in seconds
      */
    public function getJobMaxServiceTime()
    {
        return $this->queue_max_service_time;
    }
    
    /**
      *  Sets the maximum service time for job
      *
      *  @access public
      *  @param integer $count time in seconds
      */
    public function setJobMaxServiceTime($count)
    {
        $this->queue_max_service_time = $count;
    }
}

/* End of File */