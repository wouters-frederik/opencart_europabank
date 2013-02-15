<?php
/**
 * Europabank MODEL
 */

class ModelPaymentEuropabank extends Model {

  	public function getMethod( $address ) {
    $this->language->load( 'payment/europabank' );

    if ( $status = $this->config->get( 'europabank_status' ) == '1' ) {
			$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "europabank_transactions` ( `order_id` INT( 11 ) NOT NULL ,`trxid` VARCHAR( 16 ) NOT NULL, `description` VARCHAR( 255 ) NOT NULL ,PRIMARY KEY ( `trxid` ) ) ENGINE = MYISAM COMMENT = 'Link order_id to TargetPay trxid';" );

		}

		if ($status) {
      $query = $this->db->query( "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get( 'europabank_geo_zone_id' ) . "' AND country_id = '" . (int)$address[ 'country_id' ] . "' AND (zone_id = '" . (int)$address[ 'zone_id' ] . "' OR zone_id = '0')" );

			if ( ! $this->config->get( 'europabank_geo_zone_id' ) ) {
        		$status = true;
      		} elseif ( $query->num_rows ) {
      		  	$status = true;
      		} else {
     	  		$status = false;
			}
    }

		$method_data = array( );
		if ( $status ) {
      		$method_data = array(
        		'id'         => 'europabank',
        		'title'      => $this->language->get( 'text_title' ),
            'code'      => 'europabank',
				    'sort_order' => $this->config->get( 'europabank_sort_order' )
      		);
    	}
    	return $method_data;

 	}


  /**
   * Load the transactions for an order.
   */
	public function getTransactionByOrderId( $order_id ) {
		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "customer_transaction` WHERE order_id = '" . $order_id . "' order by customer_transaction_id DESC LIMIT 1" );
		if ( $query->num_rows > 0 ) {
			return $query->row;
		} else {
			return 0;
		}
	}

  /**
   * Add a new transaction.
   */
	public function insertOrderTransaction( $order_id, $description, $amount, $customer_id) {
		$query = $this->db->query( "INSERT INTO `" . DB_PREFIX . "customer_transaction` ( `order_id` , `description`, `amount`, `customer_id`, `date_added`) VALUES ( '" . $order_id . "', '" . $description . "' ," . $amount . "," . $customer_id . " , NOW() );" );
    return $this->db->getLastId();
	}

  /**
   * Delete an old transaction.
   */
	public function deleteOrderTransaction( $trxid ) {

		$query = $this->db->query( "DELETE FROM `" . DB_PREFIX . "customer_transaction` WHERE `" . DB_PREFIX . "customer_transaction`.`customer_transaction_id` = '" . $trxid . "' LIMIT 1" );
		return true;

	}

}
