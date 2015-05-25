var toolApp = angular.module('toolApp', []);
console.log("Stated");
toolApp.controller('ToolController', function ($scope, $http) {
  $http.get('/members/tools.php?summary=1').success(function(data) {
    $scope.contents = data;
    console.log($scope.contents);
    console.log("Done");
  }).error(function(data, status, headers, config) {
    console.log("Error:" + status);
  });
});