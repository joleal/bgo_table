(function(){
'use strict';

	angular
		.module('bgoApp')
		.factory('DataService', dataService);

	dataService.$inject = ['$http', '$q'];
	function dataService($http, $q){

		return {
			getHistory: getHistory,
			getGameDetail: getGameDetail,
			getPlayerDetail: getPlayerDetail,
			getSeasonTable: getSeasonTable,
			getAlternativeTable: getAlternativeTable,
			getSeasonList: getSeasonList,
			getLastUpdate: getLastUpdate,
			getOverallTable: getOverallTable,
			getPlayers: getPlayers,
			getGames: getGames
		};

		function getHistory()
		{
			return [];
		}

		function getGameDetail(gameId)
		{
			return [];
		}

		function getPlayers()
		{
			return $http.get('/php/getPlayers.php?q=t')
				.then(function(response){
					return response.data;
				});
		}

		function getOverallTable(player1, player2, player3)
		{
			var p1 = player1 ? '&p1=' + player1 : '';
			var p2 = player2 ? '&p2=' + player2 : '';
			var p3 = player3 ? '&p3=' + player3 : '';
			return $http.get('/php/getOverallTable.php?q=t' + p1 + p2 + p3)
				.then(function(response){
					return response.data;
				});
		}

		function getPlayerDetail(name)
		{
			return $http.get('/php/getPlayerDetail.php?name=' + name)
				.then(function(response){
					return response.data;
				});
		}

		function getSeasonTable(season)
		{
			return $http.get('/php/getSeasonTable.php?season=' + season)
				.then(function(response){
					var result = response.data;

					var output = [];
					var league = result[0].league;
					var table = {"division": league, "players": []};
					for(var i = 0; i < result.length; i++)
					{
						var nLeague = result[i].league;
						if(nLeague != table.division)
						{
							output.push(table);
							table = {"division": nLeague, "players": []};
						}
						table.players.push(result[i]);
					}
					output.push(table);
					return output;
				});
		}

		function getGames(season)
		{
			return $http.get('/php/getGames.php?season=' + season)
				.then(function(response){
					return response.data;
				});
		}

		function getAlternativeTable(season)
		{
			return $http.get('/php/getAlternativeTable.php?season=' + season)
				.then(function(response){
					var result = response.data;

					var output = [];
					var league = result[0].league;
					var table = {"division": league, "players": []};
					for(var i = 0; i < result.length; i++)
					{
						var nLeague = result[i].league;
						if(nLeague != table.division)
						{
							output.push(table);
							table = {"division": nLeague, "players": []};
						}
						table.players.push(result[i]);
					}
					output.push(table);
					return output;
				});
		}

		function getSeasonList()
		{
			return $http.get('/php/getSeasonList.php')
				.then(function(response){
					return response.data;
				});
		}

		function getLastUpdate()
		{
			return $http.get('/php/getLastUpdate.php')
				.then(function(response){
					return response.data;
				});
		}
	}
})();
