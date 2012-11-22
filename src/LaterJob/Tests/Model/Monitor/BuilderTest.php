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
        
        
        $entity->setMonitorId($monitor_id);
        $entity->setMonitorDate($monitor_date);
        $entity->setWorkerMaxTime($worker_max_runtime);
        $entity->setWorkerMinTime($worker_min_runtime);
        $entity->setWorkerMeanTime($worker_mean_runtime);
        $entity->setWorkerMeanThroughput($worker_mean_throughput);
        $entity->setWorkerMaxThroughput($worker_max_throughput);
        $entity->setWorkerMeanUtilization($worker_mean_utilization);
        
        
        
        
        $this->assertEquals($monitor_id,$entity->getMonitorId());
        $this->assertEquals($monitor_date,$entity->getMonitorDate());
        $this->assertEquals($worker_max_runtime,$entity->getWorkerMaxTime());
        $this->assertEquals($worker_min_runtime,$entity->getWorkerMinTime());
        $this->assertEquals($worker_mean_runtime,$entity->getWorkerMeanTime());
        $this->assertEquals($worker_mean_throughput,$entity->getWorkerMeanThroughput());
        $this->assertEquals($worker_max_throughput,$entity->getWorkerMaxThroughput());
        $this->assertEquals($worker_mean_utilization,$entity->getWorkerMeanUtilization());
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
        
        $data = array (
           'monitor_id'              => $monitor_id,
           'monitor_dte'             => $monitor_date,
           'worker_max_time'         => $worker_max_runtime,
           'worker_min_time'         => $worker_min_runtime,
           'worker_mean_time'        => $worker_mean_runtime,
           'worker_max_throughput'   => $worker_max_throughput,
           'worker_mean_throughput'  => $worker_mean_throughput,
           'worker_mean_utilization' => $worker_mean_utilization,
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
        
        
        $entity->setMonitorId($monitor_id);
        $entity->setMonitorDate($monitor_date);
        $entity->setWorkerMaxTime($worker_max_runtime);
        $entity->setWorkerMinTime($worker_min_runtime);
        $entity->setWorkerMeanTime($worker_mean_runtime);
        $entity->setWorkerMeanThroughput($worker_mean_throughput);
        $entity->setWorkerMaxThroughput($worker_max_throughput);
        $entity->setWorkerMeanUtilization($worker_mean_utilization);
        
        
        $builder = new StatsBuilder();
        
        $data = $builder->demolish($entity);
        
        $this->assertEquals($data, array (
           'monitor_id'              => $monitor_id,
           'monitor_dte'             => $monitor_date,
           'worker_max_time'         => $worker_max_runtime,
           'worker_min_time'         => $worker_min_runtime,
           'worker_mean_time'        => $worker_mean_runtime,
           'worker_max_throughput'   => $worker_max_throughput,
           'worker_mean_throughput'  => $worker_mean_throughput,
           'worker_mean_utilization' => $worker_mean_utilization,
        ));
    }
    
    
}
/* End of File */