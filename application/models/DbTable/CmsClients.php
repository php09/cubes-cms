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
 
    public function totalNumberOfClients() {
        $select = $this->select()->from($this, new Zend_Db_Expr('COUNT(id) as total'));
        $total = $this->fetchRow($select);
        return $total['total'];
    }
    
    public function numberOfActiveClients() {
        $select = $this->select()->from($this, new Zend_Db_Expr('COUNT(id) as active'))->where('status = ' . self::STATUS_ENABLED);
        $active = $this->fetchRow($select);
        return $active['active'];
    }
    
}