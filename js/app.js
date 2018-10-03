

var app = angular.module('Vorlesungsplaner', []);
var upstream = "https://fiwis.fiw.fhws.de/fiwis2/api/classes/";

app.config(function($locationProvider) { $locationProvider.html5Mode({
    enabled: true,
    requireBase: false
}); });

app.filter('idNotInArray', function($filter) {
    return function(list, arrayFilter) {
        if(arrayFilter) {
            return $filter("filter")(list, function(listItem) {
                return arrayFilter.indexOf(listItem.id) === -1;
            });
        }
    };
});

app.factory('download', ['$rootScope', '$http', function($rootScope, $http) {
    var dlSize = 100;

    function fetch(offset) {
        offset = offset || 0;
        // this method fetches the JSON data from the API
        $http({
            method: 'GET',
            url: upstream,
            params: {
                size: dlSize,
                offset: offset
            }
        }).then(function successCallback(response) {
            if(response.data.length === 0)
            {
                console.log('Received empty response, probably finished');
                $rootScope.$broadcast('fetchFinished');
            }
            else
            {
                console.log('Found classes', response.data);
                $rootScope.$broadcast('classesFound', response.data);
                fetch(offset + dlSize);
            }
        }, function errorCallback(response) {
            $rootScope.$broadcast('fetchError', false);
        });
    }

    return {
        'fetch': fetch
    };
}]);

app.controller('listCtrl', [ '$rootScope', '$scope', '$location', 'download', function ($rootScope, $scope, $location, dl) {
    $scope.classes = [];
    $scope.ignored = [];
    $scope.search = {};

    var params = $location.search();
    if('classes' in params) {
        console.log("Found classes params", params);
        $scope.ignored = params.classes.split(',').map(function(s) { return parseInt(s); });
    }

    $scope.$on('classesFound', function(ev, data) {
        console.log("classes added event", data);
        $scope.classes = $scope.classes.concat(data);

        $scope.classes.forEach(function (element, index, arr) {
            // add classes that are ignored via url to the selected class
            if($scope.ignored.indexOf(element.id) !== -1)
                $rootScope.$broadcast('classSelected', element);
        })
    });

    var updateLocation = function () {
        $location.url("?classes=" + $scope.ignored.join(","));
    };

    $scope.select = function (id) {
        console.log("Selected class:", id);
        $scope.ignored.push(id);

        $rootScope.$broadcast('classSelected', $scope.classes.filter(function(c) { return c.id === id; })[0]);
        updateLocation();
    };

    $scope.$on('classUnselected', function (ev, id) {
        $scope.ignored = $scope.ignored.filter(function (e) { return e !== id; });
        updateLocation();
    });

    $scope.$on('$locationChangeSuccess', function (ev, data) {
       console.log("Location changed", ev, data);

        var params = $location.search();
        if('classes' in params && params.classes !== $scope.ignored.join(',')) {
            console.log("Found ignored update via url", params);
            $scope.ignored = params.classes.split(',').map(function(s) { return parseInt(s); });
            $rootScope.$broadcast('classAllSelected', $scope.classes.filter(function (cl) {
                return $scope.ignored.indexOf(cl.id) !== -1;
            }));
        }
    });

    dl.fetch();
}]);

app.controller('searchCtrl', [ '$scope', '$rootScope', 'download', function ($scope, $rootScope, dl) {

}]);

app.controller('genCtrl', [ '$scope', '$rootScope', 'download', function ($scope, $rootScope, dl) {
    $scope.classes = [];

    $scope.$on('classSelected', function (ev, data) {
        console.log("class selected received", data);
        $scope.classes.push(data);
    });

    $scope.$on('classAllSelected', function (ev, data) {
        console.log("full class update received", data);
        $scope.classes = data;
    });

    $scope.unselect = function (id) {
        console.log("Unselected class:", id);
        $scope.classes = $scope.classes.filter(function (cl) { return cl.id !== id; });

        $rootScope.$broadcast('classUnselected', id);
    };
}]);
