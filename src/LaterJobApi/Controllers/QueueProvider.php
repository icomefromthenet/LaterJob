<?php
namespace LaterJobApi\Controllers;

use DateTime;
use Silex\Application,
    Silex\ControllerProviderInterface;
use Symfony\Component\Validator\Constraints as Assert,
    Symfony\Component\HttpFoundation\Request;
use LaterJob\Config\QueueConfig,
    LaterJob\Model\Queue\Storage;
use LaterJob\Formatter\JobFormatter,
    LaterJob\Exception as LaterJobException;
use Doctrine\Common\Collections\Collection;
    
class QueueProvider extends BaseProvider implements ControllerProviderInterface
{
    
    const QUERY_LIMIT = 500;
    
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/jobs/{job_id}', array($this,'getJobAction'))->assert('job_id', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}');
        $controllers->get('/jobs', array($this,'getJobsAction'));

        $controllers->delete('/jobs/{job_id}', array($this,'deleteJobAction'))->assert('job_id', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}');
        $controllers->delete('/jobs', array($this,'deleteJobsAction'));
        
        return $controllers;
    }
    
    
    
    /**
      *  Will Query the Queue for a single job
      *
      *  @access public
      *  @return Response
      */
    public function getJobAction(Application $app, Request $req, $job_id)
    {
        $format = $app['laterjob.api.formatters.job'];
        $data = array(
            'result' => array(),
            'msg' => null
        );  
        $code = 200;
        
         
        try {
            
            # run against api
            $result = $app[$this->index]->lookup($job_id);
            $data['msg']    = '';
            
            # convert using formatter if known type.
            if($result instanceof Collection) {
                $data['result'] = $format->toArrayCollection($result);
            } elseif($result instanceof Storage) {
                $data['result'] = array($format->toArray($result));
            } elseif($result === null || $result === false) {
                $data['result'] = array();
            } else {
                throw new LaterJobException('return data is in an unknown type : ' . gettype($result));
            }
            
            if($result === false) {
                $code = 404;
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

    /**
      *  Will Query the delete a single job
      *
      *  @access public
      *  @return Response
      */
    public function deleteJobAction(Application $app, Request $req , $job_id)
    {
         $data = array(
            'result' => array(),
            'msg' => null
        );  

        $code = 200;
        
        try {
            
            if(($result = $app[$this->index]->lookup($job_id)) === false) {
                $data['result'] = false;
                $code = 404;
            } else {
                # run against api
                $data['result'] = $app[$this->index]->remove($job_id, new DateTime());
                $data['msg']    = true;    
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
        $code = 200;
        
        try {
            
            $validator  = $app['validator'];
            $before     = $req->get('before');
            $constraint = new Assert\Collection(array('before' => new Assert\DateTime(),));
            
            $errors     = $app['validator']->validateValue(array('before' =>$before), $constraint);
            
            if (count($errors) > 0) {
                throw new LaterJobException($this->serializeValidationErrors($errors));
            }
            
            # run against api
            $data['result'] = $app[$this->index]->purge(new DateTime($before));
            $data['msg']    = true;
            
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
    
    /**
      *  Will Query the Queue for multiple jobs
      *
      *  @access public
      *  @return Response
      */
    public function getJobsAction(Application $app, Request $req )
    {
        $format = $app['laterjob.api.formatters.job'];
        $data = array(
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
            
            
            $errors = $app['validator']->validateValue(array(
                                                 'state'  => $state,
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
            $result      = $app[$this->index]->query((int)$offset,(int)$limit,$state,$order,$before,$after);
            $data['msg'] = true;
            
           # convert using formatter if known type.
            if($result instanceof Collection) {
                $data['result'] = $format->toArrayCollection($result);
            } elseif($result instanceof Storage) {
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