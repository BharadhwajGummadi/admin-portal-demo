<?php
namespace App\Controller;

use App\Controller\AppController;

class OperatingSystemsController extends AppController{
    
    public function index() {
        $OSTypes = $this->OperatingSystems->find('all');
        echo json_encode($OSTypes);
    }
    
    /**
     * To get all os types from the database
     */
    public function view($id){
        $operatingSystem = $this->OperatingSystems->get($id);
        echo json_encode($operatingSystem);
    }
}