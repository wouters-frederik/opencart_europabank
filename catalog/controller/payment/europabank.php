<?php
/**
 * opencart Europabank
 *
 * This payment method allows you to integrate europabank payments into your opencart.
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
 * @package   Europabank Opencart
 * @author    Frederik Wouters <wouters.f@gmail.com>
 * @copyright 2012 it2servu BVBA
 * @version   v1.0.0
 */


class ControllerPaymentEuropabank extends Controller {

  private $EuropabankIdeal;


  /**
   * Called by OpenCart at the third step of the checkout process
   *
   * @return void
   */
  protected function index( ) {

    $this->load->model( 'checkout/order' );

    $aOrder  = $this->model_checkout_order->getOrder( $this->session->data[ 'order_id' ] );
    $this->data[ 'button_confirm' ] = $this->language->get( 'button_confirm' );
    $this->data[ 'button_back' ]  = $this->language->get( 'button_back' );
    $this->data[ 'layoutcode' ]   = $this->config->get( 'europabank_layoutcode' );

    $this->data[ 'action' ]       = HTTPS_SERVER . 'index.php?route=payment/europabank/bank&order_id=' . $aOrder[ 'order_id' ];
    $this->data[ 'back' ]         = HTTPS_SERVER . 'index.php?route=checkout/payment';
    $this->id                     = 'payment';

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

    $this->language->load( 'payment/europabank' );
    $this->load->model( 'checkout/order' );
    $this->load->model( 'payment/europabank' );

    $aOrder     = $this->model_checkout_order->getOrder( $this->session->data[ 'order_id' ] );
    //$sDescription = urlencode( html_entity_decode( $this->config->get( 'config_name' ), ENT_QUOTES, 'UTF-8' ) . ' - #' . $aOrder[ 'order_id' ] );

    //$sReturnURL   = urlencode( HTTPS_SERVER . 'index.php?route=payment/europabank/callback' );
    //$sReportURL   = urlencode( HTTPS_SERVER . 'index.php?route=payment/europabank/report' );

    $sReturnURL   = HTTPS_SERVER . 'index.php?route=payment/europabank/callback' ;
    $sReportURL   = HTTPS_SERVER . 'index.php?route=payment/europabank/report' ;

    $merchant = array();
    $merchant['uid'] = $this->config->get( 'europabank_mpi' );
    $merchant['storename'] = $this->config->get( 'config_name' );
    $merchant['feedbacktype'] = 'OFFLINE';
    $merchant['feedbackurl'] = $sReturnURL;
    $merchant['redirecttype'] = 'INDIRECT';
    $merchant['redirecturl'] = $sReturnURL;
    $merchant['template'] = '';
    $merchant['css'] = '';
    $merchant['mpiurl'] = $this->config->get( 'europabank_url' );
    $merchant['me_email'] = $this->config->get( 'config_email' );

    $client = array();
    $client['firstname'] = $aOrder['payment_firstname'];
    $client['lastname'] = $aOrder['payment_lastname'];
    $client['country'] = $aOrder['payment_iso_code_2'];
    $client['email'] = $aOrder['email'];
    $client['ip'] = $_SERVER['REMOTE_ADDR'];
    $client['language'] = $aOrder['language_code'];

    $transaction = array();
    $transaction['orderid'] = $aOrder['order_id'];
    $transaction['amount'] = intval((string)($aOrder['total'] * 100));
    $description = 'Order ' . $aOrder['order_id'] . ' at ' . $this->config->get( 'config_name' );
    $transaction['description'] = $description;
    $transaction['hash'] = '';
    $transaction['hash'] = $this->calculateHash(
      $this->config->get( 'europabank_mpi' ) ,
      $aOrder['order_id'] ,
      $transaction['amount'] ,
      $description ,
      $this->config->get( 'europabank_ss' ));

    $return = $this->create_request($merchant , $client , $transaction);

    if ( $return ) {

      $this->model_payment_europabank->insertOrderTransaction( $aOrder[ 'order_id' ], $this->EuropabankIdeal->getTransactionID( ) );

      $this->redirect( $this->EuropabankIdeal->getURL( ) );

    }

  }
 /**
  * Create XML request
  */
 function create_request($merchant, $client , $transaction){
  // create request
  foreach($merchant as $key => $param){
    //$merchant[$key] = $this->xmlEscape($param);
  }

  foreach($client as $key => $param){
    $client[$key] = $this->xmlEscape($param);
  }
    $request = '<?xml version="1.0" encoding="UTF-8"?>
    <MPI_Interface>
      <Authorize>
      <version>1.1</version>
      <Merchant>
        <uid>' .          $merchant['uid'] . '</uid>
        <beneficiary>' . $merchant['storename'] . '</beneficiary>
        <title>' . $merchant['storename']  . '</title>
        <feedbacktype>' .          $merchant['feedbacktype']  . '</feedbacktype>
        <feedbackurl>' . $merchant['feedbackurl'] . '</feedbackurl>
        <redirecttype>' .          $merchant['redirecttype']  . '</redirecttype>
        <redirecturl>' .$merchant['redirecturl']  . '</redirecturl>
        <template>' . $merchant['template']  . '</template>
        <css>' . $merchant['css']  . '</css>
        <email>' . $merchant['me_email'] . '</email>
      </Merchant>
      <Customer>
        <name>' . $client['firstname']  . ' ' .$client['lastname'] . '</name>
        <country>' . $client['country']  . '</country>
        <email>' . $client['email']  . '</email>
        <ip>' . $client['ip']  . '</ip>
        <language>' . $client['language']   .  '</language>
      </Customer>
      <Transaction>
        <orderid>' .          $transaction['orderid'] . '</orderid>
        <amount>' .          $transaction['amount']  . '</amount>
        <description>' .     $transaction['description']  . '</description>
      </Transaction>
      <hash>' .            $transaction['hash']  . '</hash>
      </Authorize>
    </MPI_Interface>';

    // post request and get reply
    $xmlurl = $this->xmlPost($merchant['mpiurl'] , utf8_decode($request));

    // communication error
    if (!$xmlurl) {
      echo 'Helaas, de betaling is niet gelukt.. XML communication error';
       return false;
    }

    if (!$xml = simplexml_load_string($xmlurl)){
      echo 'Helaas, de betaling is niet gelukt.. error reading XML';
      echo $xmlurl;
      trigger_error('Error reading XML string', E_USER_ERROR);
    }

    if ($xml->Error) {
      ECHO "error";
      $errorMessage = $xml->Error->errorMessage . ' : ' . $xml->Error->errorDetail;
      echo $errorMessage;
    }
    else {
      header("Location: " .  $xml->Response->url);
      exit();
    }
    return false;
  }

