<?php

class Admin_ProfileController extends Zend_Controller_Action
{
    public function indexAction() {
        
        $redirector = $this->getHelper('Redirector');
        $redirector instanceof Zend_Controller_Action_Helper_Redirector;

        $redirector->setExit(true)
                ->gotoRoute(array(

                        'controller' => 'admin_profile',
                        'action' => 'edit'

                ), 'default', true);
        
    }
    
    public function editAction() {
        
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_ProfileEdit();

//default form data
        $form->populate($user);

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data has been sent for user profile');
                }

                //get form data
                $formData = $form->getValues();
                
               

                // do actual task
                
                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
                $cmsUsersTable->updateUser($user['id'], $formData);
                
                $user = $cmsUsersTable->getUserById($user['id']);
                
                //write fresh user data into session
                Zend_Auth::getInstance()->getStorage()->write($user);
                
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Profile has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }
    
    public function changepasswordAction() {
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');
        $form = new Application_Form_Admin_ProfileChangePassword();

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        if ($request->isPost() && $request->getPost('task') === 'change_password') {
            try {
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data has been sent for password change');
                }
                $formData = $form->getValues();
                
                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();                
                $cmsUsersTable->changeUserPassword($user['id'], $formData['new_password']);

                $flashMessenger->addMessage('Password has been changed', 'success');

                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'changepassword'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        
        
    }
    
}