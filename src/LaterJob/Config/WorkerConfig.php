<?php
namespace LaterJob\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use ReflectionClass;
use LaterJob\Exception as LaterJobException;

/**
  *  Configration Definition for a worker script
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class WorkerConfig implements ConfigurationInterface
{
    
    /**
      *  @var integer the Start State Value
      */
    const STATE_START = 1;
    
    /**
      *  @var integer the FINISH State Value
      */
    const STATE_FINISH = 2;
    
    /**
      *  @var integer the Error State Value
      */
    const STATE_ERROR = 3;
    
    
    /**
      *  @var integer the number of jobs to process 
      */
    protected $jobs_process;
    
    /**
      *  @var integer the mean run time of the worker (MS)
      *  
      */
    protected $mean_runtime;
    
    /**
      *  @var string the cron value used to start the worker used to calculate the
      *  maxium throughput of the queue in a given unit of time.
      */
    protected $cron_script;
    
    /**
      *  @var integer the number of MS to lock a job
      */
    protected $job_lock_timeout;
    
    /**
      *  @var string name for the worker 
      */
    protected $work_name;
    
    
    public function parse(array $config)
    {
        
        try {
            # process with a configuration
            $processor = new Processor();
            $values = $processor->processConfiguration($this,$config);
        
            # bind to this object
            $this->jobs_process      = $values['jobs_process'];
            $this->mean_runtime      = $values['mean_runtime'];
            $this->cron_script       = $values['cron_script'];
            $this->job_lock_timeout  = $values['job_lock_timeout'];
            $this->work_name         = $values['worker_name'];    
            
        }
        catch(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $this;
        
    }
    
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('worker');

        #node definitions to the root of the tree
        
        $rootNode->ignoreExtraKeys()
            ->children()
                ->scalarNode('jobs_process')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function($v){
                            return !is_int($v) || $v <= 0; 
                        })
                        ->thenInvalid('Jobs to Process must be an integer with value greater than 0')
                    ->end()
                ->end()
                ->scalarNode('mean_runtime')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function($v){
                            return !is_int($v) || $v <= 0; 
                        })
                        ->thenInvalid('Run Time must be an integer with value greater than 0')
                    ->end()
                ->end()
                ->scalarNode('cron_script')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function($v){
                            $parts = explode(' ', $v);
                            $result = false;
                            if (5 != count($parts)) {
                                $result = true;
                            }
                            return $result;
                        })
                        ->thenInvalid('Cron Script %s Failed to Parse')
                    ->end()
                ->end()
                ->scalarNode('job_lock_timeout')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function($v){
                            return !is_int($v) || $v <= 0; 
                        })
                        ->thenInvalid('Job Lockout Time must be an integer with value greater than 0')
                    ->end()
                ->end()
                ->scalarNode('worker_name')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
    
    //------------------------------------------------------------------
    # Util
    
    /**
     * Gets the literal for a given state.
     *
     * @param integer $token
     * @return string
     */
    public function getLiteral($state)
    {
        $class_name = get_called_class();
        $refl_class = new ReflectionClass($class_name);
        $constants = $refl_class->getConstants();

        foreach ($constants as $name => $value) {
            if ($value === $state) {
                return $class_name . '::' . $name;
            }
        }

        return $state;
    }
    
    
    //------------------------------------------------------------------
    # Accessors
    
    /**
      *  Return the number of jobs to process inside this worker
      *
      *  @access public
      *  @return integer the jobs to process
      */
    public function getJobsToProcess()
    {
        return $this->jobs_process;
    }
    
    /**
      *  The service time taken for each job
      *
      *  @access public
      *  @return integer the service time for each job
      */
    public function getMeanRuntime()
    {
        return $this->mean_runtime;        
    }
    
    /**
      *  Return the cron script used to instance this worker
      *
      *  @access public
      *  @return string the cron definition 
      */
    public function getCronDefinition()
    {
        return $this->cron_script;        
    }
    
    /**
      *  Return the number of MS to lock a job for
      *
      *  @access public
      *  @return integer the timeout in MS
      */
    public function getJobLockoutTime()
    {
        return $this->job_lock_timeout;
    }
    
    /**
      *  Return the name of the worker
      *
      *  @access public
      *  @return string the worker name
      */
    public function getWorkerName()
    {
        return $this->work_name;
    }
    
}
/* End of File */