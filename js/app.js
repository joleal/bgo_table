angular.module("bgoApp", ["ui.router"]);

angular.module("bgoApp").config(function($stateProvider, $urlRouterProvider) {

	//For any unmatched URL, redirect to /dashboard
	$urlRouterProvider.otherwise("/home");
	var stateHome = {
		url: "/home?league",
		templateUrl: "views/home.partial.html",
		controller: "MainCtrl",
		controllerAs: "ctrl"
	};

	var stateAltHome = {
		url: "/alternative?league",
		templateUrl: "views/alternative.partial.html",
		controller: "AltCtrl",
		controllerAs: "ctrl"
	};

	var statePlayer = {
		url: "/player?name",
		templateUrl: "views/player.partial.html?v=" + Date.now(),
		controller: "PlayerCtrl",
		controllerAs: "player",
		resolve: {
			data: ['DataService', '$stateParams',
				function(DataService, $stateParams){
					if($stateParams && $stateParams.name)
						return DataService.getPlayerDetail($stateParams.name);
				}]
		}
	};

	var stateHistory = {
		url: "/history",
		templateUrl: "views/history.partial.html?v=" + Date.now(),
		controller: "HistoryCtrl",
		controllerAs: "history",
		resolve: {
			data: ['DataService',
				function(DataService){
					return DataService.getHistory();
				}]
		}
	};

	var stateGame = {
		url: "/game?game_id",
		templateUrl: "views/game.partial.html?v=" + Date.now(),
		controller: "GameCtrl",
		controllerAs: "game",
		resolve: {
			data: ['DataService','$stateParams',
				function(DataService, $stateParams){
					if($stateParams && $stateParams.game_id)
						return DataService.getGameDetail($stateParams.game_id);
				}]
		}
	};

	var stateTable = {
		url: "/table",
		templateUrl: "views/table.partial.html?v=" + Date.now(),
		controller: "TableCtrl",
		controllerAs: "ctrl",
		resolve: {
			data: ['DataService',
				function(DataService){
					return DataService.getOverallTable();
				}]
		}
	};

	//Now set up the states
	$stateProvider
		.state('home', stateHome)
		.state('alternative', stateAltHome)
		.state('player', statePlayer)
		.state('history', stateHistory)
		.state('game', stateGame)
		.state('table', stateTable)
		;

});

angular.module("bgoApp").run(['$rootScope', '$state', '$window',
	function($rootScope, $state, $interval, $window) {
		return;
}]);

