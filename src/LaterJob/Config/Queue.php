<?php
namespace LaterJob\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use ReflectionClass;
use LaterJob\Exception as LaterJobException;

/**
  *  Config options for the queue
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Queue implements ConfigurationInterface
{
   /**
     *  @var integer inital state of a job 
     */
   const STATE_ADD   = 1;
   
   /**
     *  @var integer when job has been started by worker
     */
   const STATE_START = 2;
   
   /**
     *  @var  integer job has finished process by worker
     */
   const STATE_FINISH  = 3;
   
   /**
     *  @var integer job unable to be processed and needs to retry 
     */
   const STATE_ERROR   = 4;
    
   /**
     *  @var integer when job failed max retry event 
     */ 
   const STATE_FAIL  = 5;
    
    /**
      *  @var integer the mean service time for a job to complete in MS
      *  the service time is the distance between when job add to queue and
      *  the time the job left the queue as processed.
      */
    protected $mean_service_time;
    
    /**
      *  @var integer the maxium number of retries of a job 
      */
    protected $max_retry;
    
    /**
      *  @var integer amount of time to wait in seconds before retry the job
      */
    protected $retry_timer;
    
    /**
      *  Parse and validate a config array
      *
      *  @access public
      *  @param array $config
      */
    public function parse(array $config)
    {
        try {
        
            # process with a configuration
            $processor = new Processor();
            $values    = $processor->processConfiguration($this,$config);
            
            # bind to this object
            $this->mean_service_time = $values['mean_service_time'];
            $this->max_retry         = $values['max_retry'];
            $this->retry_timer       = $values['retry_timer'];
        
        }
        catch(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
        return $this;
        
    }
    
    /**
      *   Creates a config configuration
      *
      *   @access public
      *   @return TreeBuilder
      */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('queue');

        #node definitions to the root of the tree
        
        $rootNode->ignoreExtraKeys()
            ->children()
                ->scalarNode('mean_service_time')
                    ->cannotBeEmpty()
                    ->isRequired()
                    ->validate()
                        ->ifTrue(function($v){
                            return !is_int($v) || $v <= 0;
                        })
                        ->thenInvalid('Mean Job Service Time must be an integer with a value greater than 0')
                    ->end()
                ->end()
                ->scalarNode('max_retry')
                    ->defaultValue(3)
                    ->validate()
                        ->ifTrue(function($v){
                            return !is_int($v) || $v < 0; 
                        })
                        ->thenInvalid('Max Job Retry must an integer with a value of 0 or more')
                    ->end()
                ->end()
                ->scalarNode('retry_timer')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function($v){
                            return !is_int($v) || $v <= 0;
                        })
                        ->thenInvalid('Job Retry Timmer must be an integer with a value greater than 0')
                    ->end()
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
    # Properties
    
    /**
      *  Return the assigned job_builder
      *
      *  @access public
      *  @return string 
      */
    public function getJobEntityBuilder()
    {
        
        return $this->job_builder;
    }
    
    /**
      *  Return the assigned JobCollection class
      *
      *  @access public
      *  @return string
      */
    public function getJobCollectionClass()
    {
        return $this->job_collection;    
    }
    
    /**
      *  Return the maxium number of retrys for a error job
      *
      *  @access public
      *  @return integer
      */
    public function getMaxRetry()
    {
        return $this->max_retry;
    }
    
    /**
      *  Return the mean service time
      *
      *  @access public
      *  @return integer time in ms
      */
    public function getMeanServiceTime()
    {
        return $this->mean_service_time;
    }
    
    /**
      *  Return the time to wait before starting an error job again
      *
      *  @access public
      *  @return integer
      */
    public function getRetryTimer()
    {
        return $this->retry_timer;
    }
    
}
/* End of File */