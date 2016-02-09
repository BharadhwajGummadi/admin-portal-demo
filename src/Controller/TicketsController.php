<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Mailer\Email;
use Cake\Network\Http\Client;

define('WEBSTAION_API', 'http://10.0.0.19:8087/webapi/Users/UserInformation');
define('WEBSTATION_CREATE_TASK_API', 'http://10.0.0.19:8087/webapi/Tasks/insertTask');
define('OSMOSYS', '1');
define('DEFAULT_TICKET_STATUS', '2');   //for unresolved ticket status
define('ADMIN_EMAIL', 'sirisha.g@osmosys.asia');
define('ADMIN_NAME', 'Uday');
define('ADMIN_EMP_ID', '112');
define('PROJECT_ID', '316');
define('PROJECT_NAME', 'NewTechProject1');

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
                                                            'Tickets.resolution_description',
                                                            'Tickets.created_on',
                                                            'Tickets.resolved_on',
                                                            'Tickets.operating_system_id',
                                                            'Tickets.severity_id',
                                                            'Tickets.ticket_status_id',
                                                            'OperatingSystems.os_type',
                                                            'Severities.severity_level',
                                                            'TicketStatus.ticket_status_type'
                                                        ],
                                            'conditions' => [ 
                                                                'Tickets.active' => $active,
                                                                'Tickets.task_created' => 0
                                                            ]
                                        ])->order([
                                                    'Severities.id' => 'ASC',
                                                    'Tickets.created_on' => 'DESC'
                                                ]);

            $ticketDetails = $this->Tickets->normalizeResponseData($tickets);
            $this->success['data'] = $ticketDetails;
            $this->sendJSONResponse($this->success);
        }else{
            $tickets = $this->Tickets->find('matchedTickets', $queryData);
            $this->success['data'] = $tickets;
            $this->sendJSONResponse($this->success);
        }
    }
    
    /**
     * Get the respective ticket data
     * @param type $id
     */
    public function view($id, $isReturn = false){
        $ticketDetails = $this->Tickets->get($id,  [ 'contain' => ['Severities', 'TicketStatus', 'OperatingSystems'],
                                                    'fields' => [
                                                                'Tickets.id',
                                                                'Tickets.subject',
                                                                'Tickets.employee_name',
                                                                'Tickets.description',
                                                                'Tickets.resolution_description',
                                                                'Tickets.created_on',
                                                                'Tickets.resolved_on',
                                                                'Tickets.operating_system_id',
                                                                'Tickets.severity_id',
                                                                'Tickets.ticket_status_id',
                                                                'OperatingSystems.os_type',
                                                                'Severities.severity_level',
                                                                'TicketStatus.ticket_status_type'
                                                            ]
                                                    ]);
        $ticketDetails = $this->Tickets->normalizeResponseData($ticketDetails, true);
        if($isReturn){
            return $ticketDetails;
        }
        $this->success['data'] = $ticketDetails;
        $this->sendJSONResponse($this->success);
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
                    $this->badRequest['message'] = 'Erroe while patching the data';
                    $this->badRequest['data'] = $ticket->errors();
                    $this->sendJSONResponse($this->badRequest);
                }else{
                    //executes if there are no errors
                    $empID = $input['employee_id'];
                    $empData = $this->getEmpDataByID($empID);
                    $ticket['created_on'] = date('Y-m-d H:i:s');
                    $ticket['modified_on'] = date('Y-m-d H:i:s');
                    $ticket['ticket_status_id'] = DEFAULT_TICKET_STATUS;
                    $ticket['employee_name'] = $empData['FirstName'] . ' ' . $empData['LastName'];
                    
                    $result = $this->Tickets->save($ticket);
                    if(!empty($result->id)){
                        //executes if data inserted properly in to database
                        $ticketInfo = $this->view($result->id, true); //to pass data to email template
                        $ticketInfo['admin_name'] = ADMIN_NAME;
                        $this->setAction('sendMail', $ticketInfo);
                        $this->success['data'] = $result->id;
                        $this->success['message'] = 'Request inserted successfully.';
                        $this->sendJSONResponse($this->success);
                    
                    }else{
                        $this->failure['message'] = 'There was an error while inserting data.';
                        $this->sendJSONResponse($this->failure);
                    }
                }
                echo json_encode($response);
            }else{
                $this->sendJSONResponse($this->badRequest);
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
                    $this->badRequest['message'] = 'Erroe while patching the data';
                    $this->badRequest['data'] = $ticket->errors();
                    $this->sendJSONResponse($this->badRequest);
                }else{
                    $ticket['modified_on'] = date('Y-m-d H:i:s');
                    $ticket['resolved_on'] = ($input['resolved'] == '1') ? date('Y-m-d H:i:s') : '';
                    
                    if($this->Tickets->save($ticket)){
                          //Need to confirm whether to send an email or not
//                        $this->setAction('sendMail');
                        $this->success['message'] = 'Request updated successfully.';
                        $this->sendJSONResponse($this->success);
                    }else{
                        $this->failure['message'] = 'There was an error while updating data.';
                        $this->sendJSONResponse($this->failure);
                    }
                }
                echo json_encode($response);
            }else{
                $this->sendJSONResponse($this->badRequest);
            }
        }
    }
    
    /**
     * For sending mail with specific infomation
     */
    public function sendMail($ticketInfo){
        $subject = 'OSM Ticket: ' . $ticketInfo['subject'];
        $empEmail = ADMIN_EMAIL;
        if(!empty($empEmail)){
            $email = new Email('default');
            $email
                ->template('raiseticket')
                ->emailFormat('html')
                ->to($empEmail)
                ->subject($subject)
                ->viewVars(['input' => $ticketInfo])
                ->send();
        }
    }
    
    /**
     * Returns the details of the employee based their emp ID
     * @param type $empID
     * @return string
     */
    public function getEmpDataByID($empID){
        $http = new Client();
        $response = $http->get(WEBSTAION_API, ['UserID' => $empID, 'CompanyID' => OSMOSYS]);
        $response = $response->json;
        if($response['RecordCount'] == 1){
            $empData = $response['MultipleResults'][0];
            return $empData;
        }
        return '';
    }
    
    /**
     * Creates task in webstation with the given input data
     */
    public function createTask(){
        $input = $this->request->data;
        //Check the token_id is present or not in the request object
        if(!isset($input['token_id'])){
            $this->failure['message'] = 'Please pass "token_id".';
            $this->sendJSONResponse($this->failure);
        }
        $arrTaskDetails = array();
        if($this->request->is('post')){
            $loginUserTokenID = $input['token_id'];
            $arrTaskDetails['TaskName'] = $input['task_name'];
            $arrTaskDetails['Comments'] = $input['description'];
            $arrTaskDetails['AssignedBy'] = $input['assinged_by'];
            $arrTaskDetails['AssignedTo'] = $input['assinged_to'];
            $arrTaskDetails['ExpectedHours'] = $input['estimated_hours'];
            
            $arrTaskDetails['AssignedToEmpID'] = ADMIN_EMP_ID; //system admin ID
            $arrTaskDetails['EmpID'] = ADMIN_EMP_ID;
            $arrTaskDetails['OwnerID'] = ADMIN_EMP_ID;  //AssignedBy employee user ID

            $arrTaskDetails['TaskProjectID'] = PROJECT_ID; //test project id
            $arrTaskDetails['TaskProjectName'] = PROJECT_NAME;  //test project name

            $arrTaskDetails['AssociatedTasks'] = '';
            $arrTaskDetails['AttachedFiles'] = '';
          //  $arrTaskDetails['ExpectedHours'] = 0;
            $arrTaskDetails['GetUpdates'] = 1;  //will gives updates
            $arrTaskDetails['InformTo'] = '';
            $arrTaskDetails['ModuleName'] = '';
            $arrTaskDetails['NonBillableTask'] =  false;
            $arrTaskDetails['Notes'] = '';
            $arrTaskDetails['SendEmail'] = true;
            $arrTaskDetails['SprintID'] = 0;
            $arrTaskDetails['TaskCategoryID'] = 1;
            $arrTaskDetails['TaskDueDate'] = "";
            $arrTaskDetails['TaskPriorityID'] = 1;
            $arrTaskDetails['TaskSprintID'] = "";
            $arrTaskDetails['TaskStatusID'] = 1;

            $http = new Client();
            $taskStatus = $http->post(WEBSTATION_CREATE_TASK_API,$arrTaskDetails,
                            ['headers' => ['AuthenticationToken' => $loginUserTokenID]]);
            $taskStatus = $taskStatus->json;
            
            if($taskStatus['ResponseId'] == 5555){
                //Update ticket status as task created
                $this->updateTicket($ticketId);
                $this->success['message'] = 'Task created successfully in Webstation.';
                $this->sendJSONResponse($this->success);
            }else{
                $this->failure['message'] = 'There was an error while creating task in Webstation.';
                $this->sendJSONResponse($this->failure);
            }
        }
    }
    
    /**
     * Deletes selected ticket from database
     * @param type $id
     */
    public function delete($id = null){
        $ticket = $this->Tickets->get($id);
        if($this->request->is(['delete'])){
            if(!empty($ticket)){
                $input['active'] = 0;   //inactive status
                $ticket = $this->Tickets->patchEntity($ticket, $input);
                $ticket['modified_on'] = date('Y-m-d H:i:s');
                $response = array();
                if($this->Tickets->save($ticket)){
                    $response['status'] = 'success';
                    $response['message'] = 'Ticket deleted successfully.';
                }else{
                    $response['status'] = 'fail';
                    $response['message'] = 'There was an error while processing data.';
                }
                echo json_encode($response);
            }
        }
    }
    
    /**
     * Description this method is used to update task_crated value in tickets table.
     */
    private function updateTicket($ticketId) {
        $result = $this->Tickets->updateAll(['task_created' => 1], ['id' => $ticketId]);
        if(!$result){
            $this->failure['message'] = 'Error while updating ticket.';
            $this->sendJSONResponse($this->failure);
        }
        return TRUE;
    }
}