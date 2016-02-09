<?php
namespace App\Test\TestCase\Controller;

use App\Controller\OperatingSystemsController;
use Cake\TestSuite\IntegrationTestCase;

class OperatingSystemsController extends IntegrationTestCase {
    /**
     * Test index method
     *
     * @return void
     */
    
    //Test cases for index method
    public function testIndex()
    {
        $result = $this->get('/operating_systems');
        $this->assertContains('status":"success","code":200,', $result);
    }
    
    /**
     * Test cases by passing ticket id as param
     */
    public function testView() {
        $result = $this->get('/tickets/2');
        $this->assertContains('status":"success","code":200,', $result);
    }
}

