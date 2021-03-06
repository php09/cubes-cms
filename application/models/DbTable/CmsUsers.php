<?php

class Application_Model_DbTable_CmsUsers extends Zend_Db_Table_Abstract 
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    const DEFAULT_PASSWORD = 'cubesphp';

    protected $_name = 'cms_users';

    /**
     * @param int $id
     * @return null/array Associative array with keys as cms_users table columns or returns null
     */
    public function getUserById($id) {

        $select = $this->select();
        $select->where('id = ?', $id);

        $row = $this->fetchRow($select);
        
        if( $row instanceof Zend_Db_Table_Row ) {
            return $row->toArray();
        } else {
            return null;
        }
    }

    /**
     * 
     * @param int $id
     * @param array $user Associative array with keys as column names and values as column new values
     */
    public function updateUser($id, $user) {
        
        if( isset($user['id']) ) {
            unset($user['id']);
        }
        
        $this->update($user, 'id = ' . $id);
        
    }
    
    /**
     * 
     * @param int $id
     * @param string $newPassword plain password
      */
    public function changeUserPassword($id, $newPassword) {
        
        $this->update( array('password' => md5($newPassword) ), 'id = ' . $id);
    }
    
    /**
     * 
     * @param Array $user Associative array with keys as column names and values as column new values
     * @return int id of new user   
     */
    public function insertUser($user) {
        
        $user['password'] = md5(self::DEFAULT_PASSWORD);
        
        return $this->insert($user);
    }
    
    /**
     * 
     * @param int $id Id of the user to enable
     */
        public function enableUser($id) {
        $this->update(array('status' => self::STATUS_ENABLED), 'id = ' . $id);
    }
    
    /**
     * 
     * @param int $id Id of the user to disable
     */
    public function disableUser($id) {
        $this->update(array('status' => self::STATUS_DISABLED), 'id = ' . $id);
    }
    
    public function resetPassword($id) {
        $this->update(array('password' => md5(self::DEFAULT_PASSWORD)), 'id = ' . $id);
    }
    
        /**
     * 
     * @param int $id Id of the user to delete
     */
    public function deleteUser($id) {
        $this->delete('id = ' . $id);
    }
    
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
                    case 'username':
                    case 'first_name':
                    case 'last_name':
                    case 'email':
                    case 'status':
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
                    case 'username':
                    case 'first_name':
                    case 'last_name':
                    case 'email':
                    case 'status':
                        
                        if(is_array($value)) {
                            $select->where( $field . ' IN (?) ', $value);
                        } else {
                            $select->where( $field . ' = ? ' , $value);
                        }
                        break;
                    case 'password':
                        if(is_array($value)) {
                            array_walk($value, function(&$element, $key) {
                                $element = md5($element);
                            } );
                            $select->where( $field . ' IN (?) ', $value );
                        } else {
                            $select->where( $field . ' = ? ' , md5($value));
                        }
                        break;
                    case 'first_name_search':
                        $select->where('first_name LIKE ?', '%' . $value . '%');
                        break;
                    case 'last_name_search':
                        $select->where('last_name LIKE ?', '%' . $value . '%');
                        break;
                    case 'email_search':
                        $select->where('email LIKE ?', '%' . $value . '%');
                        break;
                    case 'username_search':
                        $select->where('username LIKE ?', '%' . $value . '%');
                        break;
                    case 'id_exclude':
                        if(is_array($value)) {
                            $select->where('id NOT IN (?)', $value);
                        } else {
                            $select->where('id != ?', $value);
                        }
                        
                        break;
                    case 'username_exclude':
                        if(is_array($value)) {
                            $select->where('username NOT IN (?)', $value);
                        } else {
                            $select->where('username != ?', $value);
                        }
                        
                        break;
                    
                }
                
            }
    }
    
//    public function totalNumberOfUsers() {
//        $select = $this->select()->from($this, new Zend_Db_Expr('COUNT(id) as total'));
//        $total = $this->fetchRow($select);
//        return $total['total'];
//    }
//    
//    public function numberOfActiveUsers() {
//        $select = $this->select()->from($this, new Zend_Db_Expr('COUNT(id) as active'))->where('status = ' . self::STATUS_ENABLED);
//        $active = $this->fetchRow($select);
//        return $active['active'];
//    }
    
}
