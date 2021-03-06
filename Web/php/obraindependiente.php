<?php session_start();
	require_once 'butacas.php';
	require_once '../Gestion/FuncionesDB.php';
	require_once 'headerAndFooter.php';
	
	/*****Obtenemos la información que necesitaremos*****/
	//Referencia de la obra
	if(isset($_GET['ref'])){
		$ref=$_GET['ref'];
		$_SESSION['ref']=$ref;
	}
	else
		$ref=$_SESSION['ref'];

	//Obtener información de la obra
	$infoObra=verInfoObra($ref);
	
	//Obtener todos los pases de la obra
	$pases=verPases($ref);
	
	//Obtener el último día del espectáculo y convertirlo al formato YYYYMMDD
	$ultimoDia=$infoObra["f_final"];
	$ultimoDia=explode('-', $ultimoDia);
	$ultimoDia = $ultimoDia[0].$ultimoDia[1].$ultimoDia[2];
	
	//Primer día del espectáculo activo en el calendario (formato YYYYMMDD) - Hoy en caso de que la fecha de inico de la obra sea anterior a hoy
	$primerDiaActivo=$infoObra["f_inicio"];
	if(strtotime($primerDiaActivo) < strtotime(date("Y-m-d"))){
		$primerDiaActivo=date("Ymd");
	}
	else{
		$primerDiaActivo=explode('-', $primerDiaActivo);
		$primerDiaActivo = $primerDiaActivo[0].$primerDiaActivo[1].$primerDiaActivo[2];
	}

	//Fecha
	if(isset($_SESSION['fecha']))
		$fecha = $_SESSION['fecha'];
	else{
		$fecha = 0;
		$fechaInt = 0;
	}
	
	if(isset($_GET['fecha'])){
		//Actualizamos la fecha en la sesión
		$_SESSION['fecha'] = $_GET['fecha'];
		$fecha = $_SESSION['fecha'];
	}
	
	if($fecha!=0){
		$fechaInt = explode('-', $fecha);
		$fechaInt = $fechaInt[0].$fechaInt[1].$fechaInt[2];
	}
	
	//Hora (Pase)
	if(isset($_SESSION['hora']))
		$hora = $_SESSION['hora'];
	else
		$hora = 0;
	
	if(isset($_GET['hora'])){
		//Actualizamos la hora en la sesion
		$_SESSION['hora'] = $_GET['hora'];
		$hora = $_SESSION['hora'];	
	}		
	
	//Butacas reservadas
	if(!isset($_GET['butaca']) && !isset($_GET['noButaca'])){
		unset($_SESSION['butacasReservadas']);
	}
	else{
		//Si habíamos seleccionado una butaca la añadimos a la variable de sesión de las butacas reservadas
		if(isset($_GET['butaca']))
			$_SESSION['butacasReservadas'][]=$_GET['butaca'];

		//Si habíamos deseleccionado una butaca la quitamos de la variable de sesión de las butacas reservadas
		else if(isset($_GET['noButaca']) && isset($_SESSION['butacasReservadas'])){
			unset($_SESSION['butacasReservadas'][array_search($_GET['noButaca'], $_SESSION['butacasReservadas'])]);
			//En caso de que se deseleccionen todas las butacas eliminamos la variable de sessión
			if (empty($_SESSION['butacasReservadas']))
				unset($_SESSION['butacasReservadas']);
		}
	}
	
