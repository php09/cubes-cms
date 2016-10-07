<?php

class Admin_SitemapController extends Zend_Controller_Action
{
    public function indexAction() {
        
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
		
        $systemMessages = array(

                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
        );
        
        $this->view->systemMessages = $systemMessages;
        
        $id = (int) $request->getParam('id', 0);
        
        if($id < 0) {
            throw new Zend_Controller_Router_Exception('Invalid parent id for sitemap pages.', 404);
        }
        
        
        $cmsSitemapPages = new Application_Model_DbTable_CmsSitemapPages();
        
        if($id !=0) {
            $sitemapPage = $cmsSitemapPages->getSitemapPageById($id);
        
            if( !$sitemapPage) {
                throw new Zend_Controller_Router_Exception('No sitemap page is found for sitemap pages.', 404);
            }
        }
        
        
        $childSitemapPages = $cmsSitemapPages->search(
                array(
                    'filters' => array('parent_id' => $id),
                    'orders' => array('order_number' => 'ASC'),
        //            'limit' => '50',
        //            'page' => '3'
              ) 
            );
        
        $sitemapPagesBreadcrumbs = $cmsSitemapPages->getSitemapPageBreadcrumbs($id);
        
        $this->view->sitemapPages = $childSitemapPages;
        $this->view->sitemapPagesBreadcrumbs = $sitemapPagesBreadcrumbs;
        $this->view->currentSitemapPageId = $id;
    }
    
    
    public function addAction() {
        

        $request = $this->getRequest();
        
        $parentId = (int) $request->getParam('parent_id', 0);
        
        if($parentId < 0) {
            throw new Zend_Controller_Router_Exception('Invalid parent id for sitemap pages.', 404);
        }
        
        $parentType = '';
        
        $cmsSitemapPages = new Application_Model_DbTable_CmsSitemapPages();

        if($parentId != 0) {
            $parentSitemapPage = $cmsSitemapPages->getSitemapPageById($parentId);
            
            if(!$parentSitemapPage) {
              throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $parentId, 404);
            }
            
            $parentType = $parentSitemapPage['type'];
            
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
        );

        $form = new Application_Form_Admin_SitemapPageAdd($parentId, $parentType);

        //default form data
        $form->populate(array(

        ));

