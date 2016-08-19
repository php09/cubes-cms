<?php

class Admin_PhotogalleriesController extends Zend_Controller_Action
{
	
	public function indexAction() {
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors')
		);
		
		// prikaz svih memeber-a
		
		$cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
		
                $photoGalleries = $cmsPhotoGalleriesDbTable->search( array(
//                    'filters' => array(
//                        //'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_DISABLED
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
		
		$this->view->photoGalleries = $photoGalleries;
		$this->view->systemMessages = $systemMessages;
		
	}
	
	public function addAction() {
		
		$request = $this->getRequest();
                
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		$form = new Application_Form_Admin_PhotoGalleryAdd();
               
		//default form data
		$form->populate(array(
			
		));

		if ($request->isPost() && $request->getPost('task') === 'save') { 
                    
			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) { 
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new photoGallery');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
                                unset($formData['photo_gallery_leading_photo']); //posto baca gresku jer u bazi ne postoji polje photo_gallery_leading_photo zato ga brisemo
                                
                                //inserujemo novi zapis instanciramo klasi
                                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
                                
                                $photoGalleryId = $cmsPhotoGalleriesTable->insertPhotoGallery($formData);

                                if( $form->getElement("photo_gallery_leading_photo")->isUploaded() ) {
                                
                                    $fileInfos = $form->getElement("photo_gallery_leading_photo")->getFileInfo('photo_gallery_leading_photo');
                                    $fileInfo = $fileInfos["photo_gallery_leading_photo"];
                                    
                                    try {
                                        $photoGalleryPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
                                        $photoGalleryPhoto->fit(360, 270);
                                        $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGalleryId . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
                                    } catch (Exception $ex) {
                                        
                                        $flashMessenger->addMessage('Photo Gallery has been saved but error occured during image processing.', 'errors');

                                        $redirector = $this->getHelper('Redirector');
                                        $redirector->setExit(true)
                                                ->gotoRoute(array(
                                                        'controller' => 'admin_photogalleries',
                                                        'action' => 'edit',
                                                        'id' => $photoGalleryId
                                                                    ), 'default', true);
                                        
                                    }
                                    
                                    
                                }
                                
				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('Photo Gallery has been saved', 'success');

				//redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi photoGalleryi
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_photogalleries',
						'action' => 'edit',
                                                'id' => $photoGalleryId
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
                throw new Zend_Controller_Router_Exception('Invalid photoGallery id: ' . $id , 404);
            }
            
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries;
            
            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
            
            if( empty($photoGallery) ) {
                throw new Zend_Controller_Router_Exception('No photoGallery is found with id: ' . $id , 404);
            }
            
            $this->view->photoGallery = $photoGallery;
            
            
            
            $flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		$form = new Application_Form_Admin_PhotoGalleryEdit();
               
		//default form data
		$form->populate( $photoGallery );

		if ($request->isPost() && $request->getPost('task') === 'update') { 
                    
			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) { 
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for photoGallery');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
                                unset($formData['photo_gallery_leading_photo']); //posto baca gresku jer u bazi ne postoji polje photo_gallery_leading_photo zato ga brisemo
                                
                                
                                
                                if( $form->getElement("photo_gallery_leading_photo")->isUploaded() ) {
                                
                                    $fileInfos = $form->getElement("photo_gallery_leading_photo")->getFileInfo('photo_gallery_leading_photo');
                                    $fileInfo = $fileInfos["photo_gallery_leading_photo"];
                                    
                                    try {
                                        $photoGalleryPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
                                        $photoGalleryPhoto->fit(360, 270);
                                        $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGallery['id'] . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
                                    } catch (Exception $ex) {
                                        
                                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing.');
                                        
                                    }
                                    
                                    
                                }
                                
                                
                                
                                
                                //radimo update postojeceg zapisa u tabeli
                                $cmsPhotoGalleriesTable->updatePhotoGallery( $photoGallery['id'], $formData);
                                

				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('Photo Gallery has been updated', 'success');

				//redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi photoGalleryi
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_photogalleries',
						'action' => 'index'
                                                            ), 'default', true);
			} catch (Application_Model_Exception_InvalidInput $ex) {
				$systemMessages['errors'][] = $ex->getMessage();
			}
		}
                
                $cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
                $photos = $cmsPhotosDbTable->search(
                        array(
                            'filters' => array(
                                'photo_gallery_id' => $photoGallery['id']
                                ),
                            'orders' => array(
                                'order_number' => 'ASC'
                                )
                        ));

		$this->view->systemMessages = $systemMessages;
		$this->view->form = $form;
                
                $this->view->photoGallery = $photoGallery;
                $this->view->photos = $photos;
            
            
        }
        
        public function deleteAction() {
            
            $request = $this->getRequest();
            
            if(!$request->isPost() || $request->getPost('task') != 'delete' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid photoGallery id: " . $id );
                
            }
            
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries;
            
            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
            
            if( empty($photoGallery) ) {
                
                throw new Application_Model_Exception_InvalidInput("No photoGallery is found with id: " . $id );

            }
            
//                $cmsPhotoGalleriesTable->deletePhotoGallery($id, $photoGallery['order_number']);
                $cmsPhotoGalleriesTable->deletePhotoGallery($id);
                
                $flashMessenger->addMessage("PhotoGallery " . $photoGallery["title"] . " has been deleted." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
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
                                'controller' => 'admin_photogalleries',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid photoGallery id: " . $id );
                
            }
            
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries;
            
            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
            
            if( empty($photoGallery) ) {
                
                throw new Application_Model_Exception_InvalidInput("No photoGallery is found with id: " . $id );

            }
            
                $cmsPhotoGalleriesTable->disablePhotoGallery($id);
                $flashMessenger->addMessage("PhotoGallery " . $photoGallery["title"] . " has been disabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
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
                                'controller' => 'admin_photogalleries',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid photoGallery id: " . $id );
                
            }
            
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries;
            
            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
            
            if( empty($photoGallery) ) {
                
                throw new Application_Model_Exception_InvalidInput("No photoGallery is found with id: " . $id );

            }
            
                $cmsPhotoGalleriesTable->enablePhotoGallery($id);
                $flashMessenger->addMessage("PhotoGallery " . $photoGallery["title"] . " has been enabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'index'
                                ), 'default', true);
                
            }
            
		
            
        }
        
        public function updateorderAction() {
            
            $request = $this->getRequest();
            
            if(!$request->isPost() || $request->getPost('task') != 'saveOrder' ) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
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
                
                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries;
                
                $cmsPhotoGalleriesTable->updateOrderOfPhotoGallery($sortedIds);
                
                $flashMessenger->addMessage("Order is successfully saved", 'success');
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'index'
                                ), 'default', true); 
                
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
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
            
            $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
//            $totalPhotoGalleries = $cmsPhotoGalleriesDbTable->totalNumberOfPhotoGalleries(); //old domaci, ovako nije trebalo!
            $totalPhotoGalleries = $cmsPhotoGalleriesDbTable->count();
            $activePhotoGalleries = $cmsPhotoGalleriesDbTable->count( array( 'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED) );
            
            $this->view->totalPhotoGalleries = $totalPhotoGalleries;
            $this->view->activePhotoGalleries = $activePhotoGalleries;
        }
        
}