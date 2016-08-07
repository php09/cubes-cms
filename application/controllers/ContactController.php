<?php

class ContactController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function askmemberAction() {
        
        $request = $this->getRequest();
        
        $id = $request->getParam('id');
        $id = trim($id);
        $id = (int) $id;
        
        if (empty($id)) {
            throw new Zend_Controller_Router_Exception('No member id', 404);
        } 
        
        $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
        $member = $cmsMembersTable->search( 
                array( 
                    'filters' => array(
                        'id' => $id, 
                        'status' => Application_Model_DbTable_CmsMembers::STATUS_ENABLED
                        )
                    )
                ); //search f-ja umesto getMemberById() da bi se zabranilo kontaktiranje membera koji ima status = 0
        
        $member = $member[0]; //posto je rezultat search() rowset
        
        if (empty($member)) {
            throw new Zend_Controller_Router_Exception('No member id', 404);
        } 
        
        $this->view->member = $member;

        
    }

}

