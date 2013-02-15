<?php
/**
 * Admin europabank controller
 */

class ControllerPaymentEuropabank extends Controller {

  private $error = array( );

  public function index( ) {

    $this->language->load( 'payment/europabank' );
    $this->document->setTitle($this->language->get('heading_title'));
    $this->load->model( 'setting/setting' );
    if ( ( $this->request->server[ 'REQUEST_METHOD' ] == 'POST' ) && ( $this->validate( ) ) ) {

      $this->load->model( 'setting/setting' );

      $this->model_setting_setting->editSetting( 'europabank', $this->request->post );

      $this->session->data[ 'success' ] = $this->language->get( 'text_success' );

      $this->redirect( HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data[ 'token' ] );

    }

  $languages = array(
            "heading_title",
            "text_payment",
            "text_success",
            "text_enabled",
            "text_disabled",
            "text_europabank",
            "entry_mpi",
            "entry_cur",
            "entry_url",
            "entry_cs",
            "entry_ss",
            "entry_proxy",
            "entry_lan",
            "entry_env",
            "entry_status",
            //"error_permission",
            //"error_entry_mpi",
            //"error_field_required",
            "button_save",
            "button_cancel",
            'europabank_order_status',
        );

        foreach ($languages as $lang) {
            try {
                $this->data[$lang] = $this->language->get($lang);
            } catch (Exception $e) {
                $this->data[$lang] = $lang;
            }
        }



      if ( isset( $this->error[ 'warning' ] ) ) {
      $this->data[ 'error_warning' ] = $this->error[ 'warning' ];
    } else {
      $this->data[ 'error_warning' ] = '';
    }


    $settings = array(
            "europabank_mpi",
            "europabank_url",
            "europabank_ss",
            "europabank_cs",
            "europabank_cur",
            "europabank_proxy",
            "europabank_lan",
            'europabank_env',
            'europabank_status',
            'europabank_order_status_id',
        );

        foreach ($settings as $setting) {
            $this->data[$setting] = (isset($this->request->post[$setting])) ? $this->request->post[$setting] : $this->config->get($setting);
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

    if ( isset( $this->request->post[ 'europabank_mpi' ] ) ) {
      $this->data['europabank_mpi'] = $this->request->post['europabank_mpi'];
    } else {
      $this->data['europabank_mpi'] = $this->config->get('europabank_mpi');
    }

    $this->data['callback'] = HTTP_CATALOG . 'index.php?route=payment/targetpay/callback';

    //$this->data['europabank_order_status'] = $this->config->get('europabank_order_status');

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

    if ( ! $this->error ) {
      return true;
    } else {
      return false;
    }
  }

}