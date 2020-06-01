(function(){
	'use strict';

	angular.module("bgoApp").controller("TableCtrl", controller);

	controller.$inject = ['DataService', '$state', '$stateParams', 'data'];
	function controller(DataService, $state, $stateParams, data)
	{
		var me = this;
		me.data = data;

		me.playerList = [];
		me.player1 = '';
		me.player2 = '';
		me.player3 = '';

		me.init = function()
		{
			DataService.getOverallTable().then(
				function(data){
					me.data = data;
				});

			DataService.getPlayers().then(
				function(data){
					me.playerList = data;
				});
		}

		me.update = function()
		{

			DataService.getOverallTable(me.player1, me.player2, me.player3).then(
				function(data){
					me.data = data;
				});
		}

		me.sort = function(sortable)
		{
			if(me.sort === sortable)
				me.sort = '-' + sortable;
			else 
				me.sort = sortable;
		}

		me.init();

	}
})();
