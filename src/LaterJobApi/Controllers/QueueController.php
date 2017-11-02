<?php
namespace LaterJobApi\Controllers;

use DateTime;
use Silex\Application;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use LaterJob\Config\QueueConfig;
use LaterJob\Model\Queue\Storage;
use LaterJob\Formatter\JobFormatter;
use LaterJob\Exception as LaterJobException;
use Doctrine\Common\Collections\Collection;
    
class QueueController extends BaseController 
{
    
    const QUERY_LIMIT = 500;
    
    
    
    public function lookupJob($job)
    {
        $result = $this->getQueue()->lookup($job);
            
        if(!$result instanceof Storage) {
            $this->getContainer()->abort(404,'Job not found at id '.$job);
        }
        
        return $result;
    }
    
    
    /**
      *  Will Query the Queue for a single job
      *
      *  @access public
      *  @return Response
      */
    public function getJobAction(Application $app, Request $req, Storage $job)
    {
        $data = array(
            'result' => array(),
            'msg' => null
        );  
        
        $data['result'] = array($this->getJobFormatter()->toArray($job));
        $data['msg']    = true;
        
        return $this->response($data,200);
    }

    /**
      *  Will Query the delete a single job
      *
      *  @access public
      *  @return Response
      */
    public function deleteJobAction(Application $app, Request $req , Storage $job)
    {
         $data = array(
            'result' => array(),
            'msg' => null
        );  
            
        # run against api
        $data['result'] = $this->getQueue()->remove($job->getJobId(), new DateTime());
        $data['msg']    = true;    
        
        return $this->response($data,200);
        
    }
    
    /**
      *  Will Run a purge on the queue
      *
      *  @access public
      *  @return Response
      */
    public function deleteJobsAction(Application $app, Request  $req)
    {
         $data = array(
            'result' => array(),
            'msg' => null
        );  
            
        $before     = $req->get('before');
        
        $constraint = new Assert\Collection(array('before' => new Assert\DateTime()));
            
        $errors     = $this->getValidator()->validateValue(array('before' => $before), $constraint);
            
        if (count($errors) > 0) {
            $this->getContainer()->abort(400,$this->serializeValidationErrors($errors));
        }
            
        # run against api
        $data['result'] = $this->getQueue()->purge(new DateTime($before));
        $data['msg']    = true;
            
        
        return $this->response($data,200);
    }
    
    /**
      *  Will Query the Queue for multiple jobs
      *
      *  @access public
      *  @return Response
      */
    public function getJobsAction(Application $app, Request $req)
    {
        $data = array(
            'result' => array(),
            'msg' => null
        );  

        
        # gater query params
            
        $offset = $req->get('offset',0);
        $limit  = $req->get('limit',self::QUERY_LIMIT);
        $order  = $req->get('order','asc');
        $state  = $req->get('state');
        $before = $req->get('before');
        $after  = $req->get('after');
        
        # filter query params and assign default values
        $constraint = new Assert\Collection(array(
                            'offset' => new Assert\Range(array('min' =>0 ,'max' => PHP_INT_MAX)),
                            'state'  => new Assert\Choice(array('choices' => array(
                                                                                null,
                                                                                QueueConfig::STATE_ADD,
                                                                                QueueConfig::STATE_ERROR,
                                                                                QueueConfig::STATE_FAIL,
                                                                                QueueConfig::STATE_FINISH,
                                                                                QueueConfig::STATE_START
                                                                                ))),
                            'limit'  => new Assert\Range(array('min' =>1 ,'max' =>self::QUERY_LIMIT)),
                            'order'  => new Assert\Choice(array( 'choices' => array('desc','asc') )),
                            'before' => new Assert\DateTime(),
                            'after'  => new Assert\DateTime()
                    ));
            
            
        $errors = $this->getValidator()->validateValue(array(
                                            'state'  => $state,
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
        $result      = $this->getQueue()->query((int)$offset,(int)$limit,$state,$order,$before,$after);
        $data['msg'] = true;
            
        # convert using formatter if known type.
        if($result instanceof Collection) {
            $data['result'] = $this->getJobFormatter()->toArrayCollection($result);
        } elseif($result instanceof Storage) {
            $data['result'] = array($this->getJobFormatter()->toArray($result));
        } elseif($result === null || $result === false) {
            $data['result'] = array();
        } else {
            $this->getContainer()->abort(500,'return data is in an unknown type : ' . gettype($result));
        }            

        return $this->response($data,200);
    }
    
    
}
/* End of File */