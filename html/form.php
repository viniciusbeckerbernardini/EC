<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>EC</title>
	<style type="text/css" media="screen">
		body{
			width: 1200px;
			margin: 0 auto;
		}
		form, span, textarea, button{
			width: 100%;
		}
		textarea{
			height: 200px;
		}
		button{
			height: 40px;,
			font-size: 22px;
		}
		span{
			font-size: 22px;
		}
		input{
			margin:20px 0px 20px 0px;
		}
	</style>
</head>
<body>
	<form method="post" accept-charset="utf-8">
		<span>Texto a ser encriptado</span>
		<br>
		<textarea name="text"></textarea>	
		<br>
		<input type="radio" name="option" value="1"><span>Encriptar</span>
		<br>
		<input type="radio" name="option" value="0"><span>Decriptar</span>
		<br>
		<button type="submit">Enviar</button>
	</form>	
	<h1>
		Resultado:
	</h1>
	<br>
	<textarea disabled><?=$result??''?></textarea>
</body>
</html>