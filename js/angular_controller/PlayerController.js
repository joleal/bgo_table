(function(){
	'use strict';

	angular.module("bgoApp").controller("PlayerCtrl", controller);

	controller.$inject = ['DataService', '$state', '$stateParams', 'data'];
	function controller(DataService, $state, $stateParams, data)
	{
		var me = this;
		me.data = data;
		me.init = function()
		{
			DataService.getPlayerDetail($stateParams.name).then(
				function(data){
					me.data = data;
				});
		}

		me.init();

	}
})();
