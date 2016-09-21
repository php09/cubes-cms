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
        

        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Contact();

        $systemMessages = "init";

        if ($request->isPost() && $request->getPost('task') === 'contact') {

                try {

                        //check form is valid
                        if (!$form->isValid($request->getPost())) {
                                throw new Application_Model_Exception_InvalidInput('Invalid form data bla bla');
                        }

                        //get form data
                        $formData = $form->getValues();

                        // do actual task
                        //save to database etc
                        
                        $mailHelper = new Application_Model_Library_MailHelper();
                        
                        $result = $mailHelper->sendEmail("neb991@yahoo.com", $formData['email'], $formData['name'], $formData['message']);
                        
                        if(!$result) {
                            $systemMessages = "error";
                        } else {
                            $systemMessages = "success";
                        }

                } catch (Application_Model_Exception_InvalidInput $ex) {
                        $systemMessages['errors'][] = $ex->getMessage();
                }
        }

        $this->view->systemMessages = $systemMessages;





        
        
        
        
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

