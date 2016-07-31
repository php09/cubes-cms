<?php

class Admin_ClientsController extends Zend_Controller_Action
{
    public function indexAction() {
        
        $flashMessenger = $this->getHelper('FlashMessenger');	
        $systemMessages = array(

                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
        );
		
        $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
        $select = $cmsClientsDbTable->select();
        $select->order('order_number');
        $clients = $cmsClientsDbTable->fetchAll($select);
		
        $this->view->clients = $clients;
        $this->view->systemMessages = $systemMessages;
        
    }
    
    public function addAction() {
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        $form = new Application_Form_Admin_ClientAdd();
        $form->populate(array(
        ));
        if ($request->isPost() && $request->getPost('task') === 'save') {
            try {
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new client');
                }
                $formData = $form->getValues();
                unset($formData['client_photo']);
                $cmsClientsTable = new Application_Model_DbTable_CmsClients();
                $clientId = $cmsClientsTable->insertClient($formData);
                if ($form->getElement("client_photo")->isUploaded()) {
                    $fileInfos = $form->getElement("client_photo")->getFileInfo('client_photo');
                    $fileInfo = $fileInfos["client_photo"];
                    try {
                        $clientPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
                        $clientPhoto->fit(170, 70);
                        $clientPhoto->save(PUBLIC_PATH . '/uploads/clients/' . $clientId . '.jpg');
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Client has been saved but error occured during image processing.', 'errors');
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_clients',
                                    'action' => 'edit',
                                    'id' => $clientId
                                        ), 'default', true);
                    }
                }
                $flashMessenger->addMessage('Client has been saved', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }
    
    public function editAction() {
        $request = $this->getRequest();
        $id = (int) $request->getParam("id");
        if($id <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid client id: ' . $id , 404);
        }
        $cmsClientsTable = new Application_Model_DbTable_CmsClients();
        $client = $cmsClientsTable->getClientById($id);   
        if( empty($client) ) {
            throw new Zend_Controller_Router_Exception('No client is found with id: ' . $id , 404);
        }
        $this->view->client = $client;
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
        );
        $form = new Application_Form_Admin_ClientEdit();
        $form->populate( $client );
        if ($request->isPost() && $request->getPost('task') === 'update') {
            try {
                if (!$form->isValid($request->getPost())) { 
                        throw new Application_Model_Exception_InvalidInput('Invalid data was sent for client');
                }
                $formData = $form->getValues();                
                unset($formData['client_photo']);
                if( $form->getElement("client_photo")->isUploaded() ) {
                    $fileInfos = $form->getElement("client_photo")->getFileInfo('client_photo');
                    $fileInfo = $fileInfos["client_photo"];                    
                    try {
                        $clientPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
                        $clientPhoto->fit(170, 70);
                        $clientPhoto->save(PUBLIC_PATH . '/uploads/clients/' . $client['id'] . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
                    } catch (Exception $ex) {
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing.');
                    }
                }
                $cmsClientsTable->updateClient( $client['id'], $formData);  
                $flashMessenger->addMessage('Client has been updated', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_clients',
                                'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
            
    }
    
    public function deleteAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'delete') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $id = (int) $request->getPost("id");
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput("Invalid client id: " . $id);
            }
            $cmsClientsTable = new Application_Model_DbTable_CmsClients;
            $client = $cmsClientsTable->getClientById($id);
            if (empty($client)) {
                throw new Application_Model_Exception_InvalidInput("No client is found with id: " . $id);
            }
            $cmsClientsTable->deleteClient($id);
            $flashMessenger->addMessage("Client " . $client["first_name"] . " " . $client["last_name"] . " has been deleted.", "success");
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage());
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
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
                                'controller' => 'admin_clients',
                                'action' => 'index'
                                ), 'default', true);
            }
            $flashMessenger = $this->getHelper('FlashMessenger');
            try {
            $id = (int) $request->getPost("id");
            if($id <= 0) {
                throw new Application_Model_Exception_InvalidInput("Invalid client id: " . $id );   
            }
            $cmsClientsTable = new Application_Model_DbTable_CmsClients;
            $client = $cmsClientsTable->getClientById($id);
            if( empty($client) ) {
                throw new Application_Model_Exception_InvalidInput("No client is found with id: " . $id );
            }
                $cmsClientsTable->disableClient($id);
                $flashMessenger->addMessage("Client " . $client["first_name"] . " " . $client["last_name"] . " has been disabled." , "success");
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_clients',
                                'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage());
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_clients',
                                'action' => 'index'
                                ), 'default', true);
            }   
        }
        
    public function enableAction() {
        $request = $this->getRequest();
        if(!$request->isPost() || $request->getPost('task') != 'enable' ) {

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
        $id = (int) $request->getPost("id");
        if($id <= 0) {   
            throw new Application_Model_Exception_InvalidInput("Invalid client id: " . $id );
        }
        $cmsClientsTable = new Application_Model_DbTable_CmsClients;
        $client = $cmsClientsTable->getClientById($id);
        if( empty($client) ) {
            throw new Application_Model_Exception_InvalidInput("No client is found with id: " . $id );
        }
        $cmsClientsTable->enableClient($id);
        $flashMessenger->addMessage("Client " . $client["first_name"] . " " . $client["last_name"] . " has been enabled." , "success");                
        $redirector = $this->getHelper('Redirector');
        $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage());
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                            ), 'default', true);
        }
    }
        
    public function updateorderAction() {
        $request = $this->getRequest();
        if(!$request->isPost() || $request->getPost('task') != 'saveorder'){
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_clients',
                    'action' => 'index'
                    ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try{
            $sortedIds = $request->getPost('sorted_ids');
            if(empty($sortedIds)) {
                throw Application_Model_Exception_InvalidInput('Sorted ids are not sent');
            }
            $sortedIds = trim($sortedIds, ' ,');
            if(!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }
            $sortedIds = explode(',', $sortedIds);
            $cmsClientsTable = new Application_Model_DbTable_CmsClients();
            $cmsClientsTable->updateOrderOfClient($sortedIds);
            $flashMessenger->addMessage('Order is successfuly saved', 'success');
            $redirector = $this->getHelper('Redirector');
               $redirector->setExit(true)
                   ->gotoRoute(array(
                       'controller' => 'admin_clients',
                       'action' => 'index'
                       ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);
        }
    }
 
        public function dashboardAction() {
            Zend_Layout::getMvcInstance()->disableLayout();
            
            $request = $this->getRequest();
            $request instanceof Zend_Controller_Request_Http;
            
            if(!$request->isXmlHttpRequest()) {
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_dashboard',
                        'action' => 'index'
                        ), 'default', true);
            }
            
            $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
            $totalClients = $cmsClientsDbTable->totalNumberOfClients();
            $activeClients = $cmsClientsDbTable->numberOfActiveClients();
            
            $this->view->totalClients = $totalClients;
            $this->view->activeClients = $activeClients;
        }

}