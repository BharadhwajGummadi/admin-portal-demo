<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;


class TicketsController extends AppController{
    
    public function index(){
        $active = 1;
        $tickets = $this->Tickets->find('all', 
                                        ['conditions' => ['active' => $active]]
                                    );
        echo json_encode($tickets);
    }
    
    public function view($id){
        $ticketDetails = $this->Tickets->get($id);
        echo $ticketDetails;
    }
    
    public function add(){
        $ticketTable = TableRegistry::get('tickets');
        $ticket = $ticketTable->newEntity();
        if($this->request->is('post')){
            $input = $this->request->data;
            if(!empty($input)){
                $ticket = $ticketTable->patchEntity($ticket, $input);
                $response = array();
                if($ticket->errors()){
                    $response['status'] = 'error';
                    $response['data'] = $ticket->errors();
                }else{
                    $ticket['created_on'] = date('Y-m-d H:i:s');
                    $ticket['modified_on'] = date('Y-m-d H:i:s');
                    if($ticketTable->save($ticket)){
                        $this->setAction('sendMail');
                        $response['status'] = 'success';
                        $response['data'] = 'There was an error while inserting data.';
                    }else{
                        $response['status'] = 'error';
                        $response['data'] = 'There was an error while inserting data.';
                    }
                }
                echo json_encode($response);
            }else{
                echo 'Input should not be empty.';
            }
        }
    }
    
    public function sendMail(){
        $input = $this->request->data;
        $subject = $input['subject'];
        $body = $input['description'];
        $email = new Email('default');
        $email->to('bharadwaja.g@osmosys.asia')
                ->subject($subject)
                ->send($body);
    }
}