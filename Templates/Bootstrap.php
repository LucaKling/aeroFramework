<!DOCTYPE html>
<html charset="utf-8" lang="de">
	<head charset="utf-8" lang="de">
		<?php $PageController->PrintMeta(); ?>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="HandheldFriendly" content="True">
		<meta name="MobileOptimized" content="320">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="Content-Language" content="de">
		<link rel="canonical" href="">
		<meta name="robots" content="noindex, nofollow, noarchive">
		<title><?php $PageController->PrintMetaTitle(); ?></title>
		<link rel="shortcut icon" href="">
		<link rel="apple-touch-icon" href="">
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<style>
			a {
				text-decoration: none !important;
			}
		</style>
		<style>body{padding-top:20px;padding-bottom:20px;}.navbar{margin-bottom:20px;}</style>
		<?php $PageController->PrintHead(); ?>
	</head>
	<body charset="utf-8" lang="de">
		<div class="container">
			<nav class="navbar navbar-default" role="navigation">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="<?php $PageController->PrintBaseURI(); ?>"><?php $PageController->PrintGlobalTitle(); ?></a>
					</div>
					<div id="navbar" class="navbar-collapse collapse">
						<ul class="nav navbar-nav">
							<?php $PageController->PrintNavElements(); ?>
						</ul>
					</div><!--/.nav-collapse -->
				</div><!--/.container-fluid -->
			</nav>
			<div class="page-header">
				<h1><?php $PageController->PrintPageTitle(); ?></h1>
			</div>
			<?php $PageController->PrintPageContent(); ?>
			<?php $PageController->PrintFooterContents(); ?>
		</div>
		<?php $PageController->PrintBodyScripts(); ?>
	</body>
</html>