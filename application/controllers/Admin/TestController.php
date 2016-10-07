<?php


class Admin_TestController extends Zend_Controller_Action
{
    public function indexAction() {
        
    }
    
    public function jsintroAction() {
        
    }
    
    public function jqueryAction() {
        
    }
    
    public function ajaxintroAction() {
        
    }
    
    public function ajaxbrandsAction() {
        
        $brands = array(
            'fiat' => array(
                'punto' => 'Punto',
                'stilo' => 'Stilo',
                '500l' => '500 L'
            ),
            'opel' => array(
                'corsa' => 'Corsa',
                'astra' => 'Astra',
                'vectra' => 'Vectra',
                'insignia' => 'Insignia'
            ),
            'renault' => array(
                'twingo' => 'Twingo',
                'clio' => 'Clio',
                'megane' => 'Megane',
                'scenic' => 'Scenic'
            )
        );
        
        $brandsJson = array();
        
        foreach($brands AS $brand => $model) {
            $brandsJson[] = array(
                'value' => $brand,
                'label' => ucfirst($brand)
            );
        }
        
//        Zend_Layout::getMvcInstance()->disableLayout();
//        $this->getHelper('ViewRenderer')->setNoRender(true);
//        header('Content-type: application/json');
//        echo json_encode($brandsJson);
        
        $this->getHelper("Json")->sendJson($brandsJson);
    }
    
    public function ajaxmodelsAction() {
        
        
        $models = array(
            'fiat' => array(
                'punto' => 'Punto',
                'stilo' => 'Stilo',
                '500l' => '500 L'
            ),
            'opel' => array(
                'corsa' => 'Corsa',
                'astra' => 'Astra',
                'vectra' => 'Vectra',
                'insignia' => 'Insignia'
            ),
            'renault' => array(
                'twingo' => 'Twingo',
                'clio' => 'Clio',
                'megane' => 'Megane',
                'scenic' => 'Scenic'
            )
        );
        
        
        
        $request = $this->getRequest();
        
        $brand = $request->getParam('brand');
        
        if(!isset($models[$brand])) {
            throw new Zend_Controller_Router_Exception(' unknown brand', 404);
        }
        
        $models = $models[$brand];
        
        $modelsJson = array();
        
        foreach($models AS $modelId => $modelLabel) {
            $modelsJson[] = array(
                'id' => $modelId,
                'label' => $modelLabel
            );
        }
        
        $this->getHelper('Json')->sendJson($modelsJson);
        
    }
    
    
    public function soapAction() {
        
        $wsdl = "https://webservices.nbs.rs/CommunicationOfficeService1_0/ExchangeRateService.asmx?WSDL";
        
        $error = "";
        
        $currencyList = array();
        
        try {
            
            $soapClient = new Zend_Soap_Client_DotNet($wsdl); 
        
            
            
            $header = new SoapHeader( 
                    "http://communicationoffice.nbs.rs", 
                    "AuthenticationHeader", 
                    array(
                        "UserName" => "",
                        "Password" => "",
                        "LicenceID" => ""
                        ) 
                    );
            
            $soapClient->addSoapInputHeader($header);
            
            $responseRow = $soapClient->GetCurrentExchangeRate(array(
                "exchangeRateListTypeID" => 1
            ));
            
            if($responseRow->any) {
                $response = simplexml_load_string($responseRow->any);
                
                if($response->ExchangeRateDataSet && $response->ExchangeRateDataSet->ExchangeRate) {
                    $currencyList = $response->ExchangeRateDataSet->ExchangeRate;
                    
                }
                
            }
            
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }
        
        
        $this->view->soapClient = $soapClient;
        $this->view->response = $response;
        $this->view->error = $error;
        $this->view->currencyList = $currencyList;
    }
    
    
}
