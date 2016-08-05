<?php

class Application_Model_DbTable_CmsClients extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_clients';
    
    public function getClientById($id) {
        $select = $this->select();
        $select->where('id = ?', $id);
        $row = $this->fetchRow($select);
        if( $row instanceof Zend_Db_Table_Row ) {
            return $row->toArray();
        } else {
            return null;
        }
    }
    
    public function updateClient($id, $client) {
        if( isset($client['id']) ) {
            unset($client['id']);
        }
        $this->update($client, 'id = ' . $id);
    }
        
    /**
     * Function returns following sql query:
     * SELECT MAX(order_number) AS max FROM `cms_clients`
     * @return int Maximum of the order_number column
     */
    public function getMaxOrderNumber() {
        $max = $this->select();
        $max->from($this, new Zend_Db_Expr('MAX(order_number) AS max'));
        $max = $this->fetchRow($max);
        return $max['max'];
    }
    
    
    /**
     * 
     * @param Array $client Associative array with keys as column names and values as column new values
     * @return int The id of new client (auto increment)
     */
    public function insertClient($client) {
        $select = $this->select();
        $select->order('order_number DESC');
        $clientWithBiggestOrderNumber = $this->fetchRow($select);
        if($clientWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {
            $client['order_number'] = $clientWithBiggestOrderNumber['order_number'] + 1;
        } else {
            $client['order_number'] = 1;
        }
        $id = $this->insert($client);
        return $id;
    }
    
    /**
     * 
     * @param int $id Id of the client to delete
     */
    public function deleteClient($id) {
        $client = $this->getClientById($id);
        $this->update( array(
            'order_number' => new Zend_Db_Expr('order_number - 1') 
            ), 'order_number' > $client['order_number']);
        $this->delete('id = ' . $id);
    }
    
    /**
     * 
     * @param int $id Id of the client to enagle
     */
    public function enableClient($id) {
        $this->update(array('status' => self::STATUS_ENABLED), 'id = ' . $id);
    }
    
    /**
     * 
     * @param int $id Id of the client to disable
     */
    public function disableClient($id) {
        $this->update(array('status' => self::STATUS_DISABLED), 'id = ' . $id);
    }
    
    public function updateOrderOfClient($sortedIds) {
        foreach($sortedIds as $orderNumber=>$id) {
            $this->update(array('order_number' => $orderNumber + 1), 'id = ' . $id);
        }
    }
 
//    public function totalNumberOfClients() {
//        $select = $this->select()->from($this, new Zend_Db_Expr('COUNT(id) as total'));
//        $total = $this->fetchRow($select);
//        return $total['total'];
//    }
//    
//    public function numberOfActiveClients() {
//        $select = $this->select()->from($this, new Zend_Db_Expr('COUNT(id) as active'))->where('status = ' . self::STATUS_ENABLED);
//        $active = $this->fetchRow($select);
//        return $active['active'];
//    }
 
    /**
     * Array parameters is keeping search parameters
     * Array parameters must be  in following format:
     *      array(
     *          "filters" => array(
     *                  "status" => 1,
     *                  "id= => array(3, 8 ,11),
     *                  "orders" => array(
     *                                  "username" => ASC, 
     *                                  "lastname" => DESC,
     *                                   ),
     *                  "limit" => 50, //limit result set to 50 rows
     *                  "page" => 3 //start from page 3, if no limit is set, page is ignored
     *                  )
     * @param array $parameters Associative array with keys filters, orders, limit and page
     */
    public function search(array $parameters = array() ) {
        $select = $this->select();
        
        if(isset($parameters['filters'])) {
            $filters = $parameters['filters'];
            
            $this->processFilters($filters, $select);
            
            
        }
        
        if(isset($parameters['orders'])) {
            $orders = $parameters['orders'];
            
            foreach($orders AS $field => $orderDirection) {
                
                switch($field) {
                    case 'id':
                    case 'name':
                    case 'status':
                    case 'order_number':
                         if($orderDirection === 'DESC') {
                             $select->order($field . ' DESC ');
                         } else {
                             $select->order($field);
                         }
                        break;
                }
                
            }
            
        }
        
        if(isset($parameters['limit'])) {
            
            if(isset($parameters['page'])) {
                //pagination is set, do limit by page
                $select->limitPage($parameters['page'], $parameters['limit']);
            } else {
                //page is not set, just do regular
                $select->limit($parameters['limit']);
            }
            
        }
        return $this->fetchAll($select)->toArray();
    }
    
    /**
     * 
     * @param array $filters see function search $parameters['fields']
     * @return int Count of rows that match $filters
     */
    public function count( array $filters = array()) {
        $select = $this->select();
        
        $this->processFilters($filters, $select);
        
        $select->reset('columns');
        $select->from( $this->_name, 'COUNT(*) AS total');
        
        $row = $this->fetchRow($select)->total;
        
        return $row;
    }
    
    
    /**
     * fill $select object with WHERE conditions
     * @param array $filters
     * @param Zend_Db_Select $select
     */
    protected function processFilters(array $filters = array(), Zend_Db_Select $select) {
    
        //selected object will be modified outside this function
        //objects are always passed by reference
        
        foreach($filters as $field => $value) {
                
//                if($field == 'id') {
//                    if(is_array($value)) {
//                        $select->where('id IN ( ? )', $value);
//                    } else {
//                        $select->where('id = ?', $value);
//                    }
//                }
                
                
                switch($field) {
                    case 'id':
                    case 'name':
                    case 'status':
                    case 'order_number':
                        if(is_array($value)) {
                            $select->where( $field . ' IN (?) ', $value);
                        } else {
                            $select->where( $field . ' = ? ' , $value);
                        }
                        break;
                    case 'name_search':
                        $select->where('first_name LIKE ?', '%' . $value . '%');
                        break;
                }
            }
    }
    
    
}