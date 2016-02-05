<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Mailer\Email;
use Cake\Network\Http\Client;

define('WEBSTAION_API', 'http://10.0.0.19:8087/webapi/Users/UserInformation');
define('OSMOSYS', '1');
define('DEFAULT_TICKET_STATUS', '2');   //for unresolved ticket status
define('ADMIN_EMAIL', 'sirisha.g@osmosys.asia');

class TicketsController extends AppController{
    
    /**
     * Gets list of all active tickets in database
     */
    public function index(){
        $active = 1;
        $queryData = $this->request->query;
        if(empty($queryData)){
            $tickets = $this->Tickets->find('all', 
                                        [   'contain' => ['Severities', 'TicketStatus', 'OperatingSystems'],
                                            'fields' => [
                                                            'Tickets.id',
                                                            'Tickets.subject',
                                                            'Tickets.employee_name',
                                                            'Tickets.description',
                                                            'Tickets.created_on',
                                                            'Tickets.resolved_on',
                                                            'OperatingSystems.os_type',
                                                            'Severities.severity_level',
                                                            'TicketStatus.ticket_status_type'
                                                        ],
                                            'conditions' => [ 
                                                                'Tickets.active' => $active
                                                            ]
                                        ])->order([
                                                    'Severities.id' => 'ASC',
                                                    'Tickets.created_on' => 'DESC'
                                                ]);

            $ticketDetails = $this->Tickets->normalizeResponseData($tickets);
            echo json_encode($tickets);
        }else{
            $tickets = $this->Tickets->find('matchedTickets', $queryData);
//            $ticketDetails = $this->Tickets->normalizeFltrdData($tickets);
            echo json_encode($tickets);
        }
    }
    
    /**
     * Get the respective ticket data
     * @param type $id
     */
    public function view($id){
        $ticketDetails = $this->Tickets->get($id,  [ 'contain' => ['Severities', 'TicketStatus', 'OperatingSystems'],
                                                    'fields' => [
                                                                'Tickets.id',
                                                                'Tickets.subject',
                                                                'Tickets.employee_name',
                                                                'Tickets.description',
                                                                'Tickets.created_on',
                                                                'Tickets.resolved_on',
                                                                'OperatingSystems.os_type',
                                                                'Severities.severity_level',
                                                                'TicketStatus.ticket_status_type'
                                                            ]
                                                    ]);
        $ticketDetails = $this->Tickets->normalizeResponseData($ticketDetails, true);
        echo $ticketDetails;
    }
    
    /**
     * For inserting ticket data
     */
    public function add(){
        $ticket = $this->Tickets->newEntity();
        if($this->request->is('post')){
            $input = $this->request->data;
            if(!empty($input)){
                $ticket = $this->Tickets->patchEntity($ticket, $input);
                $response = array();
                if($ticket->errors()){
                    $response['status'] = 'error';
                    $response['message'] = $ticket->errors();
                }else{
                    //executes if there are no errors
                    $empID = $input['employee_id'];
                    $empData = $this->getEmailDataByID($empID);
                    $ticket['created_on'] = date('Y-m-d H:i:s');
                    $ticket['modified_on'] = date('Y-m-d H:i:s');
                    $ticket['ticket_status_id'] = DEFAULT_TICKET_STATUS;
                    $ticket['employee_name'] = $empData['FirstName'] . ' ' . $empData['LastName'];
                    
                    $result = $this->Tickets->save($ticket);
                    if(!empty($result->id)){
                        //executes if data inserted properly in to database
                        
                        $this->setAction('sendMail');
                        $response['status'] = 'success';
                        $response['message'] = 'Request inserted successfully.';
                        $response['inserted_id'] = $result->id;
                    
                    }else{
                        $response['status'] = 'fail';
                        $response['message'] = 'There was an error while inserting data.';
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
        $ticket = $this->Tickets->get($id, [
            'contain' => []
        ]);
        if($this->request->is(['put', 'patch'])){
            $input = $this->request->data;
            if(!empty($input)){
                $response = array();
                $ticket = $this->Tickets->patchEntity($ticket, $input);
                if($ticket->errors()){
                    $response['status'] = 'error';
                    $response['message'] = $ticket->errors();
                }else{
                    $ticket['modified_on'] = date('Y-m-d H:i:s');
                    $ticket['resolved_on'] = ($input['resolved'] == '1') ? date('Y-m-d H:i:s') : '';
                    
                    if($this->Tickets->save($ticket)){
                          //Need to confirm whether to send an email or not
//                        $this->setAction('sendMail');
                        
                        $response['status'] = 'success';
                        $response['message'] = 'Request updated successfully.';
                    }else{
                        $response['status'] = 'fail';
                        $response['message'] = 'There was an error while updating data.';
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
        $empID = $input['employee_id'];
        $empEmail = ADMIN_EMAIL;
        if(!empty($empEmail)){
            $email = new Email('default');
            $email->to($empEmail)
                ->subject($subject)
                ->send($body);
        }
    }
    
    /**
     * Returns the email of the employee based their emp ID
     * @param type $empID
     * @return string
     */
    public function getEmailDataByID($empID){
        $http = new Client();
        $response = $http->get(WEBSTAION_API, ['UserID' => $empID, 'CompanyID' => OSMOSYS]);
        $response = $response->json;
        if($response['RecordCount'] == 1){
            $empData = $response['MultipleResults'][0];
            return $empData;
        }
        return '';
    }
    
}