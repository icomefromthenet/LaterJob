<?php
namespace LaterJobApi\Controllers;

use DateTime;
use Silex\Application,
    Silex\ControllerProviderInterface;
use LaterJob\Exception as LaterJobException,
    LaterJob\Model\Monitor\Stats;
use Symfony\Component\Validator\Constraints as Assert,
    Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Collections\Collection;

class MonitorProvider extends BaseProvider implements ControllerProviderInterface
{
    
    const QUERY_LIMIT = 1000;
    
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/monitoring', array($this,'getMonitoring'));
        
        return $controllers;
    }
    
    public function getMonitoring(Application $app, Request $req)
    {
        $format = $app['laterjob.api.formatters.monitor'];
        $data   = array(
            'result' => array(),
            'msg' => null
        );  

        $code = 200;
       
        try {
            
            $validator = $app['validator'];
        
            # gater query params
            
            $offset = $req->get('offset',0);
            $limit  = $req->get('limit',self::QUERY_LIMIT);
            $order  = $req->get('order','asc');
            $before = $req->get('before');
            $after  = $req->get('after');
        
            # filter query params and assign default values
            $constraint = new Assert\Collection(array(
                                'offset' => new Assert\Range(array('min' =>0 ,'max' => PHP_INT_MAX)),
                                'limit'  => new Assert\Range(array('min' =>1 ,'max' =>self::QUERY_LIMIT)),
                                'order'  => new Assert\Choice(array( 'choices' => array('desc','asc') )),
                                'before' => new Assert\DateTime(),
                                'after'  => new Assert\DateTime()
            ));
            
            
            $errors = $app['validator']->validateValue(array(
                                                 'offset' => $offset,
                                                 'limit'  => $limit,
                                                 'order'  => $order,
                                                 'before' => $before,
                                                 'after'  => $after
                                                  ), $constraint);
            
            if (count($errors) > 0) {
                throw new LaterJobException($this->serializeValidationErrors($errors));
            }
            
            if($before !== null) {
                $before = new DateTime($before);    
            }
            
            if($after !== null) {
                $after = new DateTime($after);
            }
            
            # run against api
            $result      = $app[$this->index]->monitor()->query((int)$offset,(int)$limit,$order,$after,$before);
            $data['msg'] = true;
            
            # convert using formatter if known type.
            if($result instanceof Collection) {
                $data['result'] = $format->toArrayCollection($result);
            } elseif($result instanceof Stats) {
                $data['result'] = array($format->toArray($result));
            } elseif($result === null || $result === false) {
                $data['result'] = array();
            } else {
                throw new LaterJobException('return data is in an unknown type : ' . gettype($result));
            }            
            
        } catch(LaterJobException $e) {
            $data['msg'] = $e->getMessage();
            $code = 500;
            $app['monolog']->notice($e->getMessage());
        } catch (\Exception $e) {
            $data['msg'] = $e->getMessage();
            $code = 500;
            $app['monolog']->notice($e->getMessage());
        }
        
        return $this->response($data,$code);
        
    }
    
}
/* End of File */