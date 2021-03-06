<?php
session_start();

define('CARGAR', 1);
define('AGREGAR', 2);
define('DETALLE', 3);
define('MODIFICAR', 4);
define('ELIMINAR', 5);

# A PARTIR DE AQUÍ LAS CONSTANTES SON PARA EL MODULO DE TRABAJADORES.

define('CARGAR_TRAB', 6);
define('ELIMINAR_TRAB', 7);
define('DETALLE_STATUS_CV', 8);
define('RECORDATORIO_CV', 9);

require_once('../../classes/DatabasePDOInstance.function.php');

$db = DatabasePDOInstance();

$op = isset($_REQUEST["op"]) ? $_REQUEST["op"] : false;

if($op) {
	switch($op) {
		case CARGAR:
			$categorias["data"] = array();
			$datos = $db->getAll("
					SELECT
						*
					FROM
						usuarios
					WHERE id != 1
				");

			if($datos) {
				$datos = array_reverse($datos);
				foreach($datos as $k => $pub) {
					$categorias["data"][] = array(
						$k + 1,
						$pub["nombre"]." ".$pub["apellido"],
						$pub["correo_electronico"],
						$pub["correo_electronico"],
						'<div class="acciones-categoria" data-target="' . $pub["id"] . '"> <button type="button" class="accion-categoria btn btn-primary waves-effect waves-light" onclick="modificarCategoria(this);" title="Modificar usuario"><span class="ti-pencil"></span></button> <button type="button" class="accion-categoria btn btn-danger waves-effect waves-light" title="Eliminar usuaro" onclick="eliminarCategoria(this);"><span class="ti-close"></span></button> </div>'
					);
				}
			}
			echo json_encode($categorias);
			break;
		case AGREGAR:
			$db->query("INSERT INTO usuarios (nombre, apellido, correo_electronico, clave, rol) VALUES ('$_REQUEST[nombre]', '$_REQUEST[apellido]', '$_REQUEST[email]', '$_REQUEST[clave]', '$_REQUEST[rol]');");
			echo json_encode(array("msg" => "OK"));
			break;
		case DETALLE:
			$info = $db->getRow("SELECT * FROM usuarios WHERE id=$_REQUEST[i]");
			echo json_encode(array("msg" => "OK", "data" => $info));
			break;
		case MODIFICAR:
			$db->query("UPDATE usuarios SET nombre='$_REQUEST[nombre]', apellido='$_REQUEST[apellido]', correo_electronico='$_REQUEST[email]', clave='$_REQUEST[clave]', rol='$_REQUEST[rol]' WHERE id=$_REQUEST[i]");
			echo json_encode(array("msg" => "OK"));
			break;
		case ELIMINAR:
			$db->query("DELETE FROM usuarios WHERE id=$_REQUEST[i]");
			echo json_encode(array("msg" => "OK"));
			break;
		case CARGAR_TRAB:
			$categorias["data"] = array();
			$datos = $db->getAll("
					SELECT
						*
					FROM
						trabajadores
				");

			if($datos) {
				$datos = array_reverse($datos);
				foreach($datos as $k => $pub) {
					$categorias["data"][] = array(
						$k + 1,
						$pub["nombres"]." ".$pub["apellidos"],
						$pub["correo_electronico"],
						$pub["correo_electronico"],
						'<div class="acciones-categoria" data-target="' . $pub["id"] . '"><button type="button" class="accion-categoria btn btn-success waves-effect waves-light" title="Ver status CV" onclick="statusCV(this);"><span class="ti-file	"></span></button> <button type="button" class="accion-categoria btn btn-danger waves-effect waves-light" title="Eliminar Trabajador" onclick="eliminarCategoria(this);"><span class="ti-close"></span></button> </div>'
					);
				}
			}
			echo json_encode($categorias);
			break;
		case ELIMINAR_TRAB:
			$db->query("DELETE FROM trabajadores WHERE id=$_REQUEST[i]");
			echo json_encode(array("msg" => "OK"));
			break;
		case DETALLE_STATUS_CV:


			# STATUS DE DATOS PERSONALES

			$contadorDatosP = 0;
			$datos_personales = $db->getRow("SELECT * FROM trabajadores WHERE id=".$_REQUEST["i"]);
			$totalDatosP = 14;
			$contadorDatosP += $datos_personales['nombres'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['apellidos'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['correo_electronico'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['id_sexo'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['fecha_nacimiento'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['id_pais'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['id_estado_civil'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['id_tipo_documento_identificacion'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['numero_documento_identificacion'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['provincia'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['localidad'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['calle'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['telefono'] != '' ? 1 : 0;
			$contadorDatosP += $datos_personales['telefono_alternativo'] != '' ? 1 : 0;

			$statusDP = ($contadorDatosP * 20) / $totalDatosP;
			$valDP = ceil($statusDP);

			# STATUS DE EXPERIENCIA LABORAL

			$experiencias = $db->getAll("SELECT trabajadores_experiencia_laboral.*, paises.nombre as nombre_pais, actividades_empresa.nombre as actividad_empresa FROM trabajadores_experiencia_laboral INNER JOIN paises ON paises.id=trabajadores_experiencia_laboral.id_pais INNER JOIN actividades_empresa ON actividades_empresa.id=trabajadores_experiencia_laboral.id_actividad_empresa WHERE trabajadores_experiencia_laboral.id_trabajador=".$_REQUEST['i']);

			$contadorExpLab = 0;
			$totalExpLab = 2;

			foreach ($experiencias as $e){
				if ($e['nombre_empresa'] != ''){
					$contadorExpLab += 1;
				}
			}

			$statusExpLab = ($contadorExpLab * 20) / $totalExpLab;
			$valExpLab = ceil($statusExpLab);

			# STATUS NIVEL DE ESTUDIOS

			$educacion = $db->getAll("SELECT trabajadores_educacion.*, paises.nombre as nombre_pais, nivel_estudio.nombre as nivel, areas_estudio.nombre as nombre_estudio, estado_estudio.nombre as estado_estudio FROM trabajadores_educacion INNER JOIN paises ON paises.id=trabajadores_educacion.id_pais INNER JOIN nivel_estudio ON nivel_estudio.id=trabajadores_educacion.id_nivel_estudio INNER JOIN areas_estudio ON areas_estudio.id=trabajadores_educacion.id_area_estudio INNER JOIN estado_estudio ON estado_estudio.id=trabajadores_educacion.id_estado_estudio WHERE trabajadores_educacion.id_trabajador=".$_REQUEST['i']);

			$contadorEduc = 0;
			$totalEduc = 2;

			foreach ($educacion as $e){
				if ($e['nivel'] != ''){
					$contadorEduc += 1;
				}
			}

			$statusEduc = ($contadorEduc * 20) / $totalEduc;
			$valEduc = ceil($statusEduc);

			# STATUS IDIOMAS

			$idiomasT = $db->getAll("SELECT trabajadores_idiomas.*, idiomas.nombre as nombre_idioma FROM trabajadores_idiomas INNER JOIN idiomas ON idiomas.id=trabajadores_idiomas.id_idioma WHERE trabajadores_idiomas.id_trabajador=".$_REQUEST['i']);

			$contadorIdioma = 0;
			$totalIdioma = 2;

			foreach ($idiomasT as $i){
				if ($i['nombre_idioma'] != ''){
					$contadorIdioma += 1;
				}
			}

			$statusIdioma = ($contadorIdioma * 20) / $totalIdioma;
			$valIdioma = ceil($statusIdioma);

			# STATUS OTROS CONOCIMIENTOS

			$otros_conocimientos = $db->getAll("SELECT * FROM trabajadores_otros_conocimientos WHERE id_trabajador=".$_REQUEST['i']);

			$contadorOC = 0;
			$totalOC = 3;

			foreach ($otros_conocimientos as $o){
				if ($o['nombre'] != ''){
					$contadorOC += 1;
				}
			}

			$statusOC = ($contadorOC * 20) / $totalOC;
			$valOC = ceil($statusOC);


			$json = array(
				'DP' => $valDP,
				'EL' => $valExpLab,
				'E' => $valEduc,
				'I' => $valIdioma,
				'OC' => $valOC
			);

			echo json_encode($json);

			break;
		case RECORDATORIO_CV:

			$datos_personales = $db->getRow("SELECT * FROM trabajadores WHERE id=".$_REQUEST["i"]);

			$destinatario = $datos_personales['correo_electronico'];
			$asunto = "Tu CV está incompleto - Jobbers Argentina";
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset= iso-8859-1\r\n";
			$headers .= "From: Jobbers Argentina < administracion@jobbers.com >\r\n";

			$mensaje = "<h3>Hola $datos_personales[nombres]</h3><br>";
			$mensaje .= "<h3>No has completado toda la información para tener un CV óptimo.</h3>";
			$mensaje .= "<h3>Recuerda que mientras más información contenga tu CV tienes mucha más probabilidades de conseguir un buen empleo gracias.</h3><br>";
			$mensaj .= "<h3>Jobbers Argentina \"Trabajamos para facilitarte tu busqueda de EMPLEO\".</h3><br>";

			$enviado = mail($destinatario,$asunto,$mensaje,$headers);

			if ($enviado){
				$json = array(
					'msg' => 'OK'
				);
			} else {
				$json = array(
					'msg' => 'NO'
				);
			}

			echo json_encode($json);
			break;
	}
}
function getExtension($str) {$i=strrpos($str,".");if(!$i){return"";}$l=strlen($str)-$i;$ext=substr($str,$i+1,$l);return $ext;}
?>