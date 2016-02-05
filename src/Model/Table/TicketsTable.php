<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\Query;
use Cake\Validation\Validator;

class TicketsTable extends Table{
    
    public function initialize(array $config){
        parent::initialize($config);
        
        $this->table('tickets');
        $this->primaryKey('id');
        
        $this->addBehavior('Timestamp');
        
        $this->belongsTo('OperatingSystems',[
            'foreignKey' => 'operating_system_id',
            'joinTable' => 'tickets'
        ]);
        
        $this->belongsTo('Severities',[
            'foreignKey' => 'severity_id',
            'joinTable' => 'tickets'
        ]);
        
        $this->belongsTo('TicketStatus',[
            'foreignKey' => 'ticket_status_id',
            'joinTable' => 'tickets'
        ]);
    }
    
    /**
     * For input data validation
     * @param Validator $validator
     * @return Validator
     */
    public function validationDefault(Validator $validator) {

        $validator
                ->requirePresence('employee_id', 'create')
                ->notEmpty('employee_id', 'Employee id should not be empty.')
                ->add('employee_id', 'valid', ['rule' => 'numeric', 'message' => 'Only integers are allowed.']);
                
        $validator
                ->requirePresence('subject', 'create')
                ->notEmpty('subject', 'Subject should not be empty.');
        
        $validator
                ->allowEmpty('description');
        
        $validator
                ->requirePresence('severity_id', 'create')
                ->notEmpty('severity_id')
                ->add('severity_id', 'valid', ['rule' => 'numeric', 'message' => 'Only integers are allowed.']);
        
        $validator
                ->requirePresence('operating_system_id', 'create')
                ->notEmpty('operating_system_id')
                ->add('operating_system_id', 'valid', ['rule' => 'numeric', 'message' => 'Only integers are allowed.']);
                
        return $validator;
    }
    
    /**
     * Returns data in normalized form.
     * @param type $response
     * @param type $singleRcrd
     * @return array
     */
    public function normalizeResponseData($response, $singleRcrd = false){
        if(!$singleRcrd){
            $arrFinalResponse = array();
            foreach($response as $objResponse){
                $objResponse['os_type'] = $objResponse['operating_system']['os_type'];
                $objResponse['ticket_status_type'] = $objResponse['ticket_status']['ticket_status_type'];
                $objResponse['severity_level'] = $objResponse['severity']['severity_level'];
                unset($objResponse['operating_system']);
                unset($objResponse['ticket_status']);
                unset($objResponse['severity']);
                array_push($arrFinalResponse, $objResponse);
            }
            return $arrFinalResponse;
        }else{
            $response['os_type'] = $response['operating_system']['os_type'];
            $response['ticket_status_type'] = $response['ticket_status']['ticket_status_type'];
            $response['severity_level'] = $response['severity']['severity_level'];
            unset($response['operating_system']);
            unset($response['ticket_status']);
            unset($response['severity']);
            return $response;
        }
    }
    
    /**
     * Returns filtered data in normalized form
     * @param type $response
     * @return array
     */
    public function normalizeFltrdData($response){
        $arrFinalResponse = array();
        foreach($response as $objResponse){
            $objResponse['os_type'] = $objResponse['_matchingData']['OperatingSystems']['os_type'];
            $objResponse['ticket_status_type'] = $objResponse['_matchingData']['TicketStatus']['ticket_status_type'];
            $objResponse['severity_level'] = $objResponse['_matchingData']['Severities']['severity_level'];
            unset($objResponse['_matchingData']);
            array_push($arrFinalResponse, $objResponse);
        }
        return $arrFinalResponse;
    }
    
    /**
     * Filters the ticket list on OS type, severity level, created on and resolved type
     * @param Query $query
     * @param array $options
     * @return type
     */
//    public function findMatchedTickets(Query $query,  array $options){
//        $active = 1;
//        $arrConditions = array();
//        $arrConditions['Tickets.active'] = $active;
//        if(isset($options['created_on'])){
//            $arrConditions['Tickets.created_on LIKE'] = '%' . $options['created_on'] . '%';
//        }
//        if(isset($options['is_resolved'])){
//            $arrConditions['Tickets.ticket_status_id'] = $options['is_resolved'];
//        }
//        return $this->find()
//                    ->select([
//                        'Tickets.id',
//                        'Tickets.subject',
//                        'Tickets.employee_name',
//                        'Tickets.description',
//                        'Tickets.created_on',
//                        'Tickets.resolved_on',
//                        'OperatingSystems.os_type',
//                        'Severities.severity_level',
//                        'TicketStatus.ticket_status_type'
//                    ])
//                    ->distinct(['Tickets.id'])
//                    ->matching('OperatingSystems', function($query) use ($options){
//                        if(isset($options['os_id'])){
//                            return $query->where([
//                                            'OperatingSystems.id' => $options['os_id']
//                                        ]);
//                        }else{
//                            return $query->find('all');
//                        }
//                    })
//                    ->matching('Severities', function($query) use ($options){
//                        if(isset($options['severity_id'])){
//                            return $query->where([
//                                'Severities.id' => $options['severity_id']
//                            ]);
//                        }else{
//                            return $query->find('all');
//                        }
//                    })
//                    ->matching('TicketStatus', function($query) use ($options){
//                        return $query->find('all');
//                    })
//                    ->where($arrConditions)
//                    ->order([
//                        'Tickets.created_on' => 'DESC'
//                    ]);
//    }
    
    
    public function findMatchedTickets(Query $query,  array $options){
        $active = 1;
        $arrConditions = array();
        $arrConditions['Tickets.active'] = $active;
        if(isset($options['created_on'])){
            $arrConditions['Tickets.created_on LIKE'] = '%' . $options['created_on'] . '%';
        }
        if(isset($options['is_resolved'])){
            $arrConditions['Tickets.ticket_status_id'] = $options['is_resolved'];
        }
        if(isset($options['os_id'])){
            $arrConditions['Tickets.operating_system_id'] = $options['os_id'];
        }
        if(isset($options['severity_id'])){
            $arrConditions['Tickets.severity_id'] = $options['severity_id'];
        }
        $tickets = $this->find('all', 
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
                                           'conditions' => $arrConditions
                                       ])->order([
                                                'Severities.id' => 'ASC',
                                                'Tickets.created_on' => 'DESC'
                                            ]);

        return $this->normalizeResponseData($tickets);
    }

}

