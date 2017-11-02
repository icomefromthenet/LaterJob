<?php
namespace LaterJobApi\Provider;

use DateTime;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use LaterJob\Exception as LaterJobException;
use LaterJobApi\Controllers\ScheduleController;
use LaterJobApi\Controllers\BaseController;

class ScheduleProvider extends BaseProvider implements ControllerProviderInterface
{
    
    
    protected function doGetController(Application $app, $queue_index)
    {
        return new ScheduleController($queue_index, $app);
    }
    
    
    protected function doConnect(Application $app, BaseController $controller)
    {
         // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/schedule', array($controller,'getScheduleAction'));

        return $controllers;
    }
    
    
    
}
/* End of File */