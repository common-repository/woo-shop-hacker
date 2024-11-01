
<style>
	.shop_hacker_product_details {
		background-color: #ffffff;
		display: none;
		margin: 1em 0;
		padding: 1em;
	}
</style>

<h2><?php _e( 'Shop Hacker Inventory', 'woo-shop-hacker' ); ?></h2>

<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="query_field"><?php _e( 'Search Products', 'woo-shop-hacker' ); ?></label>
			</th>
			<td class="forminp forminp-number">
				<p>
					<input type="text" id="query_field" name="query" value="<?php echo $query; ?>" />
					<a id="query_button" class="button button-secondary" href="#">
						<?php _e( 'Search Products', 'woo-shop-hacker' ); ?>
					</a>
				</p>

				<?php if( empty( $products ) && $query ) { ?>
				<h3><?php _e( 'No results', 'woo-shop-hacker' ); ?></h3>
				<?php } ?>

			</td>
		</tr>

		<?php if( ! empty( $products ) ) { ?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="woo_shop_hacker_query"><?php _e( 'Add to Store', 'woo-shop-hacker' ); ?></label>
			</th>
			<td class="forminp forminp-number">
				<h3><?php echo sprintf( __( 'Search found %d products', 'woo-shop-hacker' ), $total_records ); ?></h3>
				<dl>

					<?php
					foreach( $products as $product ) {
						woo_shop_hacker_builder::print_product( $product );
					}
					?>

				</dl>
				<p>
					<input class="button-primary" type="submit" value="<?php _e( 'Add to Store', 'woo-shop-hacker' ); ?>" /> &nbsp;
					<?php echo implode( ' &nbsp; ', $pagination ); ?>
				</p>
			</td>
		</tr>
		<?php } ?>

	</tbody>
</table>
<hr />

<script>
	jQuery( document ).ready( function( $ ) {
		$( '#query_field' ).keypress( function( e ) {
			var query = jQuery( this ).val();
			if( e.which == 13 ) {
				query_submit( query );
				return false;
			}
		} );
		$( '#query_button' ).click( function() {
			var query = jQuery( '#query_field' ).val();
			query_submit( query );
			return false;
		} );

	} );
	function query_submit( query ) {
		window.location.href = 'admin.php?page=wc-settings&tab=settings_tab_shop_hacker&query=' + query;
	}
</script>
