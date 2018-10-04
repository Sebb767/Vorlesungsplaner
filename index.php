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
        <p class="non-selected-note" ng-show="classes.length === 0">Keine Vorlesungen ausgewählt.</p>


        <div class="selected-showcase" ng-show="classes.length !== 0">
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
                <tr ng-repeat="cl in classes">
                    <th scope="row">{{ cl.name }}</th>
                    <td>{{ cl.lecturerNamesToShow }}</td>
                    <td>{{ cl.studyGroupsToShow }}</td>
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
                        <button class="btn btn-success">Downloaden</button>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<div class="container" ng-controller="listCtrl">
    <h1>Vorlesungen</h1>

    <div class="well" ng-repeat="cl in classes | idNotInArray:ignored">
        <button class="btn btn-success btn-add" ng-click="select(cl.id)">+</button>
        {{ cl.name }} - {{ cl.studyGroupsToShow }}
        <span class="lecturer-name pull-right">{{ cl.lecturerNamesToShow }}</span>
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
<script src="js/angular.min.js"></script>
<script src="js/app.js"></script>
<script>
$(function () {
    $('[data-toggle="popover"]').popover().click(function () {
        setTimeout(function () {
            $('[data-toggle="popover"]').popover('hide');
        }, 2000);
    });
});
</script>
</body>
</html>
