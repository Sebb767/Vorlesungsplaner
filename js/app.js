var app = angular.module('Vorlesungsplaner', []);
// for now just take the first upstream
var upstream = sources[Object.keys(sources)[0]];

app.config(function($locationProvider) { $locationProvider.html5Mode({
    enabled: true,
    requireBase: false
}); });

// https://stackoverflow.com/a/46532967
app.provider('$copyToClipboard', [function () {

    this.$get = ['$q', '$window', function ($q, $window) {
        var body = angular.element($window.document.body);
        var textarea = angular.element('<textarea/>');
        textarea.css({
            position: 'fixed',
            opacity: '0'
        });
        return {
            copy: function (stringToCopy) {
                var deferred = $q.defer();
                deferred.notify("copying the text to clipboard");
                textarea.val(stringToCopy);
                body.append(textarea);
                textarea[0].select();

                try {
                    var successful = $window.document.execCommand('copy');
                    if (!successful) throw successful;
                    deferred.resolve(successful);
                } catch (err) {
                    deferred.reject(err);
                    //window.prompt("Copy to clipboard: Ctrl+C, Enter", toCopy);
                } finally {
                    textarea.remove();
                }
                return deferred.promise;
            }
        };
    }];
}]);

app.filter('idNotInArray', function($filter) {
    return function(list, arrayFilter) {
        if(arrayFilter) {
            return $filter("filter")(list, function(listItem) {
                return arrayFilter.indexOf(listItem.id) === -1;
            });
        }
    };
});

app.filter('classSearch', function($filter) {
    var keywordCache = {};

    function splitByWhitespace(str) {
        if(!str)
            return [];
        else
            return str.match(/[^\s]+/g);
    }

    function getClassKeywords(cl) {
        if(cl.id in keywordCache)
            return keywordCache[cl.id];

        var keywords = [];

        // add semester keyword, i.e. BIN3
        cl.studentsView.forEach(function (el) {
            keywords.push(el.program + el.semester);
        });

        // add lecturer name and title as keyword
        cl.lecturerView.forEach(function (el) {
            keywords.push(el.firstName);
            keywords.push(el.lastName);
            keywords = keywords.concat(splitByWhitespace(el.title));
        });

        // lastly, add the lecture name and official id
        keywords.push(cl.name);
        keywords.push(cl.id);

        // make everything lowercase and remove nulls
        keywords = keywords
            .filter(function(kw) { return kw; })
            .map(function (kw) { return kw.toString().toLowerCase(); });

        keywordCache[cl.id] = keywords;
        return keywords;
    }

    // returns true when all words in the query array are contained in keywords
    function crossMatch(query, keywords) {
        return !query.some(function (q) { return !keywords.some(function(kw) { return kw.indexOf(q) !== -1; }); });
    }

    return function(list, query) {
        if(query) {
            var parts = splitByWhitespace(query.toLowerCase());

            return $filter("filter")(list, function(listItem) {
                var keywords = getClassKeywords(listItem);
                return crossMatch(parts, keywords);
            });
        }

        return $filter("filter")(list, function(listItem) {
            return true;
        });
    };
});

app.factory('download', ['$rootScope', '$http', function($rootScope, $http) {
    var dlSize = 100;

    function fetch(_faculty, offset) {
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
                $rootScope.$broadcast('fetchFinished', { faculty: _faculty });
            }
            else
            {
                console.log('Found classes', response.data, _faculty);

                var refinedData = response.data.map(function (classdata) {
                    classdata.faculty = _faculty;
                    classdata.apiId = classdata.id;
                    classdata.id = _faculty + ':' + classdata.id;
                    classdata.broken = false;
                    return classdata;
                });

                $rootScope.$broadcast('classesFound', refinedData);
                fetch(_faculty, offset + dlSize);
            }
        }, function errorCallback(response) {
            $rootScope.$broadcast('fetchError', { faculty: _faculty });
        });
    }

    function fetchAll() {
        // sources is an externel variable set by the index page, poiting to the various APIs
        var faculties = Object.keys(sources);

        function facutlyFinished(fac) {
            faculties = faculties.filter(function(fk) { return fk !== fac; });
            console.log('Faculty ' + fac + ' ended fetching data');

            if (faculties.length === 0)
            {
                $rootScope.$broadcast('fetchFinishedAll', true);
            }
        }

        $rootScope.$on('fetchFinished', function(ev, data) {
            facutlyFinished(data.faculty);
        });
        $rootScope.$on('fetchError', function(ev, data) {
            facutlyFinished(data.faculty);
        });

        faculties.forEach(function (fac) {
            fetch(fac);
        });
    }

    return {
        'fetch': fetchAll
    };
}]);

