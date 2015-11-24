<!--
Client Gmail simples

Bruno Gomes
brunogomes@live.it
-->
        <meta charset="UTF-8" name="viewport" content="text/html">
        <link rel="stylesheet" type="text/css" href="style.css">
        <title>Ler Gmail</title>		
		<?php
        	error_reporting(0);
			ini_set(“display_errors”, 0 );
			session_start();

			if(isset($_GET["logout"])){
				session_destroy();
				header("location:?");
			}

			if(isset($_GET["login"])){
				if (isset($_GET["login"]) && $_SESSION["login"] == true) {
					header("location:?pag=1");
				}else{
					/* connect to gmail */
					$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
					$username = $_POST["email"];
					$password = $_POST["password"];
					/* try to connect */
					$mailbox = imap_open($hostname,$username,$password);
					if(!$mailbox){
						$_SESSION["login"] = false;
						header("location:?fail");
						die();
					}else{
						$_SESSION['email'] = $_POST['email'];
	        			$_SESSION['password'] = $_POST['password'];
						$_SESSION["login"] = true;
						header("location:?pag=1");
					}
				}
			}else{
				if( isset($_SESSION["login"]) == false || isset($_GET["fail"]) ){
					?>
					<h2>Sua caixa de email em um arquivo</h2>
					<form action="?login" method="POST">
						Email:<input type="text" name="email" id="email"><br/>
						Senha:<input type="password" name="password" id="password"><br/>
						<input type="submit" value="Logar">
						<br/>
					</form>
					<?php
				}else{
					$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
					$mailbox = imap_open($hostname,$_SESSION['email'],$_SESSION['password']);
					$check = imap_check($mailbox);
					?>
					<!-- Mensagem de boas vindas e informaçao geral -->
					<div class="title">Bem vindo <?php echo $_SESSION["email"] ?> <a href="?logout">SAIR</a></div>

					<div class="page">
						
						<table border="1">
							<tr>
								<td>Data</td>
								<td><?php echo $check->Date; ?></td>
							</tr>
							<tr>
								<td>Total de emails</td>
								<td><?php echo $check->Nmsgs; ?></td>
							</tr>
							<tr>
								<td>emails por pagina</td>
								<td>20</td>
							</tr>
						</table>
						<?php


						if(isset($_GET["msg"])){
							$header = imap_header($mailbox, $_GET["msg"]);
							?>

							<table class="info">
								<tr>
									<td><?php echo $header->Date; ?></td>
									<td><?php echo $header->toaddress; ?></td>
									<td><?php echo $header->fromaddress; ?></td>
								</tr>
								<tr>
									<td><?php echo $header->cc; ?></td>
									<td><?php echo $header->reply_toaddress; ?></td>
									<td><?php echo $header->Size; ?></td>
								</tr>
							</table>

							<h1>
							<?php
								echo utf8_encode(iconv_mime_decode($header->Subject,0, "ISO-8859-1"))
							?>
							</h1>
							<br/>

							<?php echo quoted_printable_decode(imap_body($mailbox, $_GET["msg"])); ?>
							<br/>
							<a href="?pag=<?php echo $_GET["pag"];?>">Voltar</a>
							<?php

						}else{
							if(count($_GET) == 0)
								header("location:?pag=1");
							//echo "<table border=\"1\">";
							$tmessage = $check->Nmsgs;
							$nom = 20;  //number of message
							$cmessage = $tmessage-$nom;
							$maxPag = $tmessage-(($_GET["pag"]-1)*20);
							echo "<br>";
							echo "<table class=\"email\">";
							echo "<tr><td>Assunto</td> <td>De:</td> <td>Recebido em</td></tr>";
							for ($i = $maxPag; $i > $maxPag-20; $i--) {
								$overview = imap_fetch_overview($mailbox, $i);
								$email = current($overview);
								echo "<tr>"
										."<td><a href=\"?pag=".$_GET["pag"]."&msg=".$i."\">".utf8_encode(iconv_mime_decode($email->subject,0, "ISO-8859-1"))."</a></td>
										 <td>".$email->from."</td>
										 <td>".$email->date."</td>"
									."</tr>";
							}
							echo "</table>";

							echo "Total de Paginas: ".$tmessage/20;
							$pPage = ($_GET["pag"] == "1")?"1":$_GET["pag"]-1;
							$nPage = ($_GET["pag"] == $tmessage/20)?$tmessage/20:$_GET["pag"]+1;
							$lPage = $tmessage/20;

							?>
							<ul>
								<li><a href="?pag=1" > |---Primeira </a></li>
								<li><a href="?pag=<?php echo $pPage; ?>"> <<<---Anterior </a></li>
								<li><a href="?pag=<?php echo $nPage; ?>" > Proxima--->>> </a></li>	
								<li><a href="?pag=<?php echo $lPage ?>" > Ultima---| </a></li>
							</ul>	
						</div>
						<?php
					}
				}
			}

			?>      	