        if ($request->isPost() && $request->getPost('task') === 'save') { 

                try {

                        //check form is valid
                        if (!$form->isValid($request->getPost())) { 
                                throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new sitemapPage');
                        }
                        //ukoliko je validna forma
                        //get form data
                        $formData = $form->getValues(); //filtrirani i validirani podaci
                        unset($formData['csrf_token']);

//                        unset($formData['sitemap_page_photo']); //posto baca gresku jer u bazi ne postoji polje sitemap_page_photo zato ga brisemo

                        //inserujemo novi zapis instanciramo klasi
                        $formData['parent_id'] = $parentId;
                        
                        $sitemapPageId = $cmsSitemapPages->insertSitemapPage($formData);

//                        if( $form->getElement("sitemap_page_photo")->isUploaded() ) {
//
//                            $fileInfos = $form->getElement("sitemap_page_photo")->getFileInfo('sitemap_page_photo');
//                            $fileInfo = $fileInfos["sitemap_page_photo"];
//
//                            try {
//                                $sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
//                                $sitemapPagePhoto->fit(150, 150);
//                                $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPageId . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
//                            } catch (Exception $ex) {
//
//                                $flashMessenger->addMessage('SitemapPage has been saved but error occured during image processing.', 'errors');
//
//                                $redirector = $this->getHelper('Redirector');
//                                $redirector->setExit(true)
//                                        ->gotoRoute(array(
//                                                'controller' => 'admin_sitemapPages',
//                                                'action' => 'edit',
//                                                'id' => $sitemapPageId
//                                                            ), 'default', true);
//
//                            }
//
//
//                        }

                        // do actual task
                        //save to database etc


                        //set system message
                        $flashMessenger->addMessage('SitemapPage has been saved', 'success');

                        //redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi sitemapPagei
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                        'controller' => 'admin_sitemap',
                                        'action' => 'index',
                                        'id' => $parentId
                                                    ), 'default', true);
                } catch (Application_Model_Exception_InvalidInput $ex) {
                        $systemMessages['errors'][] = $ex->getMessage();
                }
        }

        $sitemapPagesBreadcrumbs = $cmsSitemapPages->getSitemapPageBreadcrumbs($parentId);
        
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->parentId = $parentId;
        $this->view->sitemapPagesBreadcrumbs = $sitemapPagesBreadcrumbs;
        
    }
    
    public function editAction() {
            
            $request = $this->getRequest();
            
            $id = (int) $request->getParam("id");
            
            if($id <= 0) {
                //prekida se izvrsavanje i prikazuje se page not found
                throw new Zend_Controller_Router_Exception('Invalid sitemapPage id: ' . $id , 404);
            }
            
            $cmsSitemapPages = new Application_Model_DbTable_CmsSitemapPages;
            
            $sitemapPage = $cmsSitemapPages->getSitemapPageById($id);
            
            if( empty($sitemapPage) ) {
                throw new Zend_Controller_Router_Exception('No sitemapPage is found with id: ' . $id , 404);
            }
            
            $parentType = '';
            
            if($sitemapPage['parent_id'] != 0) {
                $parentSitemapPage = $cmsSitemapPages->getSitemapPageById($sitemapPage['parent_id']);
                $parentType = $parentSitemapPage['type'];
            }
            
            $this->view->sitemapPage = $sitemapPage;
            
            $flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		$form = new Application_Form_Admin_SitemapPageEdit($sitemapPage['id'], $sitemapPage['parent_id'], $parentType);
               
		//default form data
		$form->populate( $sitemapPage );

		if ($request->isPost() && $request->getPost('task') === 'update') { 
                    
			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) { 
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for sitemapPage');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
//                                unset($formData['sitemapPage_photo']); //posto baca gresku jer u bazi ne postoji polje sitemapPage_photo zato ga brisemo
                                
//                                if( $form->getElement("sitemapPage_photo")->isUploaded() ) {
//                                
//                                    $fileInfos = $form->getElement("sitemapPage_photo")->getFileInfo('sitemapPage_photo');
//                                    $fileInfo = $fileInfos["sitemapPage_photo"];
//                                    
//                                    try {
//                                        $sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
//                                        $sitemapPagePhoto->fit(150, 150);
//                                        $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPage['id'] . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
//                                    } catch (Exception $ex) {
//                                        
//                                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing.');
//                                        
//                                    }
//                                    
//                                }
                                
                                
                                //radimo update postojeceg zapisa u tabeli
                                $cmsSitemapPages->updateSitemapPage( $sitemapPage['id'], $formData);
                                

				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('SitemapPage has been updated', 'success');

				//redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi sitemapPagei
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_sitemap',
						'action' => 'index',
                                                'id' => $sitemapPage['parent_id']
                                                            ), 'default', true);
			} catch (Application_Model_Exception_InvalidInput $ex) {
				$systemMessages['errors'][] = $ex->getMessage();
			}
		}

                $sitemapPagesBreadcrumbs = $cmsSitemapPages->getSitemapPageBreadcrumbs($sitemapPage['parent_id']);
                
		$this->view->systemMessages = $systemMessages;
		$this->view->form = $form;
                $this->view->sitemapPagesBreadcrumbs = $sitemapPagesBreadcrumbs;
            
        }
    
    public function disableAction() {
        
        $request = $this->getRequest();
            
        if(!$request->isPost() || $request->getPost('task') != 'disable' ) {

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index'
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');
            
        try {

            $id = (int) $request->getPost("id");

            if($id <= 0) {

                throw new Application_Model_Exception_InvalidInput("Invalid sitemap page id: " . $id );

            }

            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

            if( empty($sitemapPage) ) {

                throw new Application_Model_Exception_InvalidInput("No sitemap page is found with id: " . $id );

            }

            $cmsSitemapPagesTable->disableSitemapPage($id);

            $request instanceof Zend_Controller_Request_Http;

            if($request->isXmlHttpRequest()) {

                $responseJson = array(
                    'status' => 'ok',
                    'statusMessage' => "Sitemap page " . $sitemapPage['title'] . " has been disabled."
                );

                $this->getHelper('Json')->sendJson($responseJson);

            } else {

            $flashMessenger->addMessage("Sitemap page " . $sitemapPage['title'] . " has been disabled." , "success");

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
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
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'id' => $sitemapPage['parent_id']
                                ), 'default', true);
            }


        }
        
    }
        
    public function enableAction() {

        $request = $this->getRequest();
            
        if(!$request->isPost() || $request->getPost('task') != 'enable' ) {

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index'
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');
            
        try {

            $id = (int) $request->getPost("id");

            if($id <= 0) {

                throw new Application_Model_Exception_InvalidInput("Invalid sitemap page id: " . $id );

            }

            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

            if( empty($sitemapPage) ) {

                throw new Application_Model_Exception_InvalidInput("No sitemap page is found with id: " . $id );

            }

            $cmsSitemapPagesTable->enableSitemapPage($id);

            $request instanceof Zend_Controller_Request_Http;

            if($request->isXmlHttpRequest()) {

                $responseJson = array(
                    'status' => 'ok',
                    'statusMessage' => "Sitemap page " . $sitemapPage['title'] . " has been enabled."
                );

                $this->getHelper('Json')->sendJson($responseJson);

            } else {

            $flashMessenger->addMessage("Sitemap page " . $sitemapPage['title'] . " has been enabled." , "success");

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
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
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'id' => $sitemapPage['parent_id']
                                ), 'default', true);
            }


        }
        
        
    }

    public function deleteAction() {
            $request = $this->getRequest();
            if(!$request->isPost() || $request->getPost('task') != 'delete' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index'
                                ), 'default', true);
            }
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            try {
                
                $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                throw new Application_Model_Exception_InvalidInput("Invalid sitemap page id: " . $id );   
            }
            
            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
            
            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
            
            if( empty($sitemapPage) ) {
                throw new Application_Model_Exception_InvalidInput("No member is found with id: " . $id );
            }
            
                $cmsSitemapPagesTable->deleteSitemapPage($id);
                
                $flashMessenger->addMessage("Sitemap " . $sitemapPage["title"] . " has been deleted." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'id' => $sitemapPage['parent_id']
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'id' => $sitemapPage['parent_id']
                                ), 'default', true);
                
            }
            
		
            
        }
    
    public function updateorderAction() {
            
            $request = $this->getRequest();
            
            if(!$request->isPost() || $request->getPost('task') != 'saveOrder' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                $currentBreadcrumb = (int) $request->getPost('breadcrumb_id');
                
                if($currentBreadcrumb < 0) {
                    $currentBreadcrumb = 0;
                }
                
                $sortedIds = $request->getPost('sorted_ids');
                
                if(empty($sortedIds)) {
                    throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent.');
                }
                
                $sortedIds = trim($sortedIds, " ,");
                
                if(!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                    throw new Application_Model_Exception_InvalidInput("Invalid sorted ids.", $sortedIds);
                }
                
                $sortedIds = explode(',', $sortedIds);
                
                $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
                
                $cmsSitemapPagesTable->updateOrderOfSitemapPage($sortedIds);
                
                $flashMessenger->addMessage("Order is successfully saved", 'success');
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'id' => $currentBreadcrumb
                                ), 'default', true); 
                
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'id' => $currentBreadcrumb
                                ), 'default', true); 
                
            }
        }
}