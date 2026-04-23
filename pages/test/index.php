<!DOCTYPE html>
<html>
<head>
	<title>PHP - Convert HTML to PDF using DomPDF Library</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>

<div class="container">
	<h2>Information Form For Generate PDF</h2>
	<form action="pdf_generate.php" method="POST">
		<div class="form-group">
			<label>Name:</label>
			<input type="text" name="name" class="form-control" placeholder="Enter Name" required>
		</div>
		<div class="form-group">
			<label>Email:</label>
			<input type="email" name="email" class="form-control" placeholder="Enter Email" required>
		</div>
		<div class="form-group">
			<label>Website URL:</label>
			<input type="url" name="url" class="form-control" placeholder="Enter URL" required>
		</div>
		<div class="form-group">
			<label>Say Something:</label>
			<textarea name="say" class="form-control" placeholder="Say Something"></textarea>
		</div>
		<div class="form-group">
			<button class="btn btn-success">Generate PDF</button>
		</div>
	</form>
</div>

</body>
</html>