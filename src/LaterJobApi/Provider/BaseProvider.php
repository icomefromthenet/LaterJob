<?php
namespace LaterJobApi\Provider;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\JsonResponse;
use LaterJob\Exception as LaterJobException;
use LaterJobApi\Controllers\BaseController;

abstract class BaseProvider implements ControllerProviderInterface
{
    
    protected $index;
    
   
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param string $queue_index
      */
    public function __construct($queue_index)
    {
        $this->index = $queue_index;
    }
    
    
    //------------------------------------------------------------------
    # ControllerProviderInterface
    
    abstract protected function doGetController(Application $app, $queue_index);
    
    
    abstract protected function doConnect(Application $app, BaseController $controller);
    
    
    public function connect(Application $app)
    {
       
        $oController = $this->doGetController($app, $this->index);
       
        # bind errro handler
        $app->error(array($oController,'handleError'));
        
        return $this->doConnect($app, $oController);
    }
    
    
   
    
    
   
    
  
}
/* End of File */