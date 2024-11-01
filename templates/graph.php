<?php
/**
 * Graph
 *
 * @package USERLOGS
 */

if ( empty( $graph['data_json'] ) || empty( $graph['ticks_json'] ) ) {
	return;
}
?>

<div id="userlogs_graph_container">
	<div class="heading">User Login Graph</div>
	<div id="userlogs_login_chart"></div>
</div>

<script language="JavaScript">

	(function($) {

		$( document ).ready(function() {

			google.charts.load('current', {packages: ['corechart', 'line']});
			google.charts.setOnLoadCallback(drawBasic);

			function drawBasic() {

				var data = new google.visualization.DataTable();

				data.addColumn('date', 'Date');
				data.addColumn('number', "Login" );
				data.addColumn('number', "Logout" );
				data.addColumn('number', "Registrations" );
				data.addColumn('number', "Comments" );

				data.addRows(<?php echo esc_html( $graph['data_json'] ); ?>)

				var options = {
					animation: {
						duration: 1000,
						startup: true,
						easing: 'out'
					},
					chart: {
						title: '',
						subtitle: ''
					},
					chartArea: {
						left: 50,
						width: '95%',
						height: '75%',
						top: 30
					},
					legend: {
						position: 'top'
					},
					height: 300,
					vAxis: {
						textPosition: 'out'

					},
					hAxis: {
						format: 'd MMM',
						textStyle: {
							color: '#000',
							fontName: 'Arial',
							fontSize: 10,
							bold: false,
							italic: true
						},
						ticks: <?php echo esc_html( $graph['ticks_json'] ); ?>
					},
				};

				var chart = new google.visualization.LineChart( document.getElementById('userlogs_login_chart') );
				chart.draw(data, options);

				$('#userlogs_login_chart').css('border', '1px solid #c3c4c7');
				$('#userlogs_login_chart').css('box-shadow', '0 1px 1px rgba(0, 0, 0, 0.04)');
			}
		});

	})( jQuery );

</script>
