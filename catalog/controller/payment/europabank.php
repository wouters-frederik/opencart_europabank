<?php
/**
 * opencart Europabank controller
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

    $sReturnURL   = HTTPS_SERVER . 'index.php?route=payment/europabank/callback' ;
    $sReportURL   = HTTPS_SERVER . 'index.php?route=payment/europabank/report' ;

    $merchant = array();
    $merchant['uid'] = $this->config->get( 'europabank_mpi' );
    $merchant['storename'] = $this->config->get( 'config_name' );
    $merchant['feedbacktype'] = 'OFFLINE';
    $merchant['feedbackurl'] = $sReportURL;
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

    $transaction['hash'] = $this->calculateHash(
      $this->config->get( 'europabank_mpi' ) ,
      $aOrder['order_id'] ,
      $transaction['amount'] ,
      $description ,
      $this->config->get( 'europabank_ss' )
    );

    $return = $this->create_request($merchant , $client , $transaction);
    if ( $return ) {
      $this->model_payment_europabank->insertOrderTransaction(
        $aOrder[ 'order_id' ],
        $transaction['description'],
        $transaction['amount'],
        0);
      header("Location: " .  $return);
    }
  }

 /**
   * Send mail report to client (legal obligation)
   */
  public function sendMail() {

    $this->load->model( 'checkout/order' );
    $this->load->model( 'account/order');
    $order_id = 1;
    $order = $this->model_checkout_order->getOrder( $order_id);
    if ($order) {
      $this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id') );
    }
}

 /**
  * Create XML request
  */
 function create_request($merchant, $client , $transaction){
  // create request

  foreach($client as $key => $param){
    $client[$key] = $this->xmlEscape($param);
  }

  $request = '<?xml version="1.0" encoding="UTF-8"?>
    <MPI_Interface>
      <Authorize>
      <version>1.1</version>
      <Merchant>
        <uid>' .          $merchant['uid'] . '</uid>
        <beneficiary>' . substr($merchant['storename'],0,25) . '</beneficiary>
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
      return $xml->Response->url;
    }
    return false;
  }

  private function calculateHash($uid , $order_id , $amount , $description , $secret) {
    return strtoupper(sha1(
      $uid.
      $order_id .
      $amount .
      $description  .
      $secret
    ));
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

    $order_id = $this->request->post['Orderid'];
    $payed = $this->validatePayment();
    $transaction = $this->model_payment_europabank->getTransactionByOrderId($order_id);
    $this->model_payment_europabank->deleteOrderTransaction( $transaction['customer_transaction_id'] );

    if ( $payed ) {
      $this->model_checkout_order->confirm( $order_id, $this->config->get( 'europabank_order_status_id' ) );
      header( 'Location: ' . HTTPS_SERVER . 'index.php?route=checkout/success' );
    } else {
      //header( 'Location: ' . HTTPS_SERVER . 'index.php?route=checkout/success' );
      echo 'Helaas, de betaling is niet gelukt..';
    }

  }

  /**
   * Make sure a order is updated, even if the customer doesn't reach the callback page
   *
   * @return void
   */
  public function report( ) {
    $this->language->load( 'payment/europabank' );
    $this->load->model( 'checkout/order' );
    $this->load->model( 'payment/europabank' );

    $order_id = $this->request->post['Orderid'];
    $transaction = $this->model_payment_europabank->getTransactionByOrderId($order_id);
    $this->model_payment_europabank->deleteOrderTransaction( $transaction['customer_transaction_id'] );
    $payed = $this->validatePayment();
    if ( $payed ) {
      $this->model_checkout_order->confirm( $order_id, $this->config->get( 'europabank_order_status_id' ) );
       return 'OK';
    } else {
       http_response_code(400);
    }

  }

  /**
   * Validate the payment.
   */
  function validatePayment(){
    $this->language->load( 'payment/europabank' );
    $this->load->model( 'checkout/order' );
    $this->load->model( 'payment/europabank' );

    $uid = $this->request->post['Uid'];
    $id = $this->request->post['Id'];
    $hash = $this->request->post['Hash'];
    $order_id = $this->request->post['Orderid'];
    $status = $this->request->post['Status'];
    $brand = $this->request->post['Brand'];
    $refnr = isset($this->request->post['Refnr'])?$this->request->post['Refnr']:'';
    $txtype = isset($this->request->post['Txtype'])?$this->request->post['Txtype']:'';

    $order = $this->model_checkout_order->getOrder( $order_id );
    $transaction = $this->model_payment_europabank->getTransactionByOrderId($order_id);

    // id orderid and server shared secret
    $signature = strtoupper(sha1(
      $_REQUEST['Id'] .
      $_REQUEST['Orderid'] .
      $this->config->get( 'europabank_cs' )
    ));

    if ($brand == 'V')
      $brand = 'Visa';
    else if ($brand == 'M')
      $brand = 'MasterCard';
    else if ($brand == 'A')
      $brand = 'Maestro';
    else if ($brand == '?')
      $brand = 'Not chosen yet';

    $authorized = FALSE;
    switch ($status) {
        case "AU":
        $txtype = $txtype;
        $newState = 'CANCELED';
        if ($txtype == '05') {
          $comment = 'Transaction approved by MPI, Refnr ' . $refnr;
          $authorized = TRUE;
        }
        else if($txtype == '02') {
          $comment = 'Transaction authorized by MPI, Refnr ' . $refnr;
          $authorized = TRUE;
        }
        else if ($txtype == '05')
        {
          $newState = 'PROCESSING';
          $authorized = TRUE;
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

    if ($authorized&&($signature==$_REQUEST['Hash']))
    {
      return  true;
    } else {
      return false;
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