<?php
/**
 * TargetPay iDeal
 *
 * This payment module allows you to integrate iDeal by TargetPay
 * into your OpenCart webshop system.
 *
 * ----------------------------------------------------------------
 *
 * LICENSE: This program is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * @package   TargetPay iDael
 * @author    Martijn T. Dwars <info@2bytes.nl>
 * @copyright 2010 2bytes webdevelopment
 * @version   v1.0.0
 */

include 'europabank_ideal.php';
include 'europabank_request.php';


/**
 * TargetPay iDeal class
 *
 * @package   TargetPay iDeal
 * @author    Martijn T. Dwars <info@2bytes.nl>
 */
class ControllerPaymentEuropabank extends Controller {

  /**
   * @var object Holds the EuropabankIdeal object
   */
  private $EuropabankIdeal;


  /**
   * Called by OpenCart at the third step of the checkout process
   *
   * @return void
   */
  protected function index( ) {

    $this->EuropabankIdeal = new EuropabankIdeal( );

    $this->load->model( 'checkout/order' );

    $aOrder             = $this->model_checkout_order->getOrder( $this->session->data[ 'order_id' ] );
    $this->data[ 'button_confirm' ] = $this->language->get( 'button_confirm' );
    $this->data[ 'button_back' ]  = $this->language->get( 'button_back' );
    $this->data[ 'layoutcode' ]   = $this->config->get( 'europabank_layoutcode' );
    $this->data[ 'banklist' ]   = $this->EuropabankIdeal->getDirectory( );
    $this->data[ 'action' ]     = HTTPS_SERVER . 'index.php?route=payment/europabank/bank&order_id=' . $aOrder[ 'order_id' ];
    $this->data[ 'back' ]     = HTTPS_SERVER . 'index.php?route=checkout/payment';
    $this->id           = 'payment';

    if ( file_exists( DIR_TEMPLATE . $this->config->get( 'config_template' ) . '/template/payment/europabank.tpl' ) ) {

      $this->template = $this->config->get( 'config_template' ) . '/template/payment/europabank.tpl';

    } else {

      $this->template = 'default/template/payment/europabank.tpl';

    }

    $this->render( );

  }

  /**
   * Called by OpenCart at the third step of the checkout process
   *
   * @return void
   */
  public function bank( ) {

    $this->EuropabankIdeal = new EuropabankIdeal( );

    $this->load->language( 'payment/europabank' );
    $this->load->model( 'checkout/order' );
    $this->load->model( 'payment/europabank' );

    $aOrder     = $this->model_checkout_order->getOrder( $this->session->data[ 'order_id' ] );
    $iLayout    = $this->config->get( 'europabank_layoutcode' );
    $sIssuerID    = $this->request->post[ 'issuer_id' ];
    $iAmount    = $this->currency->format( $aOrder[ 'total' ], $aOrder[ 'currency' ], $aOrder[ 'value' ], false ) * 100;
    $sDescription = urlencode( html_entity_decode( $this->config->get( 'config_name' ), ENT_QUOTES, 'UTF-8' ) . ' - #' . $aOrder[ 'order_id' ] );
    $sReturnURL   = urlencode( HTTPS_SERVER . 'index.php?route=payment/europabank/callback' );
    $sReportURL   = urlencode( HTTPS_SERVER . 'index.php?route=payment/europabank/report' );

    $bPayment   = $this->EuropabankIdeal->setLayout( (int)$iLayout )
                         ->setIssuer( $sIssuerID )
                         ->setAmount( (int)$iAmount )
                         ->setDescription( $sDescription )
                         ->setReturnURL( $sReturnURL )
                         ->setReportURL( $sReportURL )
                         ->startPayment( );

    if ( $bPayment ) {

      $this->model_payment_europabank->insertOrderTransaction( $aOrder[ 'order_id' ], $this->EuropabankIdeal->getTransactionID( ) );

      $this->redirect( $this->EuropabankIdeal->getURL( ) );

    }

  }

  /**
   * Called when the visitor returns at our webshop
   *
   * @return void
   */
  public function callback( ) {

    $this->EuropabankIdeal = new EuropabankIdeal( );

    $this->load->language( 'payment/europabank' );
    $this->load->model( 'checkout/order' );
    $this->load->model( 'payment/europabank' );

    $trxid    = $this->request->get[ 'trxid' ];
    $orderid  = $this->model_payment_europabank->getOrderIdByTransaction( $trxid );
    $order    = $this->model_checkout_order->getOrder( $orderid );
    $layout   = $this->config->get( 'europabank_layoutcode' );
    $testmode = $this->config->get( 'europabank_testmode' );

    if ( $order[ 'order_status_id' ] != $this->config->get( 'europabank_order_status_id' ) ) {

      $payed = $this->EuropabankIdeal->setLayout( (int)$layout )
                      ->setTestmode( ( $testmode == '1' ) )
                      ->setTransactionID( $trxid )
                      ->checkPayment( );

      if ( $payed ) {

        $this->model_checkout_order->confirm( $orderid, $this->config->get( 'europabank_order_status_id' ) );

        $payed = true;

      } else {

        $payed = false;

      }

    } else {

      $payed = true;

    }

    if ( isset( $payed ) && $payed ) {

      $this->model_payment_europabank->deleteOrderTransaction( $trxid );

      header( 'Location: ' . HTTPS_SERVER . 'index.php?route=checkout/success' );

    } else {

      echo 'Helaas, de betaling is niet gelukt..';

    }

  }

  /**
   * Make sure a order is updated, even if the customer doesn't reach the callback page
   *
   * @return void
   */
  public function report( ) {

    $this->EuropabankIdeal = new EuropabankIdeal( );

    $this->load->model( 'checkout/order' );
    $this->load->model( 'payment/europabank' );

    if ( isset( $this->request->get[ 'trxid' ] ) ) {

      $trxId = $this->request->get[ 'trxid' ];
      $layout = $this->config->get( 'europabank_layoutcode' );
      $testmode = $this->config->get( 'europabank_testmode' );

      $payed = $this->EuropabankIdeal->setLayout( (int)$layout )
                      ->setTestmode( ( $testmode == '1' ) )
                      ->setTransactionID( $trxId )
                      ->checkPayment( );

      if ( $payed ) {

        $orderId = $this->model_payment_europabank->getOrderIdByTransaction( $trxId );
        $this->model_checkout_order->confirm( $orderId, $this->config->get( 'europabank_order_status_id' ) );

      }

    }

  }

}