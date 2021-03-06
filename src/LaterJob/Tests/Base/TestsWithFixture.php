<?php
namespace LaterJob\Tests\Base;

use PDO;
use DBALGateway\Tests\Base\DBOperationSetEnv;
use LaterJob\Config\DbMetaConfig;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit\DbUnit\Operation\Composite;
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\Operation\Factory;
use PHPUnit\DbUnit\TestCaseTrait;


class TestsWithFixture extends TestCase
{
    
   use TestCaseTrait;    
    
    //  ----------------------------------------------------------------------------
    
    /**
      *  @var PDO  only instantiate pdo once for test clean-up/fixture load
      *  @access private
      */ 
    static private $pdo = null;

    /**
      *  @var  \Doctrine\DBAL\Connection
      *  @access private
      */
    static private $doctrine_connection;
    
    /**
      *  @var PHPUnit_Extensions_Database_DB_IDatabaseConnection only instantiate once per test
      *  @access private
      */
    private $conn = null;
    
    
    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }

    
    protected function getSetUpOperation()
    {
        return new Composite([
            new DBOperationSetEnv('foreign_key_checks',0),
            Factory::CLEAN_INSERT(),
            new DBOperationSetEnv('foreign_key_checks',1),
        ]);
    }
    
    
    public function getDataSet()
    {
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture'. DIRECTORY_SEPARATOR .'fixture.xml');
    }
    
    
    /**
    * Gets a db connection to the test database
    *
    * @access public
    * @return \Doctrine\DBAL\Connection
    */
    public function getDoctrineConnection()
    {
        if(self::$doctrine_connection === null) {
        
            $config = new \Doctrine\DBAL\Configuration();
            
            $connectionParams = array(
                'dbname' => $GLOBALS['DB_DBNAME'],
                'user' => $GLOBALS['DB_USER'],
                'password' => $GLOBALS['DB_PASSWD'],
                'host' => $GLOBALS['DB_HOST'],
                'driver' => 'pdo_mysql',
            );
        
           self::$doctrine_connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        }
        
        return self::$doctrine_connection;
        
    }
    
        
    protected $db_meta_data;
    
    /**
      *  Loads the metadata for database
      *
      *  @return Doctrine/DBAL/Schema/Table
      */
    protected function getTableMetaData()
    {
        if($this->db_meta_data === null) {
            $this->db_meta_data = new DbMetaConfig();
            $this->db_meta_data->parse(array('db'=>array()));
        }
        
        return $this->db_meta_data;
    }
    
    
    //------------------------------------------------------------------
    # MonoLog
    
    /**
      *  @var Monolog\Logger; 
      */        
    protected $monolog_logger;
    
    /**
      *  Creats a single instance of monolog
      *
      *  @access public
      *  @return Monolog\Logger
      */
    public function getMonolog()
    {
        if($this->monolog_logger === null) {
            $this->monolog_logger = new Logger('mysql');
            $this->monolog_logger->pushHandler(new StreamHandler('/var/tmp/laterjob.log'));
        }
        
        return $this->monolog_logger;
    }
    
}
/* End of File */