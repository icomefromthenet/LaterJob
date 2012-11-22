<?php
namespace Migration\Components\Migration\Entities;

use Doctrine\DBAL\Connection,
    Doctrine\DBAL\Schema\AbstractSchemaManager as Schema,
    Migration\Components\Migration\EntityInterface,
    Doctrine\DBAL\Schema\Schema as SchemaBuilder;

class init_schema implements EntityInterface
{

    public function getTransitionTable(SchemaBuilder $builder)
    {
        $table = $builder->createTable("later_job_transition"); 
        
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
        
    }

    
    public function getQueueTable(SchemaBuilder $builder)
    {
        $table = $builder->createTable("later_job_queue"); 
        
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
        
    }

    public function getMonitoringTable(SchemaBuilder $builder)
    {
        $table = $builder->createTable("later_job_monitor");
        
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
        $table->addColumn('worker_mean_utilization','integer',array("unsigned" => true, 'notnull' => false));
        
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

        
    }


    public function up(Connection $db, Schema $sc)
    {
        $builder = new SchemaBuilder();
        
        $this->getTransitionTable($builder);
        $this->getQueueTable($builder);
        $this->getMonitoringTable($builder);
        
        $queries = $builder->toSql($db->getDatabasePlatform());
        
        foreach($queries as $query){
            $db->exec($query);
        }

    }

    public function down(Connection $db, Schema $sc)
    {
        $builder = new SchemaBuilder();
        
        $this->getTransitionTable($builder);
        $this->getQueueTable($builder);
        $this->getMonitoringTable($builder);
        
        $queries = $builder->toDropSql($db->getDatabasePlatform());
        
        foreach($queries as $query){
            $db->exec($query);
        }

    }


}
/* End of File */
