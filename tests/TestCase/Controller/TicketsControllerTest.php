<?php
namespace App\Test\TestCase\Controller;

use App\Controller\TicketsController;
use Cake\TestSuite\IntegrationTestCase;

class TicketsControllerTest extends IntegrationTestCase {
    /**
     * Test index method
     *
     * @return void
     */
    
    //Test cases for index method
    public function testIndex()
    {
        $result = $this->get('/tickets');
        $this->assertContains('status":"success","code":200,', $result);
    }
    
    /**
     * Test cases by passing ticket id as param
     */
    public function testIndexByID() {
        $result = $this->get('/tickets/23');
        $this->assertContains('status":"success","code":200,', $result);
    }
    
    public function testDelete() {
        $result = $this->get('/ticktes/23');
        $this->assertContains('status":"success","code":200,', $result);
    }
    
    public function testCreateTask() {
        $this->markTestIncomplete('Not implemented yet.');
    }
}

