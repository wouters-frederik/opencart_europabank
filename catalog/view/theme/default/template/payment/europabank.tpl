<div class="buttons">
	<form action="<?php echo str_replace( '&', '&amp;', $action ); ?>" method="post" id="checkout">
		Kies uw bank:
		<select name="issuer_id">
			<option value="">-</option>
			<?php foreach( $banklist as $bank_id => $bank_name ): ?>
				<option value="<?php echo $bank_id; ?>"><?php echo $bank_name; ?></option>
			<?php endforeach; ?>
		</select>
	</form>
</div>

<div class="buttons">
	<table>
		<tr>
			<td align="left"><a onclick="location = '<?php echo str_replace( '&', '&amp;', $back ); ?>'" class="button"><span><?php echo $button_back; ?></span></a></td>
			<td align="right"><a onclick="$( '#checkout' ).submit( );" class="button"><span><?php echo $button_confirm; ?></span></a></td>
		</tr>
	</table>
</div>