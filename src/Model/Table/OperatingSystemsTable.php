<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class OpearatingSystemsTable extends Table{
    
    public function initialize(array $config) {
        parent::initialize($config);
        
        $this->table('operating_systems');
        $this->displayField('id');
        $this->primaryKey('id');
    }
    
}