<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class OperatingSystemsController extends AppController{
    
    public function index(){
        $this->set('operating_systems', $this->paginate($this->OperatingSystems));
        $this->set('_serialize', ['operating_systems']);
    }
    
    /**
     * To get all os types from the database
     */
    public function getOSTypes(){
        $OSTypes = TableRegistry::get('operating_systems');
        $active = 1;
        $query = $OSTypes->find()
                        ->select(['id', 'os_type'])
                        ->where(['active' => $active])
                        ->order(['os_type' => 'ASC']);
        echo json_encode($query);
    }
}