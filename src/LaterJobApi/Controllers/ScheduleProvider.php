<?php
namespace LaterJobApi\Controllers;

use DateTime;
use Silex\Application,
    Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Validator\Constraints as Assert;
use LaterJob\Exception as LaterJobException;

class ScheduleProvider extends BaseProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
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
        $code = 200;
        
        
        try {
            
            $validator = $app['validator'];
            
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
            
            
            $errors = $app['validator']->validateValue(array('now' => $now,'iterations'  => $iterations,), $constraint);
            
            if (count($errors) > 0) {
                throw new LaterJobException($this->serializeValidationErrors($errors));
            }
            
            $response['result'] = $app[$this->index]->schedule(new DateTime($now),$iterations);
            
        
        } catch(\Exception $e) {
            $code = 500;
            $response['msg'] = $e->getMessage();
            $response['result'] = array();
            $app['monolog']->notice($e->getMessage());
        }
        
        
        return $this->response($response,$code);
        
    }
    
}
/* End of File */