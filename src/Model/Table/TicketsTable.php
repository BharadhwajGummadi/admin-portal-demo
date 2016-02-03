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
    
    public function validationDefault(Validator $validator) {

        $validator
                ->requirePresence('employee_id', 'create')
                ->notEmpty('employee_id', 'Employee id should not be empty.')
                ->add('employee_id', 'valid', ['rule' => 'numeric', 'message' => 'Only integers are allowed.']);
                
        $validator
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
}

