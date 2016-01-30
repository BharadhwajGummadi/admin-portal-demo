<?php
namespace App\Table;

use Cake\ORM\Table;

class SeveritiesTable extends Table{
    
    public function initialize(array $config) {
        parent::initialize($config);
        
        $this->table('severities');
        $this->primaryKey('id');
//        $this->displayField('id');
//        
//        $this->addBehavior('Timestamp');
        
        
    }
}