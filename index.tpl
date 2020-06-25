<html>
<head>
	<title>Login</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="css/login/login.css" type="text/css" />
	<script type="text/javascript" src="css/login/login.js"></script>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
	<div class="login-page">
	<h1 class = "heading">Exam Cell</h1>
	<h1 class = "heading">IIIT-Allahabad</h1>

		<div class="form">
		<h1 class = "heading"><img src="includes/iiita.gif" height="40" width="40"></h1>
			<form class="login-form" role="form" method="POST" action="index.php" enctype = "multipart/form-data">
				<h1 class ="heading">Login</h1>
				<input  name="username" type="text" type="text" id="username" placeholder="username" required>
				<input name = "password" type="password" placeholder="password" id = "password" autocomplete="off" required>
				<button type="submit" name="submit">login</button>
				<h2 class="mesg">[onshow.msg;magnet=h2]</h2>
				<h2 class="pc_mesg">[onshow.pc_mag;magnet=h2]</h2>
			</form>
		</div>
	</div>
</body>
</html>