<!DOCTYPE html>
<html lang="de" ng-app="Vorlesungsplaner">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="Hier kann man einen Kalender für seine Vorlesungen an der FHWS generieren lassen.">
    <meta name="author" content="Sebastian Kaim">
    <!--<link rel="icon" href="../../favicon.ico">-->

    <title>FHWS Vorlesungsplaner</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/app.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Vorlesungsplaner</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <form class="navbar-form navbar-right" ng-controller="searchCtrl">
                <div class="form-group">
                    <input type="text" placeholder="Vorlesungname oder Studiengang" class="form-control">
                </div>

                <button type="submit" class="btn btn-success">Suchen</button>
            </form>
        </div><!--/.navbar-collapse -->
    </div>
</nav>

<!-- Main jumbotron for a primary marketing message or call to action -->
<div class="jumbotron">
    <div class="container" ng-controller="genCtrl">
        <h1>Ausgewählte Vorlesungen</h1>
        <p class="non-selected-note">Keine Vorlesungen ausgewählt.</p>


        <table class="table">
            <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Dozent</th>
                <th scope="col">Semester</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">Programmieren 1</th>
                <td>Heinzl</td>
                <td>BIN 1</td>
                <td><button class="btn btn-warning">Löschen</button> </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="container" ng-controller="listCtrl">
    <h1>Vorlesungen</h1>

    <div class="well">
        <button class="btn btn-success btn-add">+</button>Programmieren II
    </div>

    <hr>

    <footer>
        <p>&copy; <?php
            $year = date("Y");

            if($year == "2018")
                echo $year;
            else
                echo "2018-$year"
            ?> Sebastian Kaim</p>
    </footer>
</div> <!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>
