<?php
namespace LaterJobApi\Tests;

use PDO;
use DBALGateway\Tests\Base\DBOperationSetEnv;
use LaterJob\Config\DbMetaConfig;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernel;
use PHPUnit\DbUnit\Operation\Composite;
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\Operation\Factory;
use PHPUnit\DbUnit\TestCaseTrait;


class TestsWithFixture extends TestCase
{
    
    use TestCaseTrait { setUp as protected traitSetUp; }
    
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
        return  $this->createXMLDataSet(__DIR__ . DIRECTORY_SEPARATOR . 'fixture.xml');
    }
    
    
    //------------------------------------------------------------------
    # WebTestCase Implementation
    
    protected $app;

    /**
     * PHPUnit setUp for setting up the application.
     *
     * Note: Child classes that define a setUp method must call
     * parent::setUp().
     */
    public function setUp()
    {
        // Invokes PHPUnit_Extensions_Database_TestCase_Trait::traitSetUp() (nee setUp),
        // which in turn calls PHPUnit_Framework_TestCase::setUp()
        $this->traitSetUp();
        
        $this->app = $this->createApplication();
    }

    /**
     * Creates the application.
     *
     * @return HttpKernel
     */
    public function createApplication()
    {
       
    }

    /**
     * Creates a Client.
     *
     * @param array $server An array of server parameters
     *
     * @return Client A Client instance
     */
    public function createClient(array $server = array())
    {
        return new Client($this->app, $server);
    }

      
}
/* End of File */