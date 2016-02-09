<?php
namespace App\Controller;

use App\Controller\AppController;

class TicketStatusController extends AppController{
    
    public function index(){
        $active = 1;
        $ticketStatus = $this->TicketStatus->find('all', [
            'conditions' => ['active' => $active]
        ]);
        $this->success['data'] = $ticketStatus;
        $this->sendJSONResponse($this->success);
    }
    
    public function view($id){
        if(!isset($id)){
            $this->sendJSONResponse($this->badRequest);
        }
        $this->success['data'] = $this->TicketStatus->get($id);
        $this->sendJSONResponse($this->success);
    }
}