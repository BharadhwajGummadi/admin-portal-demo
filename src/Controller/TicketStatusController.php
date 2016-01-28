<?php
namespace App\Controller;

use App\Controller\AppController;

class TicketStatusController extends AppController{
    
    public function index(){
        $active = 1;
        $ticketStatus = $this->TicketStatus->find('all', [
            'conditions' => ['active' => $active]
        ]);
        echo json_encode($ticketStatus);
    }
    
    public function view($id){
        $ticketStatusDetails = $this->TicketStatus->get($id);
        echo json_encode($ticketStatusDetails);
    }
}