  private function calculateHash($uid , $order_id , $amount , $description , $secret) {
    return sha1(
      $uid.
      $order_id .
      $amount .
      $description  .
      $secret
      );
  }

  /**
   * Called when the visitor returns at our webshop
   *
   * @return void
   */
  public function callback( ) {

    $this->language->load( 'payment/europabank' );
    $this->load->model( 'checkout/order' );
    $this->load->model( 'payment/europabank' );

    $uid = $this->request->post['Uid'];
    $id = $this->request->post['Id'];
    $hash = $this->request->post['Hash'];
    $order_id = $this->request->post['Orderid'];
    $status = $this->request->post['Status'];
    $brand = $this->request->post['Brand'];
    $refnr = $this->request->post['Refnr'];
    $txtype = $this->request->post['Txtype'];


    $order = $this->model_checkout_order->getOrder( $order_id );

    $signature = $this->calculateHash(
      $this->config->get( 'europabank_mpi' ) ,
      $aOrder['order_id'] ,
      $transaction['amount'] ,
      $description ,
      $this->config->get( 'europabank_ss' ));

    if ($brand == 'V')
      $brand = 'Visa';
    else if ($brand == 'M')
      $brand = 'MasterCard';
    else if ($brand == 'A')
      $brand = 'Maestro';
    else if ($brand == '?')
      $brand = 'Not chosen yet';

    switch ($status) {
        case "AU":
        $txtype = $txtype;
        if (strtoupper($hash) != strtoupper($signature))
        {
          $newState = 'CANCELED';
          if ($txtype == '05')
            $comment = 'HASH invalid, Transaction approved by MPI, Refnr ' . $refnr;
          else
            $comment = 'HASH invalid, Transaction authorized by MPI, Refnr ' . $refnr;
        }
        else if ($txtype == '05')
        {
          $newState = 'PROCESSING';
          $authorized = true;
          $comment = 'Transaction approved by MPI, Refnr ' . $refnr;
        }
        else
        {
          $newState = 'PENDING PAYMENT';
          $comment = 'Transaction authorized by MPI, Refnr ' . $refnr;
        }
        break;
      case "TI":
            $newState = 'CANCELED';
        $comment = 'Transaction has timed out';

        break;
      case "DE":
            $newState = 'CANCELED';
        $comment = 'Transaction denied by MPI';

        break;
      case "CA":
            $newState = 'CANCELED';
        $comment = 'Transaction cancelled by customer';
        break;
      default:
            $newState = 'CANCELED';
        $comment = 'Transaction encountered exception';
        break;
    }
    //TODO Make good test here.
    if (){
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

  /**
   * Europabank utility
   */
  function xmlEscape($str){
    return htmlspecialchars($str,ENT_COMPAT, "UTF-8");
  }

  function xmlUnescape($str){
    return html_entity_decode($str,ENT_COMPAT, "UTF-8");
  }

  function xmlPost($url, $data)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "$data");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error)
    {
      $errorMessage = 'cURL error: ' . $error;
      echo "ERROR : " . $errorMessage;
      return false;
    }
    else
    {
      return $response;
    }
  }
}