<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class SeveritiesController extends AppController{
    
    public function index(){
        
    }
    
    /**
     * To get all the severities from the DB
     */
    public function getSeverities(){
        $severites = TableRegistry::get('severities');
        $active = 1;
        $query = $severites->find()
                            ->select(['id', 'severity_level'])
                            ->where(['active' => $active])
                            ->order(['severity_level' => 'ASC']);
        echo json_encode($query);
    }
    
}