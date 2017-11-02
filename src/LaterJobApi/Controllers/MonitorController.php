<?php
namespace LaterJobApi\Controllers;

use DateTime;
use Silex\Application;
use LaterJob\Exception as LaterJobException;
use LaterJob\Model\Monitor\Stats;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;

class MonitorController extends BaseController 
{
    
    const QUERY_LIMIT = 1000;
    
    
    public function getMonitoring(Application $app, Request $req)
    {
        $data   = array(
            'result' => array(),
            'msg'    => null
        );  

        # gather query params
            
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
            
            
        $errors = $this->getValidator()->validateValue(array(
                                                'offset' => $offset,
                                                'limit'  => $limit,
                                                'order'  => $order,
                                                'before' => $before,
                                                'after'  => $after
                                            ), $constraint);
            
        if (count($errors) > 0) {
            $this->getContainer()->abort(400,$this->serializeValidationErrors($errors));
        }
            
        if($before !== null) {
            $before = new DateTime($before);    
        }
            
        if($after !== null) {
            $after = new DateTime($after);
        }
            
        # run against api
        $result      = $this->getQueue()->monitor()->query((int)$offset,(int)$limit,$order,$after,$before);
        $data['msg'] = true;
            
        # convert using formatter if known type.
        if($result instanceof Collection) {
            $data['result'] = $this->getMonitorFormatter()->toArrayCollection($result);
        } elseif($result instanceof Stats) {
            $data['result'] = array($this->getMonitorFormatter()->toArray($result));
        } elseif($result === null || $result === false) {
            $data['result'] = array();
        } else {
            $this->getContainer()->abort(500,'return data is in an unknown type : ' . gettype($result));
        }            
            
        return $this->response($data,200);
        
    }
    
}
/* End of File */