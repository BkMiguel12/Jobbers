<?php
	session_start();
	if(isset($_SESSION['FBID'])) {
		$_SESSION['FBID'] = $fbid;           
		$_SESSION['FULLNAME'] = $fbfullname;
		$_SESSION['EMAIL'] =  $femail;
	}

	require_once('classes/DatabasePDOInstance.function.php');
	require_once('slug.function.php');

	$infoT = $_SESSION["ctc"];

	$db = DatabasePDOInstance();

	$areas = $db->getAll("
		SELECT
			id,
			nombre,
			amigable
		FROM
			areas
		ORDER BY
			nombre
	");

	/*$sectoresPrimeraArea = $db->getAll("
		SELECT
			nombre,
			amigable
		FROM
			areas_sectores
		WHERE
			id_area = " . $areas[0]["id"] . "
	");*/

	$momentos = array(
		array(
			"nombre" => "Últimas 24 horas",
			"amigable" => "ultimas-24-horas"
		),
		array(
			"nombre" => "Durante los últimos 3 días",
			"amigable" => "durante-los-ultimos-3-dias"
		),
		array(
			"nombre" => "Durante la última semana",
			"amigable" => "durante-la-ultima-semana"
		),
		array(
			"nombre" => "Durante las ultimas 2 semanas",
			"amigable" => "durante-las-ultimas-2-semanas"
		),
		array(
			"nombre" => "Hace un mes o menos",
			"amigable" => "hace-un-mes-o-menos"
		)
	);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta name="description" content="">
		<meta name="author" content="">

		<!-- Title -->
		<title>JOBBERS - Postulaciones realizadas</title>

		<?php require_once('includes/libs-css.php'); ?>
		<link rel="stylesheet" href="vendor/bootstrap-tagsinput/dist/bootstrap-tagsinput.css">
		<link rel="stylesheet" href="vendor/select2/dist/css/select2.min.css">
		<link rel="stylesheet" href="vendor/waves/waves.min.css">
		<link rel="stylesheet" href="vendor/ionicons/css/ionicons.min.css">
		<link href="https://fonts.googleapis.com/css?family=Exo+2" rel="stylesheet">
		
		<link rel="stylesheet" href="vendor/DataTables/css/dataTables.bootstrap4.min.css">
		<link rel="stylesheet" href="vendor/DataTables/Responsive/css/responsive.bootstrap4.min.css">
		<link rel="stylesheet" href="vendor/DataTables/Buttons/css/buttons.dataTables.min.css">
		<link rel="stylesheet" href="vendor/DataTables/Buttons/css/buttons.bootstrap4.min.css">

		<style>
			th.dt-center, td.dt-center { text-align: center; }
		</style>
	</head>
	<body class="skin-5">
		<div class="frontend-wrapper frontend-max-width bg-white">
			<div class="block-5 img-cover img-fixed" style="/*background-image: url(img/photos-1/<?php echo rand(1, 3); ?>.jpg);*/ margin-top: -30px;padding-bottom: 0;">
				<div class="row" style="background-color: white;position: inherit;margin-right: 0px;margin-left: 0;">
					<div class="col-md-2" style="text-align: center;">
						<a class="text-white" href="./" style=""><img src="img/logo_d.png" style="height: 50px;margin-top: 10px;"></a>
					</div>
					<div class="col-md-10" style="padding: 0;">
						<?php require_once('includes/header.php'); ?>
					</div>
				</div>
			</div>
			
			<div class="row" style="padding: 0px 25px; margin-top: 25px;">
				<div class="col-md-12">
					<div class="card" style="margin-left: 204px;">
						<h4 class="card-header">Postulaciones realizadas</h4>
						<div class="card-block">
							<table id="tablaPostulaciones" class="table table-striped table-bordered dataTable">
								<thead>
									<tr>
										<th>#</th>
										<th>Estado</th>
										<th>Empresa</th>
										<th>Anuncio</th>
										<th>Fecha y hora</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<br>
		
			<?php require_once('includes/footer.php'); ?>

		</div>

		<!-- Vendor JS -->
		<script type="text/javascript" src="vendor/jquery/jquery-1.12.3.min.js"></script>
		<script type="text/javascript" src="vendor/tether/js/tether.min.js"></script>
		<script type="text/javascript" src="vendor/bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="vendor/detectmobilebrowser/detectmobilebrowser.js"></script>
		<script type="text/javascript" src="vendor/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
		<script type="text/javascript" src="vendor/select2/dist/js/select2.min.js"></script>
		<script type="text/javascript" src="vendor/waves/waves.min.js"></script>
		<script type="text/javascript" src="vendor/waypoints/lib/jquery.waypoints.min.js"></script>
		<script type="text/javascript" src="vendor/owl.carousel/owl.carousel.min.js"></script>
		
		<script type="text/javascript" src="vendor/DataTables/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="vendor/DataTables/js/dataTables.bootstrap4.min.js"></script>
		<script type="text/javascript" src="vendor/DataTables/Responsive/js/dataTables.responsive.min.js"></script>
		<script type="text/javascript" src="vendor/DataTables/Responsive/js/responsive.bootstrap4.min.js"></script>
		
		<!-- Neptune JS -->
		<script type="text/javascript" src="js/frontend2.js"></script>
		<script>
			$(document).ready(function() {
				var $tablaPostulaciones = jQuery("#tablaPostulaciones");

				var tablaPostulaciones = $tablaPostulaciones.DataTable( {
					"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
					"aoColumnDefs": [
						{ "width": "30px", "targets": 0 },
						{ "width": "50px", "targets": 1 },
						{ "width": "200px", "targets": 2 },
						{ "width": "150px", "targets": 4 },
						{"className": "dt-center", "targets": [0, 1, 2, 4]}
					  ],
					"language": {
						"decimal":        "",
						"emptyTable":     "Sin registros",
						"info":           "Mostrando de _START_ a _END_ registros de _TOTAL_ en total",
						"infoEmpty":      "Mostrando 0 de 0 de 0 registros",
						"infoFiltered":   "(filtrado desde _MAX_ registros en total)",
						"infoPostFix":    "",
						"thousands":      ",",
						"lengthMenu":     "Mostrar _MENU_ registros",
						"loadingRecords": "Cargando...",
						"processing":     "Procesando...",
						"search":         "Buscar:",
						"zeroRecords":    "No se encontraron registros",
						"paginate": {
							"first":      "Primero",
							"last":       "Último",
							"next":       "Siguiente",
							"previous":   "Anterior"
						},
						"aria": {
							"sortAscending":  ": activar para ordenar la columna ascendente",
							"sortDescending": ": activar para ordenar la columna descendente"
						}
					},
					"ajax": 'ajax/misc.php?op=3&idt=' + <?php echo $infoT["id"]; ?>
				} );
			});
		</script>
	</body>
</html>