app.controller('listCtrl', [ '$rootScope', '$scope', '$location', 'download', function ($rootScope, $scope, $location, dl) {
    $scope.classes = [];
    $scope.ignored = [];
    $scope.query = "";
    $scope.loaded = false;
    $scope.loadingErrors = [];

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
        });
    });

    $scope.$on('fetchFinishedAll', function(ev, data) {
        $scope.loaded = true;

        // check if some classes weren't loaded
        var selectedClasses = $scope.classes.filter(function (cl) {
            return $scope.ignored.indexOf(cl.id) !== -1;
        });
        var knownIds = selectedClasses.map(function (c) { return c.id });

        var missing = $scope.ignored.length - selectedClasses.length;
        if (missing > 0) {
            var converted = 0;
            var i;
            var j;

            var ignCopy = $scope.ignored;
            for (i in ignCopy) {
                var id = ignCopy[i];

                if (knownIds.indexOf(id) !== -1)
                    continue;

                // first, check if it's an old id w/o faculty
                var intId = parseInt(id);
                var cont = false;

                if (intId && intId.toString() == id) {
                    console.log("searching", id);
                    for (j in $scope.classes) {
                        var clx = $scope.classes[j];
                        if (clx.apiId === intId) {
                            // found a matching class!

                            // first, remove the wrong id
                            $scope.ignored = $scope.ignored.filter(function (x) { return x !== id; });

                            // then simply selected the right one
                            $scope.select(clx.id);
                            console.log("Updated old id", id, clx.id);
                            converted++;
                            cont = true;
                            break;
                        }
                    }
                }

                // do not count as missing when the class was converted
                if (cont)
                    continue;

                // otherwise, publish a dummy
                missing++;
                $rootScope.$broadcast('classSelected', { id: id, name: id, broken: true })
            }

            console.log("Found " + missing + " selected classes which were not detailed by the api, " + converted + " of which could be converted.");
        }
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

    $scope.$on('fetchError', function (ev, data) {
        if (data.faculty)
            $scope.loadingErrors.push(data.faculty);
    });

    $scope.$on('$locationChangeSuccess', function (ev, data) {
       console.log("Location changed", ev, data);

        var params = $location.search();
        if('classes' in params && params.classes !== $scope.ignored.join(',')) {
            console.log("Found ignored update via url", params);
            $scope.ignored = params.classes.split(',').filter(function (part) { return part && part !== 'Nan'; });

            var selectedClasses = $scope.classes.filter(function (cl) {
                return $scope.ignored.indexOf(cl.id) !== -1;
            });

            $rootScope.$broadcast('classAllSelected', selectedClasses);
        }
    });

    $scope.$on('searchUpdated', function (ev, data) {
        $scope.query = data;
    });

    dl.fetch();
}]);

app.controller('searchCtrl', [ '$scope', '$rootScope', function ($scope, $rootScope) {
    $scope.query = "";

    $scope.broadcastQuery = function () {
        $rootScope.$broadcast('searchUpdated', $scope.query);
    }
}]);

app.controller('genCtrl', [ '$scope', '$rootScope', '$copyToClipboard', '$location', 'download',
    function ($scope, $rootScope, $ctc, $location, dl) {
    $scope.classes = [];
    $scope.dllink = "";

    var updateDlLink = function() {
        var url = $location.absUrl();
        if(url.indexOf('?') !== -1) { // strip query parameter
            url = url.substr(0, url.indexOf('?'));
        }
        if(url.indexOf('#') !== -1) { // strip anchor
            url = url.substr(0, url.indexOf('#'));
        }
        $scope.dllink = url + "ics.php?classes=" + $scope.classes.map(function (c) { return c.id; }).join(",");
    };

    $scope.$on('classSelected', function (ev, data) {
        console.log("class selected received", data);
        $scope.classes.push(data);
        updateDlLink();
    });

    $scope.$on('classAllSelected', function (ev, data) {
        console.log("full class update received", data);
        $scope.classes = data;
        updateDlLink();
    });

    $scope.unselect = function (id) {
        console.log("Unselected class:", id);
        $scope.classes = $scope.classes.filter(function (cl) { return cl.id !== id; });

        $rootScope.$broadcast('classUnselected', id);
        updateDlLink();
    };

    $scope.copyLinkToClipboard = function () {
        $ctc.copy($scope.dllink);
    };
}]);
