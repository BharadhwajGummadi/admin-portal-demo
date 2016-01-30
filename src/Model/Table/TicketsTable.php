<?php
namespace App\Model\Table;

use Cake\ORM\Table;
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
                ->notEmpty('employee_id', 'Employee id should not be empty.');
        
        $validator
                ->notEmpty('subject', 'Subject should not be empty.');
        
        $validator
                ->allowEmpty('description');
        
        $validator
                ->requirePresence('severity_id', 'create')
                ->notEmpty('severity_id');
        
        $validator
                ->requirePresence('ticket_status_id', 'create')
                ->notEmpty('ticket_status_id');
        
        $validator
                ->requirePresence('operating_system_id', 'create')
                ->notEmpty('operating_system_id');
        
        return $validator;
    }
}

