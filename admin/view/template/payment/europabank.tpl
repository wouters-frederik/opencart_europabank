<?php echo $header; ?>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<div class="box">
  <div class="left"></div>
  <div class="right"></div>
  <div class="heading">
    <h1 style=""><?php echo $heading_title; ?></h1>

    <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
  </div>
  <img src="view/image/payment/europabank.jpg" style="float:right">
  <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="form">

        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="europabank_status">
              <?php if ($europabank_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select></td>
        </tr>


        <tr>
          <td><span class="required">*</span> <?php echo $entry_env; ?></td>
          <td><select name="europabank_env">
                <option value="test" selected="selected">TEST</option>
                <option value="production" selected="selected">Production</option>
            </select></td>
        </tr>


        <tr>
          <td><span class="required">*</span> <?php echo $entry_mpi; ?></td>
          <td><input type="text" name="europabank_mpi" value="<?php echo $europabank_mpi; ?>" />
            <?php if (isset($error_entry_mpi)) { ?>
            <span class="error"><?php echo $error_entry_mpi; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_cur; ?></td>
          <td><select name="europabank_cur">
                <option value="EUR" selected="selected">Euro</option>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_url; ?></td>
          <td><input type="text" name="europabank_url" value="<?php echo $europabank_url; ?>" />
            <?php if (isset($error_entry_url)) { ?>
            <span class="error"><?php echo $error_entry_url; ?></span>
            <?php } ?>
          </td>
        </tr>
        <tr>

        <tr>
          <td><?php echo $entry_ss; ?></td>
          <td><input type="text" name="europabank_ss" value="<?php echo $europabank_ss; ?>" />
            <?php if (isset($error_entry_ss)) { ?>
            <span class="error"><?php echo $error_entry_ss; ?></span>
            <?php } ?>
          </td>
        </tr>
        <tr>

                 <tr>
          <td><?php echo $entry_cs; ?></td>
          <td><input type="text" name="europabank_cs" value="<?php echo $europabank_cs; ?>" />
            <?php if (isset($error_entry_cs)) { ?>
            <span class="error"><?php echo $error_entry_cs; ?></span>
            <?php } ?>
          </td>
        </tr>
        <tr>

         <tr>
          <td><?php echo $entry_proxy; ?></td>
          <td><input type="text" name="europabank_proxy" value="<?php echo $europabank_proxy; ?>" />
            <?php if (isset($error_entry_proxy)) { ?>
            <span class="error"><?php echo $error_entry_proxy; ?></span>
            <?php } ?>
          </td>
        </tr>
        <tr>

        <tr>
          <td><?php echo $europabank_order_status; ?></td>
          <td><select name="europabank_order_status_id">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $europabank_order_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>



         <tr>
          <td><?php echo $entry_lan; ?></td>
          <td>
            <select name="europabank_lan">
              <option value="nl" <?php if ($europabank_lan=='nl'){echo "selected=\"selected\"";} ?>>NL</option>
              <option value="en" <?php if ($europabank_lan=='en'){echo "selected=\"selected\"";} ?>>EN</option>
              <option value="fr" <?php if ($europabank_lan=='fr'){echo "selected=\"selected\"";} ?>>FR</option>
            </select>
            <?php if (isset($error_entry_lan)) { ?>
            <span class="error"><?php echo $error_entry_lan; ?></span>
            <?php } ?>
          </td>
        </tr>
        <tr>

<tr>
          <td valign="top">
          </td>
          <td>
        <a href="https://www.ebonline.be/test/home.jsp?link=register&lang=nl">Register an europabank test account</a> and you will receive different visa/maestro/mastercard numbers.
Add the received data (MPI account number, provider url , shared client secret , proxy url) to the payment method settings and you're ready to go.

          </td>
        </tr>

        <tr>
          <td valign="top">

          </td>
          <td>
        OpenCart Europabank plugin v1.0 was created by Frederik Wouters.<br />
            More information can be found on <a href="http://it2servu.be/">it2servu.be</a> or by mail to <a href="mailto:wouters.f@gmail.com">wouters.f@gmail.com</a>. I code for a job.
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php echo $footer; ?>