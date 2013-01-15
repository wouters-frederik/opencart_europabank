<?php

class ModelPaymentEuropabank extends Model {

  	public function getMethod( $address ) {

		if ( $this->config->get( 'europabank_status' ) == '1' ) {

			$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "europabank_transactions` ( `order_id` INT( 11 ) NOT NULL ,`trxid` VARCHAR( 16 ) NOT NULL ,PRIMARY KEY ( `trxid` ) ) ENGINE = MYISAM COMMENT = 'Link order_id to TargetPay trxid';" );

		}

		$this->language->load( 'payment/europabank' );

		if ( $this->config->get( 'europabank_status' ) ) {

      		$query = $this->db->query( "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get( 'europabank_geo_zone_id' ) . "' AND country_id = '" . (int)$address[ 'country_id' ] . "' AND (zone_id = '" . (int)$address[ 'zone_id' ] . "' OR zone_id = '0')" );

			if ( ! $this->config->get( 'europabank_geo_zone_id' ) ) {

        		$status = true;

      		} elseif ( $query->num_rows ) {

      		  	$status = true;

      		} else {

     	  		$status = false;

			}

      	} else {

			$status = false;

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

	public function getOrderIdByTransaction( $trxid ) {

		$query = $this->db->query( "SELECT order_id FROM `" . DB_PREFIX . "europabank_transactions` WHERE trxid = '" . $trxid . "'" );

		if ( $query->num_rows > 0 ) {

			return $query->row[ 'order_id' ];

		} else {

			return 0;

		}

	}

	public function insertOrderTransaction( $order_id, $trxid ) {

		$query = $this->db->query( "INSERT INTO `" . DB_PREFIX . "europabank_transactions` ( `order_id` ,`trxid` ) VALUES ( '" . $order_id . "', '" . $trxid . "');" );

		return true;

	}

	public function deleteOrderTransaction( $trxid ) {

		$query = $this->db->query( "DELETE FROM `" . DB_PREFIX . "europabank_transactions` WHERE `" . DB_PREFIX . "europabank_transactions`.`trxid` = '" . $trxid . "' LIMIT 1" );

		return true;

	}

}
