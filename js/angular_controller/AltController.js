(function(){
	'use strict';

	angular.module("bgoApp").controller("AltCtrl", controller);

	controller.$inject = ['DataService', '$state'];
	function controller(DataService, $state)
	{
		var me = this;

		me.init = function()
		{
			DataService.getSeasonList().then(
				function(data){
					me.seasonList = data;

					me.selectedSeason = data[0];

					DataService.getAlternativeTable(me.selectedSeason.season).then(
					function(d){
						me.seasonDetail = d;
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
			DataService.getAlternativeTable(me.selectedSeason.season).then(
				function(data){
					me.seasonDetail = data;
				});
		}

		me.getPlayerUrl = function(player)
		{
			return $state.href('player', {name: player});
		}

		me.init();

	}
})();

