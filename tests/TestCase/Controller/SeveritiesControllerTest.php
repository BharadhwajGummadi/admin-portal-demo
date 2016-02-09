<?php
namespace App\Test\TestCase\Controller;

use App\Controller\SeveritiesController;
use Cake\TestSuite\IntegrationTestCase;

class SeveritiesController extends IntegrationTestCase {
    /**
     * Test index method
     *
     * @return void
     */
    
    //Test cases for index method
    public function testIndex()
    {
        $result = $this->get('/severities');
        $this->assertContains('status":"success","code":200,', $result);
    }
    
    /**
     * Test cases by passing ticket id as param
     */
    public function testView() {
        $result = $this->get('/severities/2');
        $this->assertContains('status":"success","code":200,', $result);
    }
}

