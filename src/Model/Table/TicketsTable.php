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
    
    public function findByOSID(Query $query, array $options){
        if($options['osID']){
            return $this->find('all', [
                'conditions' => ['acitve' => 1]
            ]);
        }else{
            return $this->find()
                    ->distinct('Tickets.id')
                    ->matching('OperatingSystems', function($query) use ($options){
                        return $query->where(['OperatingSystems.id' => $options['osID']]);
                    });
        }
    }
}

