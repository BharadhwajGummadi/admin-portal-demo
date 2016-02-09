<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        
        $this->autoRender = false;
    }
    
    // Response objects 
    protected $badRequest = array('status' => 'error', 'code' => 400, 'data' => NULL, 'message' => 'Empty request received.');
    protected $internalServerError = array('status' => 'error', 'code' => 500, 'data' => NULL, 'message' => '');
    protected $timeOut = array('status' => 'error', 'code' => 408, 'data' => NULL, 'message' => '');
    protected $success = array('status' => 'success', 'code' => 200, 'data' => NULL);
    protected $failure = array('status' => 'fail', 'code' => 202, 'data' => NULL);
    protected $notFound = array('status' => 'notFound', 'code' => 404);
    protected $unAuthorized = array('status' => 'error', 'code' => 401);


    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }
    
     /**
     * Descriptoin : This method is used to send response object in json format
     * @param array $responseObj
     */
    protected function sendJSONResponse($responseObj) {
        header("Connection: close");
        header("X-Powered-By: Osmosys");
        $this->response->type('json');
       /// $this->response->body(json_encode($responseObj));
        echo json_encode($responseObj);
        exit;
    }
}
