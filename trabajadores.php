<?php
	session_start();
	require_once('classes/DatabasePDOInstance.function.php');
	require_once('slug.function.php');

	$db = DatabasePDOInstance();

	$busquedaAvanzada = (isset($_REQUEST["accion"]) && $_REQUEST["accion"] == "busqueda") ? true : false;
	$palabrasClave = isset($_REQUEST["busqueda"]) ? $_REQUEST["busqueda"] : false;

	$filtroArea = isset($_REQUEST["area"]) ? $_REQUEST["area"] : false;
	$filtroSector = isset($_REQUEST["sector"]) ? $_REQUEST["sector"] : false;
	$filtroMomento = isset($_REQUEST["momento"]) ? $_REQUEST["momento"] : false;
	$filtroTipo = isset($_REQUEST["tipo"]) ? $_REQUEST["tipo"] : false;
	$filtroGenero = isset($_REQUEST["genero"]) ? $_REQUEST["genero"] : false;
	$filtroIdioma = isset($_REQUEST["idioma"]) ? $_REQUEST["idioma"] : false;

	$busqueda = isset($_REQUEST["busqueda"]) ? $_REQUEST["busqueda"] : false;

	$filtroActivado = $filtroArea || $filtroMomento || $filtroTipo || $filtroGenero || $filtroIdioma;

	$cantidadRegistros = 0;

	if($filtroActivado || ($busqueda || $busquedaAvanzada)) {
		$pagina = isset($_REQUEST["pagina"]) ? $_REQUEST["pagina"] : 1;
		$final = 5;
		$inicial = $final * ($pagina - 1);
	}

	function filtroMomento($cond) {
		$res = "";
		$filtroMomento = $GLOBALS["filtroMomento"];
		$infoMomento = $GLOBALS["infoMomento"];
        if($filtroMomento) {
            $res = "(TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])";
        }
		return $res;
	}

	function filtroTipo($cond) {
		$res = "";
		$filtroTipo = $GLOBALS["filtroTipo"];
		switch($filtroTipo) {
			case "nuevos":
				$res = $cond ? " AND " : " WHERE ";
				$res .= " (TIMESTAMPDIFF(MONTH, tra.fecha_creacion, CURDATE()) <= 3 AND ec.id_trabajador IS NULL)";
				break;
			case "contratados":
				$res = $cond ? " AND " : " WHERE ";
				$res .= " (ec.id_trabajador IS NOT NULL AND TIMESTAMPDIFF(MONTH, tra.fecha_creacion, CURDATE()) >= 3)";
				break;
		}
		return $res;
	}

	function filtroGenero($cond) {
		$res = "";
		$filtroGenero = $GLOBALS["filtroGenero"];
		switch($filtroGenero) {
			case "masculino":
				$res = $cond ? " AND " : " WHERE ";
				$res .= " (tra.id_sexo = 1)";
				break;
			case "femenino":
				$res = $cond ? " AND " : " WHERE ";
				$res .= " (tra.id_sexo = 2)";
				break;
		}
		return $res;
	}

	function filtroIdioma($cond) {
		$res = "";
		$filtroIdioma = $GLOBALS["filtroIdioma"];
		if($filtroIdioma) {
			$infoIdioma = $GLOBALS["infoIdioma"];
			$res = $cond ? " AND " : " WHERE ";
			$res .= " (VERIFICAR_IDIOMA(tra.id, $infoIdioma[id]) = 1)";
		}
		return $res;
	}

	function crearBreadcrumb() {
		$arr = array();
		
		$filtroActivado = $GLOBALS["filtroActivado"];
		
		$filtroArea = $GLOBALS["filtroArea"];
		$filtroMomento = $GLOBALS["filtroMomento"];
		$filtroTipo = $GLOBALS["filtroTipo"];
		$filtroGenero = $GLOBALS["filtroGenero"];
		$filtroIdioma = $GLOBALS["filtroIdioma"];
		
		$html = '<ol class="breadcrumb no-bg m-b-1">';
		$html .= '<li class="breadcrumb-item"><a href="./trabajadores.php">JOBBERS</a></li>';
		
		if($filtroArea) {
			$arr[] = array(
				"href" => crearURL(array( array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))),
				"text" => $GLOBALS["infoArea"]["nombre"]
			);			
		}
		
		if($filtroActivado) {
			if($filtroMomento || $filtroTipo || $filtroGenero || $filtroIdioma) {
				if($filtroMomento) {
					$arr[] = array(
						"href" => crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))),
						"text" => $GLOBALS["infoMomento"]["nombre"]
					);
				}
				if($filtroTipo) {
					$arr[] = array(
						"href" => crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))),
						"text" => $GLOBALS["infoTipo"]["nombre"]
					);
				}
				if($filtroGenero) {
					$arr[] = array(
						"href" => crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))),
						"text" => $GLOBALS["infoGenero"]["nombre"]
					);
				}
				if($filtroIdioma) {
					$arr[] = array(
						"href" => crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "pagina", "valor" => 1 ))),
						"text" => $GLOBALS["infoIdioma"]["nombre"]
					);
				}
			}
		}
		
		for($i = 0; $i < count($arr) - 1; $i++) {
			$html .= '<li class="breadcrumb-item"><a href="' . $arr[$i]["href"] . '">' . $arr[$i]["text"] . '</a></li>';
		}
		$html .= '<li class="breadcrumb-item active">' . $arr[count($arr) - 1]["text"] . '</li>';
		
		$html .= '</ol>';
		
		return $html;
	}

	function crearURL($params = array()) {
		$parametros = array();
		foreach($params as $p) {
			if($p["valor"]) {
				$parametros[] = $p;
			}
		}
		$url = "trabajadores.php";
		$cant = count($parametros);
		if($cant > 0) {
			$primerParametro = $parametros[0];
			if($primerParametro["valor"] != "") {
				$url .= "?$primerParametro[clave]=$primerParametro[valor]";
			}
			for($i = 1; $i < $cant; $i++) {
				$parametro = $parametros[$i];
				if($parametro["valor"] != "") {
					$url .= (htmlentities("&") . "$parametro[clave]=$parametro[valor]");
				}
			}
		}
		return $url;
	}

	$contMomentos = 0;
	$momentos = array(
		array(
			"nombre" => "De 18 a 23 años",
			"amigable" => "de-18-a-23",
			"cantidad" => 0,
            "rango_a" => 18,
            "rango_b" => 23
		),
		array(
			"nombre" => "De 24 a 30 años",
			"amigable" => "de-24-a-30",
			"cantidad" => 0,
            "rango_a" => 24,
            "rango_b" => 30
		),
		array(
			"nombre" => "De 31 a 36 años",
			"amigable" => "de-31-a-36",
			"cantidad" => 0,
            "rango_a" => 31,
            "rango_b" => 36
		),
		array(
			"nombre" => "De 37 a 45 años",
			"amigable" => "de-37-a-45",
			"cantidad" => 0,
            "rango_a" => 37,
            "rango_b" => 45
		)
	);

	foreach($momentos as $m) {
		if($m["amigable"] == $filtroMomento) {
			$infoMomento = $m;
		}
	}

	$contTipos = 0;
	$tipos = array(
		array(
			"nombre" => "Nuevos Jobbers",
			"amigable" => "nuevos",
			"cantidad" => 0
		),
		array(
			"nombre" => "Jobbers contratados",
			"amigable" => "contratados",
			"cantidad" => 0
		)
	);

	foreach($tipos as $t) {
		if($t["amigable"] == $filtroTipo) {
			$infoTipo = $t;
		}
	}

	$contGeneros = 0;
	$generos = array(
		array(
			"nombre" => "Masculino",
			"amigable" => "masculino",
			"cantidad" => 0
		),
		array(
			"nombre" => "Femenino",
			"amigable" => "femenino",
			"cantidad" => 0
		)
	);

	foreach($generos as $g) {
		if($g["amigable"] == $filtroGenero) {
			$infoGenero = $g;
		}
	}

    $contIdiomas = 0;

    $idiomas = $db->getAll("SELECT id, nombre, amigable, 0 as cantidad FROM idiomas ORDER BY nombre");

    if($filtroIdioma) {
        foreach($idiomas as $idioma) {
            if($idioma["amigable"] == $filtroIdioma) {
                $infoIdioma = $idioma;
            }
        }
    }
    else {   
        if($filtroActivado) {
            $arr = array();
			if($filtroArea) {
				$infoArea = $db->getRow("
					SELECT
						id,
						nombre
					FROM
						areas_estudio
					WHERE
						amigable = '$filtroArea'
				");
			}
            foreach($idiomas as $i => $idioma) {
				$query = "
					SELECT
						tra.id
					FROM
						trabajadores AS tra
					INNER JOIN trabajadores_idiomas AS ti ON ti.id_trabajador = tra.id
					INNER JOIN idiomas AS i ON ti.id_idioma = i.id
					INNER JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
					INNER JOIN areas_estudio AS ae ON te.id_area_estudio = ae.id
					LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
					WHERE
						ti.id_idioma = $idioma[id]
				";
				
				if($filtroArea) {
					$query .= " AND ae.id = $infoArea[id]";
				}
				
				if($filtroMomento) {
					$query .= " AND " . filtroMomento(true);
				}
				
				if($filtroTipo) {
					$query .= " AND " . filtroTipo(true);
				}
				
				if($filtroGenero) {
					$query .= " AND " . filtroGenero(true);
				}
				
				if(!$filtroArea) {
					$query .= " GROUP BY tra.id";
				}
				
				$query = "SELECT COUNT(*) FROM ($query) AS T";
				
				$c = $db->getOne($query);
				
				//echo "<br><br><br>$query;<br><br><br>";
				
				$contIdiomas += $c;
				$idiomas[$i]["cantidad"] = $c;
            }
        }
        else {
            foreach($idiomas as $i => $idioma) {
                $idiomas[$i]["cantidad"] = $db->getOne("
                    SELECT
                        COUNT(*)
                    FROM
                        trabajadores_idiomas AS tra_i
                    WHERE
                        tra_i.id_idioma = $idioma[id]
                ");
                $contIdiomas += $idiomas[$i]["cantidad"];
            }
        }
    }

	$contAreas = 0;

	if(!$filtroArea) {
		$areas = $db->getAll("
			SELECT
				id,
				nombre,
				amigable
			FROM
				areas_estudio
			ORDER BY
				nombre
		");

		if($filtroMomento) {
			foreach($areas as $i => $area) {
				$query = "
					SELECT
						COUNT(te.id_area_estudio)
					FROM
						trabajadores AS tra
					INNER JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
					INNER JOIN areas_estudio AS ae ON te.id_area_estudio = ae.id
					LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
					WHERE
						te.id_area_estudio = $area[id]
					AND (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";
				if($filtroTipo) {
					$query .= filtroTipo(true);
				}
				if($filtroGenero) {
					$query .= filtroGenero(true);
				}
				if($filtroIdioma) {
					$query .= filtroIdioma(true);
				}
				$c = $db->getOne($query);
				$areas[$i]["cantidad"] = $c;
				$contAreas += $c;
			}
		}
		else {
			foreach($areas as $i => $area) {
				$query = "
					SELECT
						COUNT(te.id_area_estudio)
					FROM
						trabajadores AS tra
					INNER JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
					INNER JOIN areas_estudio AS ae ON te.id_area_estudio = ae.id
					LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
					WHERE
						te.id_area_estudio = $area[id]
				";
				if($filtroTipo) {
					$query .= filtroTipo(true);
				}
				if($filtroGenero) {
					$query .= filtroGenero(true);
				}
				if($filtroIdioma) {
					$query .= filtroIdioma(true);
				}
				$c = $db->getOne($query);
				$areas[$i]["cantidad"] = $c;
				$contAreas += $c;
			}
		}
	}

	if($busquedaAvanzada) {
		$query = "
			SELECT
				tra.id,
				tra.fecha_nacimiento,
				tra.fecha_creacion,
                TIMESTAMPDIFF(YEAR,  tra.fecha_nacimiento, CURDATE()) AS edad,
				TIMESTAMPDIFF(MONTH, tra.fecha_creacion, CURDATE()) AS antiguedad,
                tra.nombres,
				tra.apellidos,
				CONCAT(
					img.directorio,
					'/',
					img.nombre,
					'.',
					img.extension
				) AS imagen,
				tra.calificacion_general
			FROM
				trabajadores AS tra
			LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
			WHERE 1
		";
		$query2 = "
			SELECT
				COUNT(*)
			FROM
				trabajadores AS tra
			LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
			WHERE 1
		";
		
		if($palabrasClave != "") {
			$tokens = explode(",", $palabrasClave);
			$l = count($tokens);
			if($l > 1) {
				$query .= " AND ((tra.nombres LIKE '%$tokens[0]%' OR tra.apellidos LIKE '%$tokens[0]%')";
				$query2 .= " AND ((tra.nombres LIKE '%$tokens[0]%' OR tra.apellidos LIKE '%$tokens[0]%')";
				for($i = 1; $i < $l; $i++) {
					$query .= " OR (tra.nombres LIKE '%$tokens[$i]%' OR tra.apellidos LIKE '%$tokens[$i]%')";
					$query2 .= " OR (tra.nombres LIKE '%$tokens[$i]%' OR tra.apellidos LIKE '%$tokens[$i]%')";
				}
				$query .= ")";
				$query2 .= ")";
			}
			else {
				$query .= " AND (tra.nombres LIKE '%$tokens[0]%' OR tra.apellidos LIKE '%$tokens[0]%')";
				$query2 .= " AND (tra.nombres LIKE '%$tokens[0]%' OR tra.apellidos LIKE '%$tokens[0]%')";
			}
		}
				
		if($filtroArea) {           
            if($filtroMomento) {	
				$query .= "
                    AND (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";			
				$query2 .= "
					AND (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";
				if($filtroTipo) {
					$tmp = filtroTipo(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
				if($filtroGenero) {
					$tmp = filtroGenero(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
				if($filtroIdioma) {
					$tmp = filtroIdioma(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
			}
			if($filtroTipo) {
				$tmp = filtroTipo(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
			if($filtroGenero) {
				$tmp = filtroGenero(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
			if($filtroIdioma) {
				$tmp = filtroIdioma(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
		}
		else {
            if($filtroMomento) {
				$query .= "
                    AND (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";			
				$query2 .= "
					AND (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";
				
				if($filtroTipo) {
					$tmp = filtroTipo(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
				
				if($filtroGenero) {
					$tmp = filtroGenero(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
				
				if($filtroIdioma) {
					$tmp = filtroIdioma(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
			}
			
			if($filtroTipo) {
				$tmp = filtroTipo(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
			
			if($filtroGenero) {
				$tmp = filtroGenero(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
			
			if($filtroIdioma) {
				$tmp = filtroIdioma(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
        }
		
		$cantidadRegistros = $db->getOne($query2);
		
		$cantidadPaginas = ceil($cantidadRegistros / $final);		
		
		$query .= "GROUP BY tra.id LIMIT $inicial, $final";
		
		$trabajadores = $db->getAll($query);
        
        if($trabajadores) {
			foreach($trabajadores as $k => $t) {
				if(!$t["imagen"]) {
					$trabajadores[$k]["imagen"] = "avatars/user.png";
				}
			}
		}
	}
	elseif($filtroActivado) {        
		$query = "
			SELECT
                tra.id,
                tra.fecha_nacimiento,
                TIMESTAMPDIFF(
                    YEAR,
                    tra.fecha_nacimiento,
                    CURDATE()
                ) AS edad,
                tra.nombres,
                tra.apellidos,
                CONCAT(
                    img.directorio,
                    '/',
                    img.nombre,
                    '.',
                    img.extension
                ) AS imagen,
                tra.calificacion_general
            FROM
                trabajadores AS tra
            LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
            LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
			LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
		";
		$query2 = "
			SELECT
                COUNT(*)
            FROM
                trabajadores AS tra
            LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
            LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
			LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
		";
		
		if($filtroArea) {            
            $query .= "
                WHERE
                    te.id_area_estudio = $infoArea[id]
            ";            
            $query2 .= "
                WHERE
                    te.id_area_estudio = $infoArea[id]
            ";
            
            if($filtroMomento) {
				$query .= "
                    AND (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";			
				$query2 .= "
					AND (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";
				
				if($filtroTipo) {
					$tmp = filtroTipo(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
				
				if($filtroGenero) {
					$tmp = filtroGenero(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
				if($filtroIdioma) {
					$tmp = filtroIdioma(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
			}
			
			if($filtroTipo) {
				$tmp = filtroTipo(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
			
			if($filtroGenero) {
				$tmp = filtroGenero(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
			
			if($filtroIdioma) {
				$tmp = filtroIdioma(true);
				$query .= $tmp;
				$query2 .= $tmp;
			}
		}
		else {           
            if($filtroMomento) {
				$query .= "
                    WHERE (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";			
				$query2 .= "
					WHERE (TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) >= $infoMomento[rango_a] AND TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) <= $infoMomento[rango_b])
				";
				
				if($filtroTipo) {
					$tmp = filtroTipo(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
				
				if($filtroGenero) {
					$tmp = filtroGenero(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
				if($filtroIdioma) {
					$tmp = filtroIdioma(true);
					$query .= $tmp;
					$query2 .= $tmp;
				}
			}
            else {
                if($filtroTipo) {
                    $tmp = filtroTipo(false);
                    $query .= $tmp;
                    $query2 .= $tmp;
                    if($filtroGenero) {
                        $tmp = filtroGenero(true);
                        $query .= $tmp;
                        $query2 .= $tmp;
                    }
					if($filtroIdioma) {
						$tmp = filtroIdioma(true);
						$query .= $tmp;
						$query2 .= $tmp;
					}
                }
                else {
                    if($filtroGenero) {
                        $tmp = filtroGenero(false);
                        $query .= $tmp;
                        $query2 .= $tmp;
						if($filtroIdioma) {
							$tmp = filtroIdioma(true);
							$query .= $tmp;
							$query2 .= $tmp;
						}
                    }
					else {
						if($filtroIdioma) {
							$tmp = filtroIdioma(false);
							$query .= $tmp;
							$query2 .= $tmp;
						}
					}
                }
            }
        }
		
		$cantidadRegistros = $db->getOne($query2);
		
		$cantidadPaginas = ceil($cantidadRegistros / $final);
		
		$query .= " GROUP BY tra.id LIMIT $inicial, $final";
        
		$trabajadores = $db->getAll($query);	
		
		if($trabajadores) {
			foreach($trabajadores as $k => $t) {
				if(!$t["imagen"]) {
					$trabajadores[$k]["imagen"] = "avatars/user.png";
				}
			}
		}
	}
	else if($busqueda) {
		$trabajadores = $db->getAll("
			SELECT
				tra.id,
				tra.fecha_nacimiento,
				tra.fecha_creacion,
                TIMESTAMPDIFF(YEAR,  tra.fecha_nacimiento, CURDATE()) AS edad,
				TIMESTAMPDIFF(MONTH, tra.fecha_creacion, CURDATE()) AS antiguedad,
                tra.nombres,
				tra.apellidos,
				CONCAT(
					img.directorio,
					'/',
					img.nombre,
					'.',
					img.extension
				) AS imagen,
				tra.calificacion_general
			FROM
				trabajadores AS tra
			LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
			WHERE tra.nombres LIKE '%$busqueda%' OR tra.apellidos LIKE '%$busqueda%'
			LIMIT $inicial, $final
		");

		foreach($trabajadores as $k => $t) {
			if(!$t["imagen"]) {
				$trabajadores[$k]["imagen"] = "avatars/user.png";
			}
		}
		
		$cantidadRegistros = $db->getOne("
			SELECT
				COUNT(*)
			FROM
				trabajadores AS tra
			LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
			WHERE tra.nombres LIKE '%$busqueda%' OR tra.apellidos LIKE '%$busqueda%'
		");
		
		$cantidadPaginas = ceil($cantidadRegistros / $final);
	}
	else {
		$trabajadores = $db->getAll("
			SELECT
				tra.id,
				tra.fecha_nacimiento, TIMESTAMPDIFF(YEAR, tra.fecha_nacimiento, CURDATE()) AS edad,
                tra.nombres,
				tra.apellidos,
				CONCAT(
					img.directorio,
					'/',
					img.nombre,
					'.',
					img.extension
				) AS imagen,
				tra.calificacion_general
			FROM
				trabajadores AS tra
			LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
			ORDER BY RAND()
			LIMIT 12
		");

		foreach($trabajadores as $k => $t) {
			if(!$t["imagen"]) {
				$trabajadores[$k]["imagen"] = "avatars/user.png";
			}
		}
	}

    if(!$filtroMomento) {
		$band = false;
		foreach($momentos as $i => $momento) {
			if($filtroActivado) {
				if($filtroArea) {
					$query = "
						SELECT
							COUNT(*)
						FROM
						(
							SELECT
								tra.id
							FROM
								trabajadores AS tra
							LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
							LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
                            LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
							WHERE
								(
									TIMESTAMPDIFF(
										YEAR,
										tra.fecha_nacimiento,
										CURDATE()
									) >= $momento[rango_a]
									AND TIMESTAMPDIFF(
										YEAR,
										tra.fecha_nacimiento,
										CURDATE()
									) <= $momento[rango_b]
								) AND te.id_area_estudio = $infoArea[id] ";
                    
                    $query .= filtroTipo(true);
                    $query .= filtroGenero(true);
                    $query .= filtroIdioma(true);
                    
                    $query .= "
							GROUP BY
								tra.id
						) AS t
					";
				}
				else {
					$query = "
						SELECT
							COUNT(*)
						FROM
						(
							SELECT
								tra.id
							FROM
								trabajadores AS tra
							LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
							LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
                            LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
							WHERE
								(
									TIMESTAMPDIFF(
										YEAR,
										tra.fecha_nacimiento,
										CURDATE()
									) >= $momento[rango_a]
									AND TIMESTAMPDIFF(
										YEAR,
										tra.fecha_nacimiento,
										CURDATE()
									) <= $momento[rango_b]
								)
					";
                    
                    $query .= filtroTipo(true);
                    $query .= filtroGenero(true);
                    $query .= filtroIdioma(true);
                    
                    $query .= "
							GROUP BY
								tra.id
						) AS t";
                    
                    $query .= "";
				}
			}
			else {
				$query = "
                    SELECT
                        tra.id
                    FROM
                        trabajadores AS tra
                    LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
                    LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
                    WHERE TIMESTAMPDIFF(YEAR,  tra.fecha_nacimiento, CURDATE()) >= $momento[rango_a] AND TIMESTAMPDIFF(YEAR,  tra.fecha_nacimiento, CURDATE()) <= $momento[rango_b]
                ";
                
                $query .= filtroTipo(true);
                $query .= filtroGenero(true);
                $query .= filtroIdioma(true);
                
                $query = "SELECT COUNT(*) FROM ($query GROUP BY tra.id) AS T";
			}
            
			$c = $db->getOne($query);
			$momentos[$i]["cantidad"] = $c;
			$contMomentos += $c;
		}
	}

	if(!$filtroTipo) {
		$band = false;
		foreach($tipos as $i => $tipo) {
			$condt = "";
			switch($tipo["amigable"]) {
				case "nuevos":
					$condt = "(TIMESTAMPDIFF(MONTH, tra.fecha_creacion, CURDATE()) <= 3 AND ec.id_trabajador IS NULL)";
					break;
				case "contratados":
					$condt = "(ec.id_trabajador IS NOT NULL AND TIMESTAMPDIFF(MONTH, tra.fecha_creacion, CURDATE()) >= 3)";
					break;
			}
			if($filtroActivado) {
				if($filtroArea) {
					if($filtroMomento) {
						$query = "
							SELECT
								COUNT(*)
							FROM
							(
								SELECT
									tra.id
								FROM
									trabajadores AS tra
								LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
								LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
								LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
								WHERE $condt AND te.id_area_estudio = $infoArea[id]
						";
                        $query .= filtroMomento(true);
                        $query .= filtroGenero(true);
						$query .= filtroIdioma(true);
                        $query .= "
                            GROUP BY
                                tra.id
                        ) AS t";
					}
					else {
						$query = "
							SELECT
								COUNT(*)
							FROM
							(
								SELECT
									tra.id
								FROM
									trabajadores AS tra
								LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
								LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
								LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
								WHERE $condt AND te.id_area_estudio = $infoArea[id]
						";
                        
                        $query .= filtroGenero(true);
						$query .= filtroIdioma(true);
                        
                        $query .= "
                            GROUP BY
                                tra.id
                        ) AS t";
					}
				}
				else {
					if($filtroMomento) {
						$query = "
							SELECT
								COUNT(*)
							FROM
							(
								SELECT
									tra.id
								FROM
									trabajadores AS tra
								LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
								LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
								LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
								WHERE $condt
						";
                        
                        $query .= filtroMomento(true);
                        $query .= filtroGenero(true);
						$query .= filtroIdioma(true);
                        
                        $query .= "
                            GROUP BY
                                tra.id
                        ) AS t";
                        
					}
					else {
						$query = "
							SELECT
								COUNT(*)
							FROM
							(
								SELECT
									tra.id
								FROM
									trabajadores AS tra
								LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
								LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
								LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
								WHERE $condt
						";
                        
                        $query .= filtroGenero(true);
						$query .= filtroIdioma(true);
                        
                        $query .= "
                            GROUP BY
                                tra.id
                        ) AS t";
					}
				}
			}
			else {
				if($filtroMomento) {
					$query = "
						SELECT
							COUNT(*)
						FROM
							trabajadores AS tra
						LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
						LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
						WHERE $condt
					";
                    
                    $query .= filtroMomento(true);
                    $query .= filtroGenero(true);
					$query .= filtroIdioma(true);
				}
				else {
                    $query = "
                        SELECT
                            COUNT(*)
                        FROM
                            (
                                SELECT
                                    tra.id
                                FROM
                                    trabajadores AS tra
                                LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
                                LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
                                WHERE $condt
                    ";
                    
                    $query .= filtroGenero(true);
					$query .= filtroIdioma(true);
                    
                    $query .= "
                        GROUP BY
                            tra.id
                    ) AS T";
				}
			}
			
			$c = $db->getOne($query);
			
			$tipos[$i]["cantidad"] = $c;
			$contTipos += $c;
		}
	}

	if(!$filtroGenero) {
		$band = false;
		foreach($generos as $i => $genero) {
			$condt = "";
			switch($genero["amigable"]) {
				case "masculino":
					$condt = "(id_sexo = 1)";
					break;
				case "femenino":
					$condt = "(id_sexo = 2)";
					break;
			}
			if($filtroActivado) {
				if($filtroArea) {
					if($filtroMomento) {
						$query = "
							SELECT
								COUNT(*)
							FROM
							(
								SELECT
									tra.id
								FROM
									trabajadores AS tra
								LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
								LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
								LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
								WHERE
									$condt AND te.id_area_estudio = $infoArea[id]
						";
                        $query .= filtroMomento(true);
                        $query .= filtroTipo(true);
						$query .= filtroIdioma(true);
                        $query .= "
                            GROUP BY
                                tra.id
                        ) AS t";
					}
					else {
						$query = "
							SELECT
								COUNT(*)
							FROM
							(
								SELECT
									tra.id
								FROM
									trabajadores AS tra
								LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
								LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
								LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
								WHERE $condt AND te.id_area_estudio = $infoArea[id]
						";

                        $query .= filtroTipo(true);
						$query .= filtroIdioma(true);
                        
                        $query .= "
                            GROUP BY
                                tra.id
                        ) AS t";
					}
				}
				else {
					if($filtroMomento) {
						$query = "
							SELECT
								COUNT(*)
							FROM
							(
								SELECT
									tra.id
								FROM
									trabajadores AS tra
								LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
								LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
								LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
								WHERE $condt
						";
                        
                        $query .= filtroMomento(true);
                        $query .= filtroTipo(true);
						$query .= filtroIdioma(true);
                        
                        $query .= "
                            GROUP BY
                                tra.id
                        ) AS t";
                        
					}
					else {
						$query = "
							SELECT
								COUNT(*)
							FROM
							(
								SELECT
									tra.id
								FROM
									trabajadores AS tra
								LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
								LEFT JOIN trabajadores_educacion AS te ON tra.id = te.id_trabajador
								LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
								WHERE $condt
						";
                        
                        $query .= filtroTipo(true);
						$query .= filtroIdioma(true);
                        
                        $query .= "
                            GROUP BY
                                tra.id
                        ) AS t";
					}
				}
			}
			else {
				if($filtroMomento) {
					$query = "
						SELECT
							COUNT(*)
						FROM
							trabajadores AS tra
						LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
						LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
						WHERE $condt
					";
                    
                    $query .= filtroMomento(true);
                    $query .= filtroTipo(true);
					$query .= filtroIdioma(true);
				}
				else {
                    $query = "
                        SELECT
                            COUNT(*)
                        FROM
                            (
                                SELECT
                                    tra.id
                                FROM
                                    trabajadores AS tra
                                LEFT JOIN imagenes AS img ON tra.id_imagen = img.id
                                LEFT JOIN empresas_contrataciones AS ec ON tra.id = ec.id_trabajador
                                WHERE $condt
                    ";                    
                    
                    $query .= filtroTipo(true);
					$query .= filtroIdioma(true);
                    
                    $query .= "
                        GROUP BY
                            tra.id
                    ) AS T";
				}
			}
			
			$c = $db->getOne($query);
			
			$generos[$i]["cantidad"] = $c;
			$contGeneros += $c;
		}
        
	}
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
		<title>JOBBERS - Trabajadores</title>
		<?php require_once('includes/libs-css.php'); ?>
		<link rel="stylesheet" href="vendor/ionicons/css/ionicons.min.css">
		<style>
			.tra {
				min-height: 150px;
				margin-bottom: 30px;
			}
			.tra-f {
				min-height: 110px;
			}
			.tra, .tra-f {
				background-color: #f8f8f8 !important;
				-webkit-transition: all 0.2s ease-in-out;
				transition: all 0.2s ease-in-out;
				cursor: pointer;
			}			
			.tra:hover, .tra-f:hover {
				background-color: #3e70c9 !important;
			}
			.tra:hover *, .tra-f:hover * {
				color: #fff !important;
			}
		</style>
	</head>
	<body class="large-sidebar fixed-sidebar fixed-header skin-5">
		<div class="wrapper">
		<!-- Sidebar -->
		<?php require_once('includes/sidebar.php'); ?>

		<!-- Sidebar second -->
		<?php require_once('includes/sidebar-second.php'); ?>

		<!-- Header -->
		<?php require_once('includes/header.php'); ?>
			<div class="site-content bg-white">
				<!-- Content -->
				<div class="content-area p-y-1">
				
					<div class="container-fluid">
						<div class="col-md-6">				
							<?php if($filtroActivado): ?>
								<h4>Trabajadores</h4>
								
								<?php echo crearBreadcrumb(); ?>
							
							<?php else: ?>
								<br>
							<?php endif ?>					
						</div>
						<?php if($cantidadRegistros > 0): ?>
							<?php if($filtroActivado): ?>
								<div class="col-md-6 text-xs-right">
									<h6 class="m-t-1"><?php echo $filtroArea ? ($infoArea["nombre"] . ($filtroSector ? " ($infoSector[nombre])" : "")) : (empty($infoMomento) ? "" : "$infoMomento[nombre]"); ?></h6>
									<h6>Trabajadores: <?php echo ($inicial + 1); ?> - <?php echo ($final * $pagina) > $cantidadRegistros ? $cantidadRegistros : ($final * $pagina); ?> de <?php echo $cantidadRegistros; ?></h6>
								</div>
							<?php elseif($busqueda): ?>
								<div class="col-md-6 text-xs-right">
									<h6 class="m-t-1">Resultados de búsqueda para <strong><?php echo $busqueda; ?></strong></h6>
									<h6>Trabajadores: <?php echo ($inicial + 1); ?> - <?php echo ($final * $pagina) > $cantidadRegistros ? $cantidadRegistros : ($final * $pagina); ?> de <?php echo $cantidadRegistros; ?></h6>
								</div>
							<?php endif ?>
						<?php endif ?>
					</div>

					<div class="container-fluid">

						<div class="col-md-3">
							<?php if($contAreas > 0 || $filtroArea): ?>
								<div class="box bg-white">
									<div class="box-block clearfix">
										<h5 class="pull-xs-left"><i class="ion-ios-list m-sm-r-1"></i> Área de estudio</h5>
									</div>
									<table class="table m-md-b-0">
										<tbody>
											<?php if($filtroArea): ?>
												<tr>
													<td>
														<a style="margin-left: 7px;" class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $infoArea["nombre"]; ?></a>
													</td>
													<td>
														<?php if(!$busquedaAvanzada || $palabrasClave == ""): ?>
															<span class="text-muted pull-xs-right" title="Remover filtro"><a href="<?php echo crearURL(array(array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><i class="ion-close text-danger"></i></a></span>
														<?php endif ?>
													</td>
												</tr>
											<?php else: ?>
												<?php foreach($areas as $area): ?>
													<?php if($area["cantidad"] > 0): ?>
														<tr>
															<td>
																<a style="margin-left: 7px;" class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $area["amigable"] ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><span class="underline"><?php echo $area["nombre"]; ?></span></a>
															</td>
															<td>
																<span class="text-muted pull-xs-right"><?php echo $area["cantidad"]; ?></span>
															</td>
														</tr>
													<?php endif ?>
												<?php endforeach ?>
											<?php endif ?>
										</tbody>
									</table>
								</div>
							<?php endif ?>
							
							<?php if($contMomentos > 0 || $filtroMomento): ?>
								<div class="box bg-white">
									<div class="box-block clearfix">
										<h5 class="pull-xs-left"><i class="m-sm-r-1"></i> Edad</h5>
									</div>
									<table class="table m-md-b-0">
										<tbody>						
											<?php if($filtroMomento): ?>
												<tr>
													<td>
														<a class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $infoMomento["nombre"]; ?><span class="underline"></span></a>
													</td>
													<td>
														<?php if(!$busquedaAvanzada || $palabrasClave == ""): ?>
															<span class="text-muted pull-xs-right" title="Remover filtro"><a href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><i class="ion-close text-danger"></i></a></span>
														<?php endif ?>
													</td>
												</tr>
											<?php else: ?>
												<?php foreach($momentos as $momento): ?>
													<?php if($momento["cantidad"] > 0): ?>
														<tr>
															<td>
																<a class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $momento["amigable"] ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $momento["nombre"]; ?></a>
															</td>
															<td>
																<span class="text-muted pull-xs-right"><?php echo $momento["cantidad"]; ?></span>
															</td>
														</tr>
													<?php endif ?>
												<?php endforeach ?>									
											<?php endif ?>							
										</tbody>
									</table>
								</div>
							<?php endif ?>
                           
                           <?php if($contTipos > 0 || $filtroTipo): ?>
                           	<div class="box bg-white">
								<div class="box-block clearfix">
									<h5 class="pull-xs-left"><i class="m-sm-r-1"></i> Etapa</h5>
								</div>
								<table class="table m-md-b-0">
									<tbody>
                                        <?php if($filtroTipo): ?>
											<tr>
												<td>
													<a class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $infoTipo["nombre"]; ?><span class="underline"></span></a>
												</td>
												<td>
													<?php if(!$busquedaAvanzada || $palabrasClave == ""): ?>
														<span class="text-muted pull-xs-right" title="Remover filtro"><a href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><i class="ion-close text-danger"></i></a></span>
													<?php endif ?>
												</td>
											</tr>
										<?php else: ?>
											<?php foreach($tipos as $tipo): ?>
												<?php if($tipo["cantidad"] > 0): ?>
													<tr>
														<td>
															<a class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $tipo["amigable"] ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $tipo["nombre"]; ?></a>
														</td>
														<td>
															<span class="text-muted pull-xs-right"><?php echo $tipo["cantidad"]; ?></span>
														</td>
													</tr>
												<?php endif ?>
											<?php endforeach ?>									
										<?php endif ?>							
									</tbody>
								</table>
							</div>
                           <?php endif ?>
                           
                           <?php if($contGeneros > 0 || $filtroGenero): ?>
                           
							   <div class="box bg-white">
									<div class="box-block clearfix">
										<h5 class="pull-xs-left"><i class="m-sm-r-1"></i> Género</h5>
									</div>
									<table class="table m-md-b-0">
										<tbody>
											<?php if($filtroGenero): ?>
												<tr>
													<td>
														<a class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $infoGenero["nombre"]; ?><span class="underline"></span></a>
													</td>
													<td>
														<?php if(!$busquedaAvanzada || $palabrasClave == ""): ?>
															<span class="text-muted pull-xs-right" title="Remover filtro"><a href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><i class="ion-close text-danger"></i></a></span>
														<?php endif ?>
													</td>
												</tr>
											<?php else: ?>
												<?php foreach($generos as $genero): ?>
													<?php if($genero["cantidad"] > 0): ?>
														<tr>
															<td>
																<a class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $genero["amigable"] ), array( "clave" => "idioma", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $genero["nombre"]; ?></a>
															</td>
															<td>
																<span class="text-muted pull-xs-right"><?php echo $genero["cantidad"]; ?></span>
															</td>
														</tr>
													<?php endif ?>
												<?php endforeach ?>									
											<?php endif ?>							
										</tbody>
									</table>
								</div>
                          	<?php endif ?>
                          	
                          	<?php if($contIdiomas > 0 || $filtroIdioma): ?>
                           
							   <div class="box bg-white">
									<div class="box-block clearfix">
										<h5 class="pull-xs-left"><i class="m-sm-r-1"></i> Idioma</h5>
									</div>
									<table class="table m-md-b-0">
										<tbody>
											<?php if($filtroIdioma): ?>
												<tr>
													<td>
														<a class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroGenero ), array( "clave" => "tipo", "valor" => $filtroIdioma ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $infoIdioma["nombre"]; ?><span class="underline"></span></a>
													</td>
													<td>
														<?php if(!$busquedaAvanzada || $palabrasClave == ""): ?>
															<span class="text-muted pull-xs-right" title="Remover filtro"><a href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><i class="ion-close text-danger"></i></a></span>
														<?php endif ?>
													</td>
												</tr>
											<?php else: ?>
												<?php foreach($idiomas as $idioma): ?>
													<?php if($idioma["cantidad"] > 0): ?>
														<tr>
															<td>
																<a class="text-primary" href="<?php echo crearURL(array( array( "clave" => "area", "valor" => $filtroArea ), array( "clave" => "momento", "valor" => $filtroMomento ), array( "clave" => "tipo", "valor" => $filtroTipo ), array( "clave" => "genero", "valor" => $filtroGenero ), array( "clave" => "idioma", "valor" => $idioma["amigable"] ), array( "clave" => "pagina", "valor" => 1 ))); ?>"><?php echo $idioma["nombre"]; ?></a>
															</td>
															<td>
																<span class="text-muted pull-xs-right"><?php echo $idioma["cantidad"]; ?></span>
															</td>
														</tr>
													<?php endif ?>
												<?php endforeach ?>									
											<?php endif ?>							
										</tbody>
									</table>
								</div>
                          	<?php endif ?>
                            
						</div>
						
						<div class="col-md-9">

							<?php if($filtroActivado || $busqueda || $busquedaAvanzada): ?>
								<?php if($cantidadRegistros > 0): ?>
									<div class="row row-sm">
										<?php foreach($trabajadores as $trabajador): ?>										
												<div class="col-md-12">
													<a href="trabajador-detalle.php?t=<?php echo slug("$trabajador[nombres] $trabajador[apellidos]") . "-$trabajador[id]"; ?>">
														<div class="tra-f box box-block bg-white user-5">
															<div class="u-content" style="text-align: left;display: flex;">
																<div style="margin-right: 11px;" class="avatar box-96">
																	<img class="b-a-radius-circle" src="img/<?php echo $trabajador["imagen"]; ?>" alt="" style="max-height: 90px;height: 100%;">
																</div>
																<div style="display: inline-block;padding-top: 25px;">
																	<h5 style="margin-bottom: 0px;margin-left: 7px;"><span class="text-black"><?php echo "$trabajador[nombres] $trabajador[apellidos]"; ?></span></h5>
																	<div style="font-size: 28px;display: flex;"></div>
																</div>

															</div>
														</div>
													</a>
												</div>
										<?php endforeach ?>	
										</div>
								<?php else: ?>	
								<div class="alert alert-danger fade in" role="alert">
									<i class="ion-android-alert"></i> No hemos obtenido ningún resultado que se ajuste a tus criterios de búsqueda.
								</div>
								<?php endif ?>
							<?php else: ?>
								<div class="row row-sm">
									<?php foreach($trabajadores as $trabajador): ?>
										<div class="col-md-4">
											<a href="trabajador-detalle.php?t=<?php echo slug("$trabajador[nombres] $trabajador[apellidos]") . "-$trabajador[id]"; ?>">
												<div class="tra box box-block bg-white user-5">
													<div class="u-content">
														<div class="avatar box-96 m-b-2" style="margin-right: 11px;">
															<img class="b-a-radius-circle" src="img/<?php echo $trabajador["imagen"]; ?>" alt="" style="max-height: 90px;height: 100%;">
														</div>
														<h5><span class="text-black"><?php echo "$trabajador[nombres] $trabajador[apellidos]"; ?></span></h5>
														<div style="font-size: 28px;"></div>
													</div>
												</div>
											</a>
										</div>
									<?php endforeach ?>	
								</div>
							<?php endif ?>

							<?php if($cantidadRegistros > 0 && ($filtroActivado || $busqueda)): ?>
								<div class="btn-toolbar">
									<div class="btn-group pull-xs-right">
										<?php
											$urlParams = "empleos.php";
											if($filtroArea) {
												$urlParams .= "?area=$filtroArea";
												if($filtroSector) {
													$urlParams .= "&sector=$filtroSector";
												}
												if($filtroMomento) {
													$urlParams .= "&momento=$filtroMomento";
												}
											}
											else {
												if($busqueda) {
													$urlParams .= "?busqueda=$busqueda";
												}
												elseif($filtroMomento) {
													$urlParams .= "?momento=$filtroMomento";
												}
											}

										?>
										<a href="<?php echo $pagina > 1 ? ("$urlParams&pagina=" . ($pagina - 1)) : "javascript: void(0);"; ?>" class="btn btn-secondary waves-effect waves-light <?php if($pagina == 1) { echo "disabled"; } ?>">Anterior</a>
										<?php for($i = 1; $i <= $cantidadPaginas; $i++): ?>
											<a href="<?php echo "$urlParams&pagina=$i"; ?>" class="btn <?php echo $i == $pagina ? "btn-primary" : "btn-secondary"; ?> waves-effect waves-light"><?php echo $i; ?></a>
										<?php endfor ?>
										<a href="<?php echo $pagina < $cantidadPaginas ? ("$urlParams&pagina=" . ($pagina + 1)) : ""; ?>" class="btn btn-secondary waves-effect waves-light <?php if($pagina == $cantidadPaginas) { echo "disabled"; } ?>">Siguiente</a>
									</div>
								</div>
							<?php endif ?>

						</div>
					</div>

				</div>
				<?php require_once('includes/footer.php'); ?>
			</div>
		</div>

		<?php require_once('includes/libs-js.php'); ?>
		<script>
            
		</script>
	</body>
</html>