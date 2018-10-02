var app = angular.module('VorlesungsPlaner', []);



function toApiDate(date, suffix) // date should be an instance of moment
{
    return date.format("DD.MM.YYYY ") + suffix;
}

app.factory('download', ['$rootScope', '$http', function($rootScope, $http) {
    var data = {

        'fetch': function () {
            // this method fetches the JSON data from the API
            $http({
                method: 'GET',
                url: upstream
            }).then(function successCallback(response) {
                if(response.data.length === 0)
                {
                    console.log('Received an error from the server.');
                    console.log(response);
                    $rootScope.$broadcast('fetchError', false);
                }
                else
                {


                    $rootScope.$broadcast('classesFound', response.data);
                }
            }, function errorCallback(response) {
                $rootScope.$broadcast('fetchError', false);
            });
        }
    };

    return data;
}]);

app.controller('listCtrl', [ '$rootScope', '$scope', 'download', function ($rootScope, $scope, ufactory) {

}]);

app.controller('searchCtrl', [ '$rootScope', 'download', function ($rootScope, ufactory) {

}]);

app.controller('genCtrl', [ '$rootScope', 'download', function ($rootScope, ufactory) {

}]);
