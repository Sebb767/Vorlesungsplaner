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

    <!-- Custom styles for this template -->
    <link href="css/app.css" rel="stylesheet">

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
                    <input type="text" placeholder="Suchtext" class="form-control" ng-model="query" ng-change="broadcastQuery()"
                           data-toggle="tooltip" title="Tipp: Du kannst nach dem Vorlesungsnamen, Dozenten, Studiengang (auch mit Semester) und Fakultät suchen. Beispiel: 'Programmieren BIN2'">
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
        <p class="non-selected-note" ng-show="classes.length === 0 && loaded">Keine Vorlesungen ausgewählt.</p>
        <p class="non-selected-note" ng-hide="loaded">Lade ...</p>


        <div class="selected-showcase" ng-show="classes.length !== 0 && loaded">
            <div class="alert alert-info">
                <b>Tipp:</b> Du kannst diesen Link zu deinen Bookmarks hinzufügen oder teilen, um wieder zu deinem
                persönlichem Plan zu kommen.
            </div>
            <div class="alert alert-warning" ng-show="hasBrokenClasses">
                <b>Achtung:</b> Zu einigen Vorlesungen konnten keine Daten gefunden werden. Vermutlich ist dies
                ein temporärer Fehler. Sollte dieser jedoch bestehen bleiben, gibt es entweder die Vorlesung nicht mehr,
                die API hat einen dauerhaften, technischen Fehler oder die aufgerufene URL ist fehlerhaft.
            </div>

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
                <tr ng-repeat="cl in classes" ng-class="{ 'broken-entry': cl.broken }">
                    <th scope="row">{{ cl.name }}</th>
                    <td>{{ cl.lecturerNamesToShow || "-" }}</td>
                    <td>{{ cl.studyGroupsToShow || "-" }}</td>
                    <td><button class="btn btn-warning" ng-click="unselect(cl.id);">Löschen</button> </td>
                </tr>
                </tbody>
            </table>

            <div class="well well-sm alert-success">
                <div class="row">
                    <div class="col-sm-2 col-md-1 url-field-text">
                        ICS Link:
                    </div>
                    <div class="col-sm-6 col-md-7 col-lg-8">
                         <input class="url-field" type="text" readonly="readonly" ng-value="dllink" onclick="this.select()">
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-3">
                        <button
                                class="btn btn-success"
                                ng-click="copyLinkToClipboard()"
                                data-toggle="popover"
                                data-trigger="click"
                                data-placement="top"
                                data-content="Link kopiert!">Kopieren</button>
                        <a href="{{ dllink }}&download" target="_blank">
                            <button class="btn btn-success">Downloaden</button>
                        </a>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<div class="container" ng-controller="listCtrl">
    <h1><span ng-hide="loaded"><img src="loading.gif" class="loading-spinner">&nbsp;</span> Vorlesungen</h1>

    <div class="loading-error-container" ng-hide="loadingErrors.length === 0">
        <div class="alert alert-warning loading-error-alert" role="alert">
            <b>Fehler!</b> Die Daten einiger Fakultäten konnten nur teilweise oder gar nicht geladen werden:

            <ul>
                <li ng-repeat="faculty in loadingErrors">{{ faculty }}</li>
            </ul>

            Sofern Sie Vorlesungen dieser Fakultät benötigen laden Sie bitte die Seite neu. Wenn der<br>
            Fehler dauerhaft bestehen bleibt wenden Sie sich bitte an den Administrator.
        </div>
    </div>

    <div class="well" ng-repeat="cl in classes | idNotInArray:ignored | classSearch:query">
        <div class="row">
            <div class="col-sm-12 col-md-8">
                <button class="btn btn-success btn-add" ng-click="select(cl.id)">+</button>
                {{ cl.name }} - {{ cl.studyGroupsToShow }}
            </div>
            <div class="col-sm-12 col-md-4">
                <span class="lecturer-name">{{ cl.lecturerNamesToShow }}</span>
            </div>
        </div>
    </div>

    <hr>

    <footer>
        <p>&copy; <?php
            $year = date("Y");

            if($year == "2018")
                echo $year;
            else
                echo "2018-$year"
            ?> <a href="https://kaim-consulting.com/">Sebastian Kaim</a>
            &#183; Lizensiert unter der <a href="https://choosealicense.com/licenses/mit/">MIT Lizenz</a>
            &#183; Source auf <a href="https://github.com/Sebb767/Vorlesungsplaner">GitHub</a>
        </p>
    </footer>
</div> <!-- /container -->


<!-- core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script>
var sources = <?php
    echo json_encode(require 'config.php');
?>;
</script>
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/angular.min.js"></script>
<script src="js/app.js"></script>
<script>
$(function () {
    $('[data-toggle="popover"]').popover().click(function () {
        setTimeout(function () {
            $('[data-toggle="popover"]').popover('hide');
        }, 2000);
    });

    $('[data-toggle="tooltip"]').tooltip({
        placement: "bottom",
        trigger: "focus"
    });
});
</script>
</body>
</html>
