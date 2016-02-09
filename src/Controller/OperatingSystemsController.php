<?php
namespace App\Controller;

use App\Controller\AppController;

class OperatingSystemsController extends AppController{
    
    public function index() {
        $this->success['data'] = $this->OperatingSystems->find('all');
        $this->sendJSONResponse($this->success);
    }
    
    /**
     * To get all os types from the database
     */
    public function view($id){
        if(!isset($id)){
            $this->sendJSONResponse($this->badRequest);
        }
        $this->success['data'] = $this->OperatingSystems->get($id);
        $this->sendJSONResponse($this->success);
    }
}