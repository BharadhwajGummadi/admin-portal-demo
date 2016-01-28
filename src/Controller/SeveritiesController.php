<?php
namespace App\Controller;

use App\Controller\AppController;

class SeveritiesController extends AppController{
    
    public function index(){
        $active = 1;
        $severities = $this->Severities->find('all', ['conditions' => ['active' => $active]]
                                            );
        echo json_encode($severities);
    }
    
    public function view($id){
        $severities = $this->Severities->get($id);
        echo json_encode($severities);
    }
    
}