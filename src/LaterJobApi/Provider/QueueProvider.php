<?php
namespace LaterJobApi\Provider;

use DateTime;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use LaterJob\Exception as LaterJobException;
use Doctrine\Common\Collections\Collection;
use LaterJobApi\Controllers\QueueController;
use LaterJobApi\Controllers\BaseController;
    
class QueueProvider extends BaseProvider implements ControllerProviderInterface
{
    
    
    protected function doGetController(Application $app, $queue_index)
    {
        return new QueueController($queue_index, $app);
    }
    
    
    protected function doConnect(Application $app, BaseController $controller)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/jobs/{job}', array($controller,'getJobAction'))
                    ->assert('job', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')
                    ->convert('job',array($controller,'lookupJob'));
                    
        $controllers->get('/jobs', array($controller,'getJobsAction'));

        $controllers->delete('/jobs/{job}', array($controller,'deleteJobAction'))
                    ->assert('job', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')
                    ->convert('job',array($controller,'lookupJob'));
                    
        $controllers->delete('/jobs', array($controller,'deleteJobsAction'));

        return $controllers;
    }
    
    
}
/* End of File */