?>
	

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Obra Individual</title>
<link href="../Stylesheet/principal.css" rel="stylesheet" type="text/css" />
<link href="../Stylesheet/butacas.css" rel="stylesheet" type="text/css" />
<!--Calendario-->
<link rel="stylesheet" type="text/css" href="../javascript/Calendario/css/jscal2.css" />
<link rel="stylesheet" type="text/css" href="../javascript/Calendario/css/border-radius.css" />
<link rel="stylesheet" type="text/css" href="../javascript/Calendario/css/gold/gold.css" />
<script type="text/javascript" src="../javascript/Calendario/js/jscal2.js"></script>
<script type="text/javascript" src="../javascript/Calendario/js/lang/es.js"></script>
<!--Calendario-->
<script language="javascript" type="text/javascript">
function comprobaractivaciones(){
	//Activar o desactivar el campo de fecha
	var isFecha = "<?php echo isset($_SESSION['fecha']); ?>" ;
	if (isFecha == false)
		document.getElementById("pase").disabled=true;
		
	var boolean = "<?php echo isset($_SESSION['butacasReservadas']); ?>" ;
	if (boolean == 1){
		document.getElementById("formEnvio").reservar.disabled=false;
	} else {
		document.getElementById("formEnvio").reservar.disabled=true;
	}
	
	document.getElementById("ref").value="<?=$ref?>";
	document.getElementById("pase").value="<?=$hora?>";
	document.getElementById("fecha").value="<?=$fecha?>";
}
</script>
</head>
<body onload=comprobaractivaciones()>
	<div id="capacontenedora">
    	<header>
        	<?php cabecera(); ?>
        </header>
        <div id="contenedoraCapaCalendario">
        <!-- butacas here -->
        	<div id="capaobras">
				<?php 
				//Obtener los precios de las sesiones
				$precios=obtenerPreciosSecciones();
				if(isset($_SESSION['hora']) && $_SESSION['hora']!=0){
					$butacasOcupadas[]=null;
					//Obtener las butacas reservadas en esa fecha
					$resultado=verButacasReservadas($fecha,$hora);	
					while($v=mysqli_fetch_array($resultado)){
						$butacasOcupadas[]=$v['seccion'].":".$v['fila'].":".$v['numero'];
					}					
				?>
					<div id="butacasAnfiteatro">
						<?php crearAnfiteatro($NUM_FILAS_ANFITEATRO, $NUM_COLUMNAS_ANFITEATRO, $butacasOcupadas, $precios[$SECCIONES['ANFITEATRO']]);?>
					</div>
					<div id="butacasPlatea">
						<?php crearPlatea($NUM_FILAS_PLATEA, $NUM_COLUMNAS_PLATEA, $butacasOcupadas, $precios[$SECCIONES['PLATEA']]);?>
					</div>
					<div id="butacasPalco1">
						<?php crearPalco($SECCIONES['PALCO1'], $NUM_BUTACAS_PALCO1, $butacasOcupadas, $precios[$SECCIONES['PALCO1']]);?>
					</div>
					<div id="butacasPalco2">
						<?php crearPalco($SECCIONES['PALCO2'], $NUM_BUTACAS_PALCO2, $butacasOcupadas, $precios[$SECCIONES['PALCO2']]);?>
					</div>
					<div id="butacasPalco3">
						<?php crearPalco($SECCIONES['PALCO3'], $NUM_BUTACAS_PALCO3, $butacasOcupadas, $precios[$SECCIONES['PALCO3']]);?>
					</div>
					<div id="butacasPalco4">
						<?php crearPalco($SECCIONES['PALCO4'], $NUM_BUTACAS_PALCO4, $butacasOcupadas, $precios[$SECCIONES['PALCO4']]);?>
					</div>
				<?php } 
				else{ ?>
					<div id="butacasAnfiteatro">
						<h1 class="titulos">
							Anfiteatro <br/>
							<?php echo $precios[$SECCIONES['ANFITEATRO']]."€";?>
						</h1>
					</div>
					<div id="butacasPlatea">
						<h1 class="titulos">
							Platea <br/>
							<?php echo $precios[$SECCIONES['PLATEA']]."€";?>
						</h1>
					</div>
					<div id="butacasPalco1">
						<h3 class="titulos">
							Palco 1 <br/>
							<?php echo $precios[$SECCIONES['PALCO1']]."€";?>
						</h3>
					</div>
					<div id="butacasPalco2">
						<h3 class="titulos">
							Palco 2 <br/>
							<?php echo $precios[$SECCIONES['PALCO2']]."€";?>
						</h3>
					</div>
					<div id="butacasPalco3">
						<h3 class="titulos">
							Palco 3 <br/>
							<?php echo $precios[$SECCIONES['PALCO3']]."€";?>
						</h3>
					</div>
					<div id="butacasPalco4">
						<h3 class="titulos">
							Palco 4 <br/>
							<?php echo $precios[$SECCIONES['PALCO4']]."€";?>
						</h3>
					</div>
				<?php 
				}
				?>
            </div>
        	<div id="capacalendarioyhora">  
				<div id="capatituloobra">
					<?=$infoObra["nombre"];?>
				</div> 
				<!--Calendario-->            
        		<div id="capafechas">
			  	  <script type="text/javascript">
			  	  	var calendario = Calendar.setup({
			  	      	cont          : "capafechas",
			  	      	bottomBar	  : false,
			  	  		min: <?=$primerDiaActivo;?>, //Primer día seleccionable
			  	  		max: <?=$ultimoDia;?>, 	//Último día seleccionable
			  			onSelect      : function() {
			              	var fecha = document.getElementById("fecha");
			              	fecha.value = this.selection.print("%Y-%m-%d").join("\n");
							document.getElementById("pase").value=0;
							document.getElementById("formulario").submit();
			  		    },
						selection : <?=$fechaInt;?>,
	
			  	  	})
					

			  	  </script>
                </div>
				<form id="formulario" method="get" action="">
					<input id="fecha" type="hidden" name="fecha"/>
	        		<div id="capahoras">
						<select name="hora" onchange="this.form.submit()" id="pase">
							<Option value="0">---Hora---</option>
							<?php
								while($v=mysqli_fetch_array($pases))
									if($v["fecha"]==$fecha)
										echo "<Option>".$v["hora"]."</option>";
							?>						
						</select>
	                </div> 
				</form>  
				<form id="formEnvio" method="post" action="reserva.php">
	                <div id="capabutton">
						<input type="hidden" name="hdnRef" value="<?=$ref?>" id="ref"/>
	            		<input type="submit" disabled="true" name="reservar" value="Comprar"/>
                	</div>
				</form>      		       	      
        	</div>          	         
        </div>
		<div id="capaFooter">
        	<?php pieDePagina(); ?>
        </div>   
	</div>
</body>
</html>

