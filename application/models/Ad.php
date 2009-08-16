<?php

/**
 * Nolotiro_Ad - a model class representing an ad 
 *
 * This is the DbTable class for the ads table.
 *  
 */
class Model_Ad extends Zend_Db_Table_Abstract  {
	protected $_name = 'ads';
	protected $_primary = "id";
	protected $_dependentTables = array('comments');

    
	/** Model_Table_Page */
	protected $_table;
	
	
	public function addAd($body, $title) {

		$data = array ('body' => $body, 'title' => $title );
		$this->insert ( $data );
	}
	function updateAd($id, $body, $title) {
		$data = array ('body' => $body, 'title' => $title );
		$this->update ( $data, 'id = ' . ( int ) $id );
	}
	function deleteAd($id) {
		$this->delete ( 'id =' . ( int ) $id );
	}



	
	
	
	/**
	 * Save a new entry
	 * 
	 * @param  array $data 
	 * @return int|string
	 */
	public function save(array $data) {
		$table = new Model_Ad ();
		$fields = $table->info ( Zend_Db_Table_Abstract::COLS );
		foreach ( $data as $field => $value ) {
			if (! in_array ( $field, $fields )) {
				unset ( $data [$field] );
			}
		}
		return $table->insert ( $data );
	}
	
	/**
	 * Fetch an individual entry
	 * 
	 * @param  int|string $id 
	 * @return null|Zend_Db_Table_Row_Abstract
	 */
	public function getAd($id) {
		$id = ( int ) $id;
		
		$table = new Model_Ad ( );
		$select = $table->select ()->where ( 'id = ?', $id );
		
		if (!$table->fetchRow ( $select )) {
			throw new Exception ( "Count not find row $id" );
			
		} else {
		    
		    //$result = $table->fetchRow ( $select )->findDependentRowset('Comment')->toArray ();
			
		    
		    $result = $table->fetchRow ( $select )->toArray ();
			
		}
		
		return $result;
		
	}
	
	/**
	 * Fetch the comments of an ad
	 * 
	 * @param  int|string $id 
	 * @return null|Zend_Db_Table_Row_Abstract
	 */
	public function getComments($id) {
		$id = ( int ) $id;
		
		$table = new Model_Ad ( );
		$select = $table->select ()->where ( 'id = ?', $id );
		
		$result = $table->fetchRow ( $select )->findDependentRowset('Comment')->toArray ();
		
		return $result;
		
	}

	
	/**
	 * Fetch a list of ads where woeid and ad_type matches 
	 * 
	 * @param  int $woeid
	 * @param  string $ad_type  
	 * @return array list of ads with this params
	 */
	public function getAdList($woeid,$ad_type) {
		$woeid = ( int ) $woeid;
		$ad_type = ( string ) $ad_type;
		
		
		$table = new Model_Ad ( );
		$select = $table->select ()->where ( 'woeid_code = ?', $woeid  )
		->where ( 'type = ?', $ad_type )
		->order('date_created DESC')
		;
		
		
		$result = $table->fetchAll ( $select )->toArray ();

		
		return $result;
		
	}

	
	
}






class Comment extends Zend_Db_Table_Abstract  {
	protected $_name = 'comments';
    
	protected $_referenceMap    = array(
    'Ad' => array(
        'columns'           => array('ads_id'),
        'refTableClass'     => 'Model_Ad',
        'refColumns'        => array('id')
    )
);

	
	
	

	/**
	 * Fetch a list of comments where id_ad_parent match 
	 * 
	 * @param  int $ads_id
	 * @return array list of ads with this params
	 */
	public function getComments($ads_id) {
		$id_ad_parent = ( int ) $ads_id;
		
		$table = $this->getTable ();
		$select = $table->select ()->where ( 'ads_id = ?', $ads_id  )
		->order('date_created ASC');
		
		
		$result = $table->fetchAll ( $select )->toArray ();
		
		return $result;
		
	}

	
	
}



