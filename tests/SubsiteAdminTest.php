<?

class SubsiteAdminTest extends SapphireTest {
	static $fixture_file = 'subsites/tests/SubsiteTest.yml';

    function adminLoggedInSession() {
        return new Session(array(
            'loggedInAs' => $this->idFromFixture('Member', 'admin')
        ));
    }

    /**
     * Test generation of the view
     */
    function testBasicView() {
        // Open the admin area logged in as admin
        $response1 = Director::test('admin/subsites/', null, $this->adminLoggedInSession());
        
        // Confirm that this URL gets you the entire page, with the edit form loaded
        $response2 = Director::test('admin/subsites/show/1', null, $this->adminLoggedInSession());
        $this->assertTrue(strpos($response2->getBody(), 'id="Form_EditForm_ID"') !== false, "Testing Form_EditForm_ID exists");
        $this->assertTrue(strpos($response2->getBody(), '<head') !== false, "Testing <head> exists");

        // Confirm that this URL gets you just the form content, with the edit form loaded
        $response3 = Director::test('admin/subsites/show/1', array('ajax' => 1), $this->adminLoggedInSession());

        $this->assertTrue(strpos($response3->getBody(), 'id="Form_EditForm_ID"') !== false, "Testing Form_EditForm_ID exists on ajax page");
        $this->assertTrue(strpos($response3->getBody(), '<form') === false, "Testing <form> doesn't exist on ajax page");
        $this->assertTrue(strpos($response3->getBody(), '<head') === false, "Testing <head> doesn't exist on ajax page");
    }
	
	/**
	 * Test searching for an intranet
	 */
	function XXtestIntranetSearch() {
		$cont = new SubsiteAdmin();
		$cont->pushCurrent();
        $cont->setSession($this->adminLoggedInSession());
		
		// Check that the logged-in member has the correct permissions
		$this->assertTrue(Permission::check('ADMIN') ? true : false);

		$form = $cont->SearchForm();
		
		$searches = array(
			array('Name' => 'Other'),
		);
		
		foreach($searches as $search) {
			$response = $form->testAjaxSubmission('getResults', $search);
            $links = $response->getLinks();
            foreach($links as $link) {
                $this->assertTrue(preg_match('/^admin\/subsites\/show\/[0-9]+$/', $link['href']) == 1, "Search result links bad.");
            }
		}
		
		$cont->popCurrent();
	}
    
    /**
     * Test the intranet creation form.
     */
    function XXtestIntranetCreation() {
  		$cont = new SubsiteAdmin();
        $cont->pushCurrent();
        $cont->setSession($this->adminLoggedInSession());
        
        $form = $cont->AddSubsiteForm();
        $source = $form->dataFieldByName('TemplateID')->getSource();
        
        $templateIDs = $this->allFixtureIDs('Subsite_Template');
        foreach($templateIDs as $templateID) {
            $this->assertArrayHasKey($templateID, $source);
        }
        
        $templateObj = $this->objFromFixture('Subsite_Template','main');
        $this->assertEquals($templateObj->Title, $source[$templateObj->ID], "Template dropdown isn't listing Title values");

        $response = $form->testSubmission('addintranet', array(
            'Name' => 'Test Intranet',
            'Subdomain' => 'Test',
            'TemplateID' => 1,
            'AdminEmail' => '',
            'AdminName' => '',
        ));

        $this->assertTrue(true == preg_match('/admin\/subsites\/show\/([0-9]+)/i', $response->getHeader('Location'), $matches), "Intranet creation dowsn't redirect to new view");
        
        $newIntranet = DataObject::get_by_id("Subsite", $matches[1]);
        $this->assertEquals('Test Intranet', $newIntranet->Title, "New intranet not created properly.");
        
        $cont->popCurrent();
  }
	
}

?>