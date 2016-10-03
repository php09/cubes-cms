<?php

class Admin_MembersController extends Zend_Controller_Action
{
	
	public function indexAction() {
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors')
		);
		
		// prikaz svih memeber-a
		

                $cache = Zend_Registry::get('mycache');
                $members = $cache->load('members');
		
                if(!$members) {
                    		$cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
		
                $members = $cmsMembersDbTable->search( array(
//                    'filters' => array(
//                        //'status' => Application_Model_DbTable_CmsMembers::STATUS_DISABLED
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
                    $cache->save($members, "members");
                }
                
		$this->view->members = $members;
		$this->view->systemMessages = $systemMessages;
		
	}
	
	public function addAction() {
		
		$request = $this->getRequest();
                
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		$form = new Application_Form_Admin_MemberAdd();
               
		//default form data
		$form->populate(array(
			
		));

		if ($request->isPost() && $request->getPost('task') === 'save') { 
                    
			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) { 
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new member');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
                                unset($formData['member_photo']); //posto baca gresku jer u bazi ne postoji polje member_photo zato ga brisemo
                                
                                //inserujemo novi zapis instanciramo klasi
                                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                                
                                $memberId = $cmsMembersTable->insertMember($formData);

                                if( $form->getElement("member_photo")->isUploaded() ) {
                                
                                    $fileInfos = $form->getElement("member_photo")->getFileInfo('member_photo');
                                    $fileInfo = $fileInfos["member_photo"];
                                    
                                    try {
                                        $memberPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
                                        $memberPhoto->fit(150, 150);
                                        $memberPhoto->save(PUBLIC_PATH . '/uploads/members/' . $memberId . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
                                    } catch (Exception $ex) {
                                        
                                        $flashMessenger->addMessage('Member has been saved but error occured during image processing.', 'errors');

                                        $redirector = $this->getHelper('Redirector');
                                        $redirector->setExit(true)
                                                ->gotoRoute(array(
                                                        'controller' => 'admin_members',
                                                        'action' => 'edit',
                                                        'id' => $memberId
                                                                    ), 'default', true);
                                        
                                    }
                                    
                                    
                                }
                                
				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('Member has been saved', 'success');

				//redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi memberi
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_members',
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
                throw new Zend_Controller_Router_Exception('Invalid member id: ' . $id , 404);
            }
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers;
            
            $member = $cmsMembersTable->getMemberById($id);
            
            if( empty($member) ) {
                throw new Zend_Controller_Router_Exception('No member is found with id: ' . $id , 404);
            }
            
            $this->view->member = $member;
            
            
            
            $flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		$form = new Application_Form_Admin_MemberAdd();
               
		//default form data
		$form->populate( $member );

		if ($request->isPost() && $request->getPost('task') === 'update') { 
                    
			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) { 
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for member');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
                                unset($formData['member_photo']); //posto baca gresku jer u bazi ne postoji polje member_photo zato ga brisemo
                                
                                
                                
                                if( $form->getElement("member_photo")->isUploaded() ) {
                                
                                    $fileInfos = $form->getElement("member_photo")->getFileInfo('member_photo');
                                    $fileInfo = $fileInfos["member_photo"];
                                    
                                    try {
                                        $memberPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo["tmp_name"]);
                                        $memberPhoto->fit(150, 150);
                                        $memberPhoto->save(PUBLIC_PATH . '/uploads/members/' . $member['id'] . '.jpg'); //na osnovu ovde navedene ekstenzije automatski i konvertuje u taj format
                                    } catch (Exception $ex) {
                                        
                                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing.');
                                        
                                    }
                                    
                                    
                                }
                                
                                
                                
                                
                                //radimo update postojeceg zapisa u tabeli
                                $cmsMembersTable->updateMember($member['id'], $formData);
                                

				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('Member has been updated', 'success');

				//redirect to same or another page po nasoj ideji bacamo na stranicu gde su svi memberi
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_members',
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
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid member id: " . $id );
                
            }
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers;
            
            $member = $cmsMembersTable->getMemberById($id);
            
            if( empty($member) ) {
                
                throw new Application_Model_Exception_InvalidInput("No member is found with id: " . $id );

            }
            
//                $cmsMembersTable->deleteMember($id, $member['order_number']);
                $cmsMembersTable->deleteMember($id);
                
                $flashMessenger->addMessage("Member " . $member["first_name"] . " " . $member["last_name"] . " has been deleted." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
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
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid member id: " . $id );
                
            }
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers;
            
            $member = $cmsMembersTable->getMemberById($id);
            
            if( empty($member) ) {
                
                throw new Application_Model_Exception_InvalidInput("No member is found with id: " . $id );

            }
            
                $cmsMembersTable->disableMember($id);
                $flashMessenger->addMessage("Member " . $member["first_name"] . " " . $member["last_name"] . " has been disabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
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
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            
            try {
                
                
            $id = (int) $request->getPost("id");
            
            if($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput("Invalid member id: " . $id );
                
            }
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers;
            
            $member = $cmsMembersTable->getMemberById($id);
            
            if( empty($member) ) {
                
                throw new Application_Model_Exception_InvalidInput("No member is found with id: " . $id );

            }
            
                $cmsMembersTable->enableMember($id);
                $flashMessenger->addMessage("Member " . $member["first_name"] . " " . $member["last_name"] . " has been enabled." , "success");
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);

            } catch (Application_Model_Exception_InvalidInput $ex) {

                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
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
                                'controller' => 'admin_members',
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
                
                $cmsMembersTable = new Application_Model_DbTable_CmsMembers;
                
                $cmsMembersTable->updateOrderOfMember($sortedIds);
                
                $flashMessenger->addMessage("Order is successfully saved", 'success');
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true); 
                
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                $flashMessenger->addMessage($ex->getMessage());
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
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
            
            $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
//            $totalMembers = $cmsMembersDbTable->totalNumberOfMembers(); //old domaci, ovako nije trebalo!
            $totalMembers = $cmsMembersDbTable->count();
            $activeMembers = $cmsMembersDbTable->count( array( 'status' => Application_Model_DbTable_CmsMembers::STATUS_ENABLED) );
            
            $this->view->totalMembers = $totalMembers;
            $this->view->activeMembers = $activeMembers;
        }
}