<?php
namespace App\Controller;

use App\Controller\AppController;

class SeveritiesController extends AppController{
    
    public function index(){
        $active = 1;
        $this->success['data'] = $this->Severities->find('all', ['conditions' => ['active' => $active]]);
        $this->sendJSONResponse($this->success);
        
    }
    
    public function view($id = Null){
        if(!isset($id)){
            $this->sendJSONResponse($this->badRequest);
        }
        $this->success['data'] = $this->Severities->get($id);
        $this->sendJSONResponse($this->success);
    }
    
}