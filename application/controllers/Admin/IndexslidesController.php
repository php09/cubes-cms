<?php

class Admin_IndexslidesController extends Zend_Controller_Action
{
	
	public function indexAction() {
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors')
		);
		
		// prikaz svih memeber-a
		
		$cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
		
                $indexSlides = $cmsIndexSlidesDbTable->search( array(
//                    'filters' => array(
//                        //'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_DISABLED
//                        //'first_name' => 'Aleksandar'
//                        //'work_title' => 'PHP Developer'
////                        'first_name_search' => 'ale'
//                        'id' => array(1,3,5,7)
//                    ),
                    'orders' => array(
                        'order_number' => 'ASC'
                        ) /*,
                    'limit' => 4,
                    'page' => 1 */
                ) );
		
		$this->view->indexSlides = $indexSlides;
		$this->view->systemMessages = $systemMessages;
		
	}
	
	public function addAction() {
		
		$request = $this->getRequest();
                
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		$form = new Application_Form_Admin_IndexSlideAdd();
               
		//default form data
		$form->populate(array(
			
		));

		if ($request->isPost() && $request->getPost('task') === 'save') { 
                    
			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) { 
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new indexSlide');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
                                unset($formData['index_slide_photo']); //posto baca gresku jer u bazi ne postoji polje index_slide_photo zato ga brisemo
                                
                                //inserujemo novi zapis instanciramo klasi
                                $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();
                                
                                $indexSlideId = $cmsIndexSlidesTable->insertIndexSlide($formData);

                                if( $form->getElement("index_slide_photo")->isUploaded() ) {
                                
                                    $fileInfos = $form->getElement("index_slide_photo")->getFileInfo('index_slide_photo');
                                    $fileInfo = $fileInfos["index_slide_photo"];
                                    
                                    try {
                                        $indexSlidePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
                                        $indexSlidePhoto->fit(600, 400);
                                        $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlideId . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
                                    } catch (Exception $ex) {
                                        
                                        $flashMessenger->addMessage('IndexSlide has been saved but error occured during image processing.', 'errors');

                                        $redirector = $this->getHelper('Redirector');
                                        $redirector->setExit(true)
                                                ->gotoRoute(array(
                                                        'controller' => 'admin_indexslides',
                                                        'action' => 'edit',
                                                        'id' => $indexSlideId
                                                                    ), 'default', true);
                                        
                                    }
                                    
                                    
                                }
                                
				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('IndexSlide has been saved', 'success');

				//redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi indexSlidei
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_indexslides',
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
                //prekida se izvrsavanje i prikazuje se page not found
                throw new Zend_Controller_Router_Exception('Invalid indexSlide id: ' . $id , 404);
            }
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides;
            
            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);
            
            if( empty($indexSlide) ) {
                throw new Zend_Controller_Router_Exception('No indexSlide is found with id: ' . $id , 404);
            }
            
            $this->view->indexSlide = $indexSlide;
            
            
            
            $flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		$form = new Application_Form_Admin_IndexSlideAdd();
               
		//default form data
		$form->populate( $indexSlide );

		if ($request->isPost() && $request->getPost('task') === 'update') { 
                    
			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) { 
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for indexSlide');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
                                unset($formData['index_slide_photo']); //posto baca gresku jer u bazi ne postoji polje index_slide_photo zato ga brisemo
                                
                                
                                
                                if( $form->getElement("index_slide_photo")->isUploaded() ) {
                                
                                    $fileInfos = $form->getElement("index_slide_photo")->getFileInfo('index_slide_photo');
                                    $fileInfo = $fileInfos["index_slide_photo"];
                                    
                                    try {
                                        $indexSlidePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
                                        $indexSlidePhoto->fit(600, 400);
                                        $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlide['id'] . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
                                    } catch (Exception $ex) {
                                        
                                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing.');
                                        
                                    }
                                    
                                    
                                }
                                
                                
                                
                                
                                //radimo update postojeceg zapisa u tabeli
                                $cmsIndexSlidesTable->updateIndexSlide( $indexSlide['id'], $formData);
                                

				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('IndexSlide has been updated', 'success');

				//redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi indexSlidei
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_indexslides',
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
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid indexSlide id: " . $id );
                
            }
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides;
            
            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);
            
            if( empty($indexSlide) ) {
                
                throw new Application_Model_Exception_InvalidInput("No indexSlide is found with id: " . $id );

            }
            
//                $cmsIndexSlidesTable->deleteIndexSlide($id, $indexSlide['order_number']);
                $cmsIndexSlidesTable->deleteIndexSlide($id);
                
                $flashMessenger->addMessage("IndexSlide " . $indexSlide["title"] . " has been deleted." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
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
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid indexSlide id: " . $id );
                
            }
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides;
            
            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);
            
            if( empty($indexSlide) ) {
                
                throw new Application_Model_Exception_InvalidInput("No indexSlide is found with id: " . $id );

            }
            
                $cmsIndexSlidesTable->disableIndexSlide($id);
                $flashMessenger->addMessage("IndexSlide " . $indexSlide["title"]  . " has been disabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
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
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid indexSlide id: " . $id );
                
            }
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides;
            
            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);
            
            if( empty($indexSlide) ) {
                
                throw new Application_Model_Exception_InvalidInput("No indexSlide is found with id: " . $id );

            }
            
                $cmsIndexSlidesTable->enableIndexSlide($id);
                $flashMessenger->addMessage("IndexSlide " . $indexSlide["title"] . " has been enabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
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
                                'controller' => 'admin_indexslides',
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
                
                $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides;
                
                $cmsIndexSlidesTable->updateOrderOfIndexSlide($sortedIds);
                
                $flashMessenger->addMessage("Order is successfully saved", 'success');
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true); 
                
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
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
            
            $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
            $totalIndexSlides = $cmsIndexSlidesDbTable->count();
            $activeIndexSlides = $cmsIndexSlidesDbTable->count( array( 'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED) );
            
            $this->view->totalIndexSlides = $totalIndexSlides;
            $this->view->activeIndexSlides = $activeIndexSlides;
        }
}