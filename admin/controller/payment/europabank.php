<?php
class ControllerPaymentEuropabank extends Controller {

  private $error = array( );

  public function index( ) {

    $this->load->language( 'payment/europabank' );

    $this->document->title = $this->language->get( 'heading_title' );

    $this->load->model( 'setting/setting' );

    if ( ( $this->request->server[ 'REQUEST_METHOD' ] == 'POST' ) && ( $this->validate( ) ) ) {

      $this->load->model( 'setting/setting' );

      $this->model_setting_setting->editSetting( 'europabank', $this->request->post );

      $this->session->data[ 'success' ] = $this->language->get( 'text_success' );

      $this->redirect( HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data[ 'token' ] );

    }

    $this->data[ 'heading_title' ]    = $this->language->get( 'heading_title' );
    $this->data[ 'text_enabled' ]   = $this->language->get( 'text_enabled' );
    $this->data[ 'text_disabled' ]    = $this->language->get( 'text_disabled' );
    $this->data[ 'text_all_zones' ]   = $this->language->get( 'text_all_zones' );
    $this->data[ 'entry_layoutcode' ] = $this->language->get( 'entry_layoutcode' );
    $this->data[ 'entry_testmode' ]   = $this->language->get( 'entry_testmode' );
    $this->data[ 'entry_order_status' ] = $this->language->get( 'entry_order_status' );
    $this->data[ 'entry_geo_zone' ]   = $this->language->get( 'entry_geo_zone' );
    $this->data[ 'entry_status' ]   = $this->language->get( 'entry_status' );
    $this->data[ 'entry_sort_order' ] = $this->language->get( 'entry_sort_order' );
    $this->data[ 'entry_author' ]   = $this->language->get( 'entry_author' );
    $this->data[ 'button_save' ]    = $this->language->get( 'button_save' );
    $this->data[ 'button_cancel' ]    = $this->language->get( 'button_cancel' );
    $this->data[ 'tab_general' ]    = $this->language->get( 'tab_general' );

      if ( isset( $this->error[ 'warning' ] ) ) {
      $this->data[ 'error_warning' ] = $this->error[ 'warning' ];
    } else {
      $this->data[ 'error_warning' ] = '';
    }

    if ( isset( $this->error[ 'layoutcode' ] ) ) {
      $this->data[ 'error_layoutcode' ] = $this->error[ 'layoutcode' ];
    } else {
      $this->data[ 'error_layoutcode' ] = '';
    }

    if ( isset( $this->error[ 'testmode' ] ) ) {
      $this->data[ 'error_testmode' ] = $this->error[ 'testmode' ];
    } else {
      $this->data[ 'error_testmode' ] = '';
    }

      $this->document->breadcrumbs = array( );

      $this->document->breadcrumbs[] = array(
          'href'      => HTTPS_SERVER . 'index.php?route=common/home&token=' . $this->session->data['token'],
          'text'      => $this->language->get('text_home'),
          'separator' => FALSE
      );

      $this->document->breadcrumbs[] = array(
          'href'      => HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'],
          'text'      => $this->language->get('text_payment'),
          'separator' => ' :: '
      );

      $this->document->breadcrumbs[] = array(
          'href'      => HTTPS_SERVER . 'index.php?route=payment/europabank&token=' . $this->session->data['token'],
          'text'      => $this->language->get('heading_title'),
          'separator' => ' :: '
      );

    $this->data[ 'action' ] = HTTPS_SERVER . 'index.php?route=payment/europabank&token=' . $this->session->data[ 'token' ];
    $this->data[ 'cancel' ] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data[ 'token' ];

    if ( isset( $this->request->post[ 'europabank_layoutcode' ] ) ) {
      $this->data['europabank_layoutcode'] = $this->request->post['europabank_layoutcode'];
    } else {
      $this->data['europabank_layoutcode'] = $this->config->get('europabank_layoutcode');
    }

    if ( isset( $this->request->post[ 'europabank_testmode' ] ) ) {
      $this->data['europabank_testmode'] = $this->request->post['europabank_testmode'];
    } else {
      $this->data['europabank_testmode'] = $this->config->get('europabank_testmode');
    }

    $this->data['callback'] = HTTP_CATALOG . 'index.php?route=payment/targetpay/callback';

    if (isset($this->request->post['europabank_order_status_id'])) {
      $this->data['europabank_order_status_id'] = $this->request->post['europabank_order_status_id'];
    } else {
      $this->data['europabank_order_status_id'] = $this->config->get('europabank_order_status_id');
    }

    $this->load->model('localisation/order_status');

    $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    if (isset($this->request->post['europabank_geo_zone_id'])) {
      $this->data['europabank_geo_zone_id'] = $this->request->post['europabank_geo_zone_id'];
    } else {
      $this->data['europabank_geo_zone_id'] = $this->config->get('europabank_geo_zone_id');
    }

    $this->load->model('localisation/geo_zone');

    $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    if (isset($this->request->post['europabank_status'])) {
      $this->data['europabank_status'] = $this->request->post['europabank_status'];
    } else {
      $this->data['europabank_status'] = $this->config->get('europabank_status');
    }

    if (isset($this->request->post['europabank_sort_order'])) {
      $this->data['europabank_sort_order'] = $this->request->post['europabank_sort_order'];
    } else {
      $this->data['europabank_sort_order'] = $this->config->get('europabank_sort_order');
    }

    $this->template = 'payment/europabank.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->response->setOutput(
      $this->render( true ),
      $this->config->get( 'config_compression' )
    );
  }

  private function validate( ) {

    if ( ! $this->user->hasPermission( 'modify', 'payment/europabank' ) ) {
      $this->error[ 'warning' ] = $this->language->get( 'error_permission' );
    }

    if ( ! $this->request->post[ 'europabank_layoutcode' ] ) {
      $this->error[ 'layoutcode' ] = $this->language->get( 'error_layoutcode' );
    }

    if ( ! $this->error ) {

      return true;

    } else {

      return false;

    }

  }

}