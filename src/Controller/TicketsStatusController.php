<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class TicketsStatusController extends AppController{
    
    public function index(){
        $this->set('ticket_status', $this->paginate($this->TicketsStatus));
        $this->set('_serialize', ['ticket_status']);
    }
    
    /**
     * To get all ticket status types from the database
     */
    public function getStatusType(){
        $OSTypes = TableRegistry::get('ticket_status');
        $active = 1;
        $query = $OSTypes->find()
                        ->select(['id', 'ticket_status_type'])
                        ->where(['active' => $active])
                        ->order(['ticket_status_type' => 'ASC']);
        echo json_encode($query);
    }
}