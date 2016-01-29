<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;


class TicketsController extends AppController{
    
    /**
     * Gets list of all active tickets in database
     */
    public function index(){
        $active = 1;
        $tickets = $this->Tickets->find('all', 
                                        ['conditions' => ['active' => $active]]
                                    );
        echo json_encode($tickets);
    }
    
    /**
     * Get the respective ticket data
     * @param type $id
     */
    public function view($id){
        $ticketDetails = $this->Tickets->get($id);
        echo $ticketDetails;
    }
    
    /**
     * For inserting ticket data
     */
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
                    //executes if there are no errors
                    
                    $ticket['created_on'] = date('Y-m-d H:i:s');
                    $ticket['modified_on'] = date('Y-m-d H:i:s');
                    $result = $ticketTable->save($ticket);
                    if(!empty($result->id)){
                        //executes if data inserted properly in to database
                        
                        $this->setAction('sendMail');
                        $response['status'] = 'success';
                        $response['data'] = 'Request inserted successfully.';
                        $response['inserted_id'] = $result->id;
                    
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
    
    /**
     * Edit the ticket information
     * @param type $id
     */
    public function edit($id = null){
        $ticketTable = TableRegistry::get('tickets');
        $ticket = $ticketTable->get($id, [
            'contain' => []
        ]);
        if($this->request->is(['put', 'patch'])){
            $input = $this->request->data;
            if(!empty($input)){
                $response = array();
                $ticket = $ticketTable->patchEntity($ticket, $input);
                if($ticket->errors()){
                    $response['status'] = 'error';
                    $response['data'] = $ticket->errors();
                }else{
                    $ticket['modified_on'] = date('Y-m-d H:i:s');
                    if($ticketTable->save($ticket)){
                          //To confirm whether to send an email or not
//                        $this->setAction('sendMail');
                        
                        $response['status'] = 'success';
                        $response['data'] = 'Request updated successfully.';
                    }else{
                        $response['status'] = 'error';
                        $response['data'] = 'There was an error while updating data.';
                    }
                }
                echo json_encode($response);
            }else{
                echo 'Input should not be empty.';
            }
        }
    }
    
    /**
     * For sending mail with specific infomation
     */
    public function sendMail(){
        $input = $this->request->data;
        $subject = $input['subject'];
        $body = $input['description'];
        $email = new Email('default');
        $email->to('sirisha.g@osmosys.asia')
                ->subject($subject)
                ->send($body);
    }
}