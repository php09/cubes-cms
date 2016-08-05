<?php

class Admin_ServicesController extends Zend_Controller_Action 
{

    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        $select = $cmsServicesDbTable->select();
        $select->order('order_number');
        $services = $cmsServicesDbTable->fetchAll($select);
        $this->view->services = $services;
        $this->view->systemMessages = $systemMessages;
    }

    public function editAction() {
        $request = $this->getRequest();
        $id = (int) $request->getParam("id");
        if ($id <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid service id: ' . $id, 404);
        }
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices;
        $service = $cmsServicesDbTable->getServiceById($id);
        if (empty($service)) {
            throw new Zend_Controller_Router_Exception('No service is found with id: ' . $id, 404);
        }
        $this->view->service = $service;
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        $form = new Application_Form_Admin_ServiceAdd();
        $form->populate($service);
        if ($request->isPost() && $request->getPost('task') === 'update') {
            try {
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for service');
                }
                $formData = $form->getValues();
                $cmsServicesDbTable->updateService($service['id'], $formData);
                $flashMessenger->addMessage('Service has been updated', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_services',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }

    public function addAction() {
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        $form = new Application_Form_Admin_ServiceAdd();
        $form->populate(array(
        ));
        if ($request->isPost() && $request->getPost('task') === 'save') {
            try {
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new service');
                }
                $formData = $form->getValues();
                $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
                
                $cmsServicesDbTable->insertService($formData);
                $flashMessenger->addMessage('Service has been saved', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_services',
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
            
            if(!$request->isPost() || $request->getPost('task') != 'delete' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid service id: " . $id );
                
            }
            
            $cmsServicesTable = new Application_Model_DbTable_CmsServices;
            
            $service = $cmsServicesTable->getServiceById($id);
            
            if( empty($service) ) {
                
                throw new Application_Model_Exception_InvalidInput("No service is found with id: " . $id );

            }
            
                $cmsServicesTable->deleteService($id, $service['order_number']);
                $flashMessenger->addMessage("Service " . $service["title"] . " has been deleted." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
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
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid service id: " . $id );
                
            }
            
            $cmsServicesTable = new Application_Model_DbTable_CmsServices;
            
            $service = $cmsServicesTable->getServiceById($id);
            
            if( empty($service) ) {
                
                throw new Application_Model_Exception_InvalidInput("No service is found with id: " . $id );

            }
            
                $cmsServicesTable->disableService($id);
                $flashMessenger->addMessage("Service " . $service["title"]  . " has been disabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
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
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            try {
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid service id: " . $id );
                
            }
            
            $cmsServicesTable = new Application_Model_DbTable_CmsServices;
            
            $service = $cmsServicesTable->getServiceById($id);
            
            if( empty($service) ) {
                
                throw new Application_Model_Exception_InvalidInput("No service is found with id: " . $id );

            }
            
                $cmsServicesTable->enableService($id);
                $flashMessenger->addMessage("Service " . $service["title"] . " has been enabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true);
                
            }
        }
        
        public function updateorderAction() {
            
            $request = $this->getRequest();
            
            if(!$request->isPost() || $request->getPost('task') != 'saveorder' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                $sortedIds = $request->getPost('sorted_ids');
                
                if(empty($sortedIds)) {
                    throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent.');
                }
                
                $sortedIds = trim($sortedIds, " ,");
                
                if(!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                    throw new Application_Model_Exception_InvalidInput("Invalid sorted ids.", $sortedIds);
                }
                
                $sortedIds = explode(',', $sortedIds);
                
                $cmsServicesTable = new Application_Model_DbTable_CmsServices;
                
                $cmsServicesTable->updateOrderOfService($sortedIds);
                
                $flashMessenger->addMessage("Order is successfully saved", 'success');
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
                                'action' => 'index'
                                ), 'default', true); 
                
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_services',
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

        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        $totalServices = $cmsServicesDbTable->count();
        $activeServices = $cmsServicesDbTable->count(array('status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED));

        $this->view->totalServices = $totalServices;
        $this->view->activeServices = $activeServices;
    }
        
        
    
}
