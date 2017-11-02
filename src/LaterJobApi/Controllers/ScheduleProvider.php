<?php
namespace LaterJobApi\Controllers;

use DateTime;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use LaterJob\Exception as LaterJobException;

class ScheduleProvider extends BaseProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        parent::connect($app);
        
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/schedule', array($this,'getScheduleAction'));

        return $controllers;
    }
    
    
    public function getScheduleAction(Application $app, Request $req)
    {
        $response = array(
            'msg'    => null,
            'result' => null
        );
        
        
        if(($now = $req->get('now')) === null) {
                $now = date('Y-m-s H:m:s');
        }
            
        if(($iterations = $req->get('iterations')) === null) {
            $iterations = 10;
        }
            
        # filter query params and assign default values
        $constraint = new Assert\Collection(array(
                            'now'        => new Assert\DateTime(),
                            'iterations' => new Assert\Range(array('min' =>1 ,'max' =>100)),
                    ));
            
            
        $errors = $this->getValidator()->validateValue(array('now' => $now,'iterations'  => $iterations,), $constraint);
            
        if (count($errors) > 0) {
            $this->getContainer()->abort(400,$this->serializeValidationErrors($errors));
        }
            
        $response['result'] = $this->getQueue()->schedule(new DateTime($now),$iterations);
        
        return $this->response($response,200);
        
    }
    
}
/* End of File */