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
                                                                'OperatingSystems.os_type',
                                                                'Severities.severity_level',
                                                                'TicketStatus.ticket_status_type'
                                                            ]
                                                    ]);
        $ticketDetails = $this->Tickets->normalizeResponseData($ticketDetails, true);
        if($isReturn){
            return $ticketDetails;
        }
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
                    $empData = $this->getEmpDataByID($empID);
                    $ticket['created_on'] = date('Y-m-d H:i:s');
                    $ticket['modified_on'] = date('Y-m-d H:i:s');
                    $ticket['ticket_status_id'] = DEFAULT_TICKET_STATUS;
                    $ticket['employee_name'] = $empData['FirstName'] . ' ' . $empData['LastName'];
                    
                    $result = $this->Tickets->save($ticket);
                    if(!empty($result->id)){
                        //executes if data inserted properly in to database
                        $ticketInfo = $this->view($result->id, true); //to pass data to email template
                        $this->setAction('sendMail', $ticketInfo);
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
    public function sendMail($ticketInfo){
        $subject = $ticketInfo['subject'];
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
        $arrTaskDetails = array();
        if($this->request->is('post')){
            $arrTaskDetails['TaskName'] = $input['task_name'];
            $arrTaskDetails['Comments'] = $input['description'];
            $arrTaskDetails['AssignedBy'] = $input['assinged_by'];
            $arrTaskDetails['AssignedTo'] = $input['assinged_to'];
            $arrTaskDetails['AssignedToEmpID'] = ADMIN_EMP_ID;

            $arrTaskDetails['EmpID'] = ADMIN_EMP_ID;
            $arrTaskDetails['OwnerID'] = ADMIN_EMP_ID;

            $arrTaskDetails['TaskProjectID'] = PROJECT_ID; //test project id
            $arrTaskDetails['TaskProjectName'] = PROJECT_NAME;  //test project name

            $arrTaskDetails['AssociatedTasks'] = '';
            $arrTaskDetails['AttachedFiles'] = '';
            $arrTaskDetails['ExpectedHours'] = 0;
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
            $taskStatus = $http->post(WEBSTATION_CREATE_TASK_API,$arrTaskDetails);
            $taskStatus = $taskStatus->json;
            $response = array();

            if($taskStatus['ResponseId'] == 5555){
                $response['status'] = 'success';
                $response['message'] = 'Task created successfully in Webstation.';
            }else{
                $response['status'] = 'fail';
                $response['message'] = 'There was an error while creating task in Webstation.';
            }
            echo json_encode($response);
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
}