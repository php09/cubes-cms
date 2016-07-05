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
		
		$cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
		
		// $select je objekat klase Zend_Db_Select
		$select = $cmsMembersDbTable->select();
		
		$select->order('order_number');
		
		//degug za db select - vraca se sql upit
		//die($select->assemble());
		
		$members = $cmsMembersDbTable->fetchAll($select);
		
		$this->view->members = $members;
		$this->view->systemMessages = $systemMessages;
		
	}
	
	public function addAction() {
		
		$request = $this->getRequest(); //nalaze se podaci iz url iz forme koje prosledjujemo 
                //cuva dolazne podatke input data
                //getpost je getter za post podatke
		
		$flashMessenger = $this->getHelper('FlashMessenger');
                //prenosi sistemske poruke sa prethodnih stranica
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		
		$form = new Application_Form_Admin_MemberAdd();
               

		//default form data
		$form->populate(array(
			
		));

		if ($request->isPost() && $request->getPost('task') === 'save') { //ukoliko je pokrenuta forma
                    //da li je pokrenut post zahtev i dali je to hiden polje save  tj forma pokrenuta

			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) {  //da li su podaci sa forme validni asocijativni niz ciji su kljucevi name atriuti polja u formi 
                                    //a vrednisti su ono sto smo uneli u formi
                                    //ukooliko nisu podaci validni bacamo execption y slucaju da nisu dobri podaci
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new member');
				}
                                //ukoliko je validna forma
				//get form data
				$formData = $form->getValues(); //filtrirani i validirani podaci
                                
                                
                                //inserujemo novi zapis instanciramo klasi
                                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                                $cmsMembersTable->insert($formData);
                                

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

}

