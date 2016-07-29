<?php

class Admin_UsersController extends Zend_Controller_Action
{
    
    public function indexAction() {
        
        $flashMessenger = $this->getHelper('FlashMessenger');
		
        $systemMessages = array(

                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
        );

        $this->view->users = array();
            
        $this->view->systemMessages = $systemMessages;
        
    }
    
    public function addAction() {
        
        $request = $this->getRequest();
                
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		$form = new Application_Form_Admin_UserAdd();
               
		//default form data
		$form->populate(array(
			
		));

		if ($request->isPost() && $request->getPost('task') === 'save') { 
                    
			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) { 
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new user');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
                                //inserujemo novi zapis instanciramo klasi
                                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                                
                                $userId = $cmsUsersTable->insertUser($formData);

				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('User has been saved', 'success');

				//redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi memberi
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
						'action' => 'index'
                                                            ), 'default', true);
			} catch (Application_Model_Exception_InvalidInput $ex) {
				$systemMessages['errors'][] = $ex->getMessage();
			}
		}

		$this->view->systemMessages = $systemMessages;
		$this->view->form = $form;
        
    }
    
    public function editAction(){
       
            $request=  $this->getRequest();
           
            $id = (int)$request->getParam('id');
           
            if($id <= 0){
                //prekida se izvrsavanje programa i prikazuje se page not found
                throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
            }
            
            $loggedInUser = Zend_Auth::getInstance()->getIdentity();
            
            if( $id == $loggedInUser['id']) {
//                throw new Zend_Controller_Router_Exception('Go to edit profile page' . $id, 404);
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_profile',
                        'action' => 'edit'
                        ), 'default', true);
            }
           
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
           
            $user = $cmsUsersTable->getUserById($id);
           
            if(empty($user)){
                throw new Zend_Controller_Router_Exception('No user found with id : ' . $id, 404);
            }
           
            $flashMessenger = $this->getHelper('FlashMessenger');
                //prenosi sistemske poruke sa prethodnih stranica
       
            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );
       
       
        $form = new Application_Form_Admin_UserEdit($user['id']);
              

        //default form data
        $form->populate($user);

        if ($request->isPost() && $request->getPost('task') === 'update') { //ukoliko je pokrenuta forma
                    //da li je pokrenut post zahtev i dali je to hiden polje save  tj forma pokrenuta

            try {
                if (!$form->isValid($request->getPost())) {  //da li su podaci sa forme validni asocijativni niz ciji su kljucevi name atriuti polja u formi
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for user');
                }
                $formData = $form->getValues(); 
                $cmsUsersTable->updateUser($user['id'], $formData);

                $flashMessenger->addMessage('User has been updated', 'success');
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                        ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
           
            $this->view->user =$user;
    }
    
    public function enableAction() {
        $request = $this->getRequest();
            
        if(!$request->isPost() || $request->getPost('task') != 'enable' ) {
                
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                            ), 'default', true);
            }
            
        $flashMessenger = $this->getHelper('FlashMessenger');
            
            
        try {


        $id = (int) $request->getPost("id");

        if($id <= 0) {

            throw new Application_Model_Exception_InvalidInput("Invalid user id: " . $id );

        }

        $cmsUsersTable = new Application_Model_DbTable_CmsUsers;

        $user = $cmsUsersTable->getUserById($id);

        if( empty($user) ) {

            throw new Application_Model_Exception_InvalidInput("No user is found with id: " . $id );

        }

            $cmsUsersTable->enableUser($id);
            $flashMessenger->addMessage("User " . $user["first_name"] . " " . $user["last_name"] . " has been enabled." , "success");

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                            ), 'default', true);

        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage());

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                            ), 'default', true);

        }

		
        
    }
    
    public function disableAction() {
        
        $request = $this->getRequest();
            
            if(!$request->isPost() || $request->getPost('task') != 'disable' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid user id: " . $id );
                
            }
            
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers;
            
            $user = $cmsUsersTable->getUserById($id);
            
            if( empty($user) ) {
                
                throw new Application_Model_Exception_InvalidInput("No user is found with id: " . $id );

            }
            
                $cmsUsersTable->disableUser($id);
                
                $request instanceof Zend_Controller_Request_Http;
                
                if($request->isXmlHttpRequest()) {
                    
                    $responseJson = array(
                        'status' => 'ok',
                        'statusMessage' => "User " . $user["first_name"] . " " . $user["last_name"] . " has been disabled."
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                
                $flashMessenger->addMessage("User " . $user["first_name"] . " " . $user["last_name"] . " has been disabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                
                }

            } catch (Application_Model_Exception_InvalidInput $ex) {

                if($request->isXmlHttpRequest()) {
                    
                    $responseJson = array(
                        'status' => 'error',
                        'statusMessage' => $ex->getMessage()
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                    $flashMessenger->addMessage($ex->getMessage());

                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_users',
                                    'action' => 'index'
                                    ), 'default', true);
                }
                
                
            }
            
    }
    
        public function resetpasswordAction() {
                
            $request = $this->getRequest();
            
            if(!$request->isPost() || $request->getPost('task') != 'reset' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid user id: " . $id );
                
            }
            
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers;
            
            $user = $cmsUsersTable->getUserById($id);
            
            if( empty($user) ) {
                
                throw new Application_Model_Exception_InvalidInput("No user is found with id: " . $id );

            }
            
                $cmsUsersTable->resetPassword($id);
                $flashMessenger->addMessage("User's password (" . $user["first_name"] . " " . $user["last_name"] . ") has been reset." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                
            }
            
        }
    
        
        public function deleteAction() {
            
            $request = $this->getRequest();
            
            if(!$request->isPost() || $request->getPost('task') != 'delete' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid user id: " . $id );
                
            }
            
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers;
            
            $users = $cmsUsersTable->getUserById($id);
            
            if( empty($users) ) {
                
                throw new Application_Model_Exception_InvalidInput("No user is found with id: " . $id );

            }
            
//                $cmsMembersTable->deleteMember($id, $member['order_number']);
                $cmsUsersTable->deleteUser($id);
                
                $flashMessenger->addMessage("User " . $user["first_name"] . " " . $user["last_name"] . " has been deleted." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                
            }
            
		
            
        }
    
        public function datatableAction() {
            
            $request = $this->getRequest();
            
            $datatableParameters = $request->getParams();
            
            
            /*
            Array
                (
                    [controller] => admin_users
                    [action] => datatable
                    [module] => default
                    [draw] => 1
                    [order] => Array
                        (
                            [0] => Array
                                (
                                    [column] => 2
                                    [dir] => asc
                                )

                        )

                    [start] => 0
                    [length] => 3
                    [search] => Array
                        (
                            [value] => 
                            [regex] => false
                        )

                )
             */
            
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            
            $loggedInUser = Zend_Auth::getInstance()->getIdentity();
            
            $filters = array(
                'id_exclude' => $loggedInUser['id']
            );
            $orders = array();
            $limit = 5;
            $page = 1;
            $draw = 1;
            
            $columns = array('status', 'username','first_name', 'last_name', 'email', 'actions');
            
            if(isset($datatableParameters['draw'])) {
                $draw = $datatableParameters['draw'];
                
                if( isset($datatableParameters['length'])) {
                    $limit = $datatableParameters['length'];
                    
                    if(isset($datatableParameters['start'])) {
                        $page = floor($datatableParameters['start'] / $datatableParameters['length']) + 1;
                    }
                    
                } 
                
            }
            
            if ( isset($datatableParameters['order']) && is_array($datatableParameters['order']) ) {
                
                foreach($datatableParameters['order'] AS $datatableOrder) {
                    $columnIndex = $datatableOrder['column'];
                    $orderDirection = strtoupper($datatableOrder['dir']);
                    
                    if(isset($columns[$columnIndex])) {
                    
                        $orders[$columns[$columnIndex]] = $orderDirection;
                    
                    }
                }
                
            }
            
            if( isset($datatableParameters['search']) 
                    && is_array($datatableParameters['search'])
                    && isset($datatableParameters['search']['value'])
                    ) {
                $filters['username_search'] = $datatableParameters['search']['value'];
            }
            
            $usersFilteredCount = $cmsUsersTable->count($filters);
            $usersTotal = $cmsUsersTable->count();
            
            
            
            $users = $cmsUsersTable->search(array(
                'filters' => $filters,
                'orders' => $orders,
                'limit' => $limit,
                'page' => $page
            ));
            
            $this->view->users = $users;
            $this->view->usersTotal = $usersTotal;
            $this->view->usersFilteredCount = $usersFilteredCount;
            $this->view->draw = $draw;
            $this->view->columns = $columns;
        }
        
}
