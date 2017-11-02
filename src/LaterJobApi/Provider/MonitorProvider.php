<?php
namespace LaterJobApi\Provider;

use DateTime;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use LaterJob\Exception as LaterJobException;
use LaterJob\Model\Monitor\Stats;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;
use LaterJobApi\Controllers\MonitorController;
use LaterJobApi\Controllers\BaseController;

class MonitorProvider extends BaseProvider implements ControllerProviderInterface
{
   
    
    protected function doGetController(Application $app, $queue_index)
    {
        return new MonitorController($queue_index, $app);
    }
    
    
    protected function doConnect(Application $app, BaseController $controller)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/monitoring', array($controller,'getMonitoring'));

        return $controllers;
    }
    
    
}
/* End of File */