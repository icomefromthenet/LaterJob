<?php
namespace LaterJob\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use DBALGateway\Metadata\Table;
use LaterJob\Exception as LaterJobException;

/**
  *  Contains metadata for the DB Model
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class DbMeta implements ConfigurationInterface
{
    
    /**
      *  @var string the database table name
      */    
    protected $transition_table;
    
    /**
      *  @var string the database table name 
      */    
    protected $queue_table;
    
    /**
      *  @var string the database table name
      */    
    protected $monitor_table;
    
    
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
            $values = $processor->processConfiguration($this,$config);
            
            # bind to this object
            $this->transition_table = $values['transition_table'];
            $this->queue_table      = $values['queue_table'];
            $this->monitor_table    = $values['monitor_table'];
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
        $rootNode = $treeBuilder->root('db');

        
        
        #node definitions to the root of the tree
        $rootNode->ignoreExtraKeys()
            ->children()
                ->scalarNode('transition_table')
                    ->cannotBeEmpty()
                    ->defaultValue('later_job_transition')
                ->end()
                ->scalarNode('queue_table')
                    ->cannotBeEmpty()
                    ->defaultValue('later_job_queue')
                ->end()
                ->scalarNode('monitor_table')
                    ->cannotBeEmpty()
                    ->defaultValue('later_job_monitor')
                ->end()
            ->end();

        return $treeBuilder;
    }

    
    //------------------------------------------------------------------
    # Accessors
    
    
    public function getTransitionTableName()
    {
        return $this->transition_table;
    }
    
    public function getMonitorTableName()
    {
        return $this->monitor_table;
    }
    
    public function getQueueTableName()
    {
        return $this->queue_table;
    }
    
    //------------------------------------------------------------------
    # MetaData Builders
        
    public function getTransitionTable()
    {
        $table = new Table($this->transition_table);
        
        # setup pk
        $table->addColumn('transition_id','integer',array("unsigned" => true,'autoincrement' => true));
        $table->setPrimaryKey(array("transition_id"));
        
        # column for worker Global UUID
        $table->addColumn('worker_id','string',array('length'=> 36,'notnull' => false));
        $table->addIndex(array('worker_id'));
        
        # column for job Global UUID
        $table->addColumn('job_id','string',array('length'=> 36,'notnull' => false));
        $table->addIndex(array('job_id'));
        
        # column for transition state
        $table->addColumn('state_id','integer',array('unsigned'=> true ,'notnull' => true));
        
        # Job Process Handler
        $table->addColumn('process_handle','string',array('length'=> 36,'notnull' => false));
        
        # column dte of transition
        $table->addColumn('dte_occured','datetime',array('notnull' => true));
        
        # Optional transitional message
        $table->addColumn('transition_msg','string',array('length' => 200,'notnull'=> false));
        
        
        # VColumn state_count used in agg count queries of the job state
        $table->addVirtualColumn('state_count','integer',array("unsigned" => true));
        
        return $table;
        
    }

    
    public function getQueueTable()
    {
        $table = new Table($this->queue_table); 
        
        # setup pk
        $table->addColumn('job_id','string',array('length'=> 36,'notnull' => false));
        $table->setPrimaryKey(array("job_id"));
        
        # current state
        $table->addColumn('state_id','integer',array('unsigned'=> true , 'notnull' => true));
        
        # date added to queue
        $table->addColumn('dte_add','datetime',array('notnull' => true));
        
        # retry count
        $table->addColumn('retry_count','integer',array('default' => 0,'unsigned' => true));
        
        # retry timer
        $table->addColumn('retry_last','datetime',array('notnull' => false));
        
        # data blob for this job.
        $table->addColumn('job_data','object',array());
        
        # lock information
        $table->addColumn('handle','string',array('length' => 36, 'notnull' => false));
        $table->addIndex(array('handle'));
        $table->addColumn('lock_timeout','datetime',array('notnull' => false));
        
        $table->addVirtualColumn('job_count','integer',array('unsigned' => true));
        
        return $table;
    }

    public function getMonitorTable()
    {
        $table = new Table($this->monitor_table);
        
        # setup pk
        $table->addColumn('monitor_id','integer',array("unsigned" => true,'autoincrement' => true));
        $table->setPrimaryKey(array("monitor_id"));
        
        # date and hour
        $table->addColumn('monitor_dte','datetime',array());
        
         # add index    
        $table->addUniqueIndex(array('monitor_dte'));  
        
        # worker stats
        $table->addColumn('worker_max_time','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('worker_min_time','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('worker_mean_time','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('worker_mean_throughput','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('worker_max_throughput','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('worker_mean_utilization','float',array("unsigned" => true, 'notnull' => false));
        
        # queue stats
        $table->addColumn('queue_no_waiting_jobs','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('queue_no_failed_jobs','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('queue_no_error_jobs','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('queue_no_completed_jobs','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('queue_no_processing_jobs','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('queue_mean_service_time','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('queue_min_service_time','integer',array("unsigned" => true, 'notnull' => false));
        $table->addColumn('queue_max_service_time','integer',array("unsigned" => true, 'notnull' => false));
            
         $table->addColumn('monitor_complete','boolean',array('default' => false));     
                

        return $table;
    }
    
    
}

/* End of File */