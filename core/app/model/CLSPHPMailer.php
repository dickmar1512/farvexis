<?php
// require 'plugins/PHPMailer/src/Exception.php';
// require 'plugins/PHPMailer/src/PHPMailer.php';
// require 'plugins/PHPMailer/src/SMTP.php';

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// Calcular la ruta base del proyecto
$basePath = dirname(__DIR__, 3); // Va de core/app/model hasta la raíz

require_once $basePath . '/assets/plugins/PHPMailer/src/Exception.php';
require_once $basePath . '/assets/plugins/PHPMailer/src/PHPMailer.php';
require_once $basePath . '/assets/plugins/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class CLSPHPMailer
{

	private $host = "smtp.gmail.com";
	private $dominio = "gmail.com";
	private $de = "botica.au";
	private $usuario = "botica.au@gmail.com";
	private $clave = "kkon sgwz kipa koow"; //sbfi twaq trmc yuef
	private $tituloCorreoMSG = "BOTICA ALFONZO UGARTE";
	private $fromname = 'AVISOS';
	private $objMail;

	public function __CONSTRUCT()
	{
		$this->objMail = new PHPMailer();
		$this->usuario = $this->de . "@" . $this->dominio;
	}

	/**
	 * fnMail
	 * @param array $arraddress
	 * @param array $arrAddcc
	 * @param string $asunto
	 * @param string $cuerpohTML
	 * @param string $pie
	 * @param string $firma
	 * @param array|null $atachar
	 * @param array $arrAddbcc
	 * @return bool
	 */

	public function fnMail($arraddress, $arrAddcc, $asunto, $cuerpohTML, $pie = 'pie', $firma = '', $atachar = null, $arrAddbcc = array())
	{
		try {
			// Cargar correos configurados en el sistema
			if (class_exists('EmailConfigData')) {
				$configuredEmails = EmailConfigData::getActives();
				foreach ($configuredEmails as $cEmail) {
					if ($cEmail->type == 'to') {
						$arraddress[] = $cEmail->email;
					} elseif ($cEmail->type == 'cc') {
						$arrAddcc[] = $cEmail->email;
					} elseif ($cEmail->type == 'bcc') {
						$arrAddbcc[] = $cEmail->email;
					}
				}
			}

			// Eliminar duplicados y vacíos
			$arraddress = array_unique(array_filter($arraddress));
			$arrAddcc = array_unique(array_filter($arrAddcc));
			$arrAddbcc = array_unique(array_filter($arrAddbcc));

			// Configuración SMTP
			$this->objMail->isSMTP();
			$this->objMail->Host = $this->host;
			$this->objMail->SMTPAuth = true; // Cambiado a true para Gmail
			$this->objMail->Username = $this->usuario;
			$this->objMail->Password = $this->clave;
			$this->objMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usar constante
			$this->objMail->Port = 587;
			$this->objMail->Timeout = 30;
			$this->objMail->SMTPKeepAlive = true;
			$this->objMail->CharSet = 'UTF-8';

			// Remitente
			$this->objMail->setFrom($this->usuario, $this->fromname);
			$this->objMail->FromName = $this->fromname;

			// Destinatarios
			foreach ($arraddress as $email) {
				$this->objMail->addAddress(trim($email));
			}

			// CC
			foreach ($arrAddcc as $email) {
				$this->objMail->addCC(trim($email));
			}

			// CCO (BCC)
			foreach ($arrAddbcc as $email) {
				$this->objMail->addBCC(trim($email));
			}

			// Contenido
			$this->objMail->isHTML(true);
			$this->objMail->Subject = $asunto;

			// Pie de página (imagen incrustada)
			if ($pie == 'pie') {
				$this->objMail->addEmbeddedImage('assets/img/pie.jpg', 'pie');
			} elseif ($pie == 'pie2') {
				$this->objMail->addEmbeddedImage('assets/img/pie2.jpg', 'pie');
			} elseif ($pie == 'pie3') {
				$this->objMail->addEmbeddedImage('assets/img/pie3.jpg', 'pie');
			}

			// Cuerpo del mensaje
			$cuerpo = $this->fn_Cabecera();
			$cuerpo .= $cuerpohTML;
			$cuerpo .= $this->fn_pie($firma);
			$this->objMail->Body = $cuerpo;

			// Adjuntos
			if ($atachar) {
				foreach ($atachar as $key => $value) {
					$this->objMail->addAttachment($key, $value);
				}
			}

			// Envío
			if (!$this->objMail->send()) {
				throw new Exception("Error al enviar: " . $this->objMail->ErrorInfo);
			}

			return true;

		} catch (Exception $e) {
			error_log("Error al enviar correo: " . $e->getMessage());
			return false;
		} finally {
			$this->objMail->smtpClose();
		}
	}

	public function fn_Cabecera()
	{
		$cuerpo = "<!DOCTYPE html>
					<html lang='es'>
					<head>
					<meta charset='UTF-8'>
					<meta name='viewport' content='width=device-width, initial-scale=1.0'>
					<title> CAREPHARM | ENvio de Alertas </title>";		
		$cuerpo .= $this->fn_estilo();			
		$cuerpo .= " </head>";
		$cuerpo .= " <body>";
		$cuerpo .= " <table>";
		$cuerpo .= " <tbody>";
		$cuerpo .= " <tr>";
		$cuerpo .= " <td valign='top' width='980'>";
		$cuerpo .= " <div style='padding: 0px 0px 0px 15px;'>";
		$cuerpo .= "<b>Estimado(a):</b>";
		return $cuerpo;
	}

	public function fn_pie($firma = '')
	{
		$cuerpo = "
					<!-- Footer / Firma Corporativa -->
					<table width='100%' cellpadding='0' cellspacing='0' border='0'
						style='background: linear-gradient(135deg, #0d1b2a 0%, #1b3a5c 100%);
								border-radius: 0 0 8px 8px;'>
					<tr>
						<td style='padding: 28px 40px;'>

						<!-- Línea divisora decorativa -->
						<table width='100%' cellpadding='0' cellspacing='0' border='0'>
							<tr>
							<td style='border-top: 1px solid rgba(127,168,204,0.3); padding-bottom: 20px;'></td>
							</tr>
						</table>

						<!-- Logo / Nombre + Contacto -->
						<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
		$cuerpo .= " <tr>";
		$cuerpo .= " <td valign='top' width='980'>";
		$cuerpo .= " <img src='cid:pie' />";
		$cuerpo .= " </td>";
		$cuerpo .= " </tr>";
		$cuerpo .= $firma;
		$cuerpo .= " </td>";
		$cuerpo .= " </tr>";
		$cuerpo .= " </table>";
		$cuerpo .= " </body>";
		return $cuerpo;
	}

	public function fn_estilo()
	{		
		$estilo = "<style  type='text/css'>";
		$estilo .= " </style>";

		return $estilo;
	}
}
