(function(){
	'use strict';

	angular.module("bgoApp").controller("MainCtrl", controller);

	controller.$inject = ['DataService', '$state'];
	function controller(DataService, $state)
	{
		var me = this;

		me.init = function()
		{
			DataService.getSeasonList().then(
				function(data){
					me.seasonList = data;

					me.selectedSeason = data[0].season;

					DataService.getSeasonTable(me.selectedSeason).then(
					function(d){
						me.seasonDetail = d;
					});

					DataService.getGames(me.selectedSeason).then(
					function(d){
						me.games = d;
					});
				});

			DataService.getLastUpdate().then(
				function(data){
					me.lastUpdate = data;
				});



		}

		me.selectSeason = function(season)
		{
			me.selectedSeason = season;
			DataService.getSeasonTable(me.selectedSeason).then(
				function(data){
					me.seasonDetail = data;
				});
			
			DataService.getGames(me.selectedSeason).then(
					function(d){
						me.games = d;
					});
		}

		me.getPlayerUrl = function(player)
		{
			return $state.href('player', {name: player});
		}

		me.init();

	}
})();

