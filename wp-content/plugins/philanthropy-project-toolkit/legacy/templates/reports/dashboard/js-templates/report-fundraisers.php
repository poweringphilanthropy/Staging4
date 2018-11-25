<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

$report = $view_args['report'];
$color = $view_args['color'];
$download_report_url = add_query_arg( array(
	'type' => 'fundraisers'
), $view_args['download_report_url'] );

$style_bg_color = '';
$style_color = '';
if(!empty($color)){
    $style_bg_color = ' style="background:'.$color.'"';
    $style_color = ' style="color:'.$color.'"';
}
?>

<script id="report-fundraisers-template" type="text/x-handlebars-template">
<div class="report-section">
	<div class="section-title" <?php echo $style_bg_color; ?>>
		<div class="uk-grid">
			<div class="uk-width-1-1 uk-width-medium-1-3">
				<div class="report-title">FUNDRAISERS</div>
			</div>
			<div class="uk-width-1-1 uk-width-medium-2-3">
				<div class="uk-grid">
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-dollar.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
								<div class="amount">{{numberFormat amount}}</div>
								<div class="sub">Total</div>
						  	</div>
						</div>
					</div>
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-user.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
						  		<div class="amount">{{count}}</div>
								<div class="sub">Fundraisers</div>
						  	</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="section-content">
		<div class="uk-grid">
			<div class="uk-width-1-1 uk-width-medium-3-5">
				<div class="container-donation load-more-table-container">
			        <table class="report-table table-donation">
			        	<thead>
							<tr>
								<td class="thead-qty">AMOUNT</td>
								<td>FUNDRAISER NAME</td>
							</tr>
						</thead>
			            <tbody>
			            	{{#each data}}
							
							{{#ifMoreThan 5 @index}}
							</tbody><tbody class="more-tbody" style="display:none;">
							{{/ifMoreThan}}

							<tr class="{{#ifMoreThan 5 @index}}more{{/ifMoreThan}}">
			                    <td class="amount">${{numberFormat amount}}</td>
			                    <td class="link">{{item_name}}</td>
			                </tr>
			            	{{/each}}
			            </tbody>
			            <tfoot>
							
							{{#ifMoreThan 5 data.length}}
			            	<tr>	
								<td class="load-more" colspan="2">
									<a href="javascript:;" class="load-more-button"><?php _e('See All', 'pp-toolkit'); ?></a>
								</td>
							</tr>
							{{/ifMoreThan}}

			            </tfoot>
			        </table>
			    </div>
			</div>
			<div class="uk-width-1-1 uk-width-medium-2-5">
				<div class="download-report-button">
				  	<a href="<?php echo $download_report_url; ?>">
						<div class="inner icon">
							<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/download-report.png'; ?>" alt="icon">
					  	</div>
					  	<div class="inner">
					  		<div>Download list of<br>fundraisers & amounts</div>
					  	</div>
				  	</a>
				</div>
			</div>
		</div>
	</div>
</div>
</script>