<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class OperatingSystemsController extends AppController{
    
    public function index() {
        $OSTypes = TableRegistry::get('operating_systems');
        $active = 1;
        $query = $OSTypes->find()
                        ->select(['id', 'os_type'])
                        ->where(['active' => $active])
                        ->order(['os_type' => 'ASC']);
        echo json_encode($query);
    }
    
    /**
     * To get all os types from the database
     */
    public function view($id){
        $operatingSystem = $this->OperatingSystems->get($id);
        echo json_encode($operatingSystem);
    }
}