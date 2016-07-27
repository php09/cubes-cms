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
    
}
