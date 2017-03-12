;(function(){
	'use strict';
	window.his = {
		id: parseInt($('html').attr('user-id'))
	};

	angular.module('zhihu', [
		'ui.router',
		'user',
		'common',
		'question',
		'answer'
	])
		.config(['$interpolateProvider', '$stateProvider', '$urlRouterProvider', 
			function($interpolateProvider, $stateProvider, $urlRouterProvider){
				$interpolateProvider.startSymbol('[[');
				$interpolateProvider.endSymbol(']]');

				$urlRouterProvider.otherwise('/home');
				
				$stateProvider
					.state('home', {
						url:'/home',
						templateUrl:'tpl/page/home' // 如果找不到，这个url就会变为localhost/home.tpl
					})

					.state('signup', {
						url:'/signup',
						templateUrl:'tpl/page/signup'
					})

					.state('login', {
						url:'/login',
						templateUrl:'tpl/page/login'
					})

					.state('question', {
						abstract: true, 
						url:'/question',
						template:'<div ui-view></div>',
						controller: 'QuestionController'
					})

					/*由三部分组成，路由别名，url，模板url*/
					.state('question.detail', {
						url: '/detail/:id?answer_id',
						templateUrl: 'tpl/page/question_detail'
					})

					.state('question.add', {
						url: '/add',
						templateUrl: 'tpl/page/question_add'
					})

					

					.state('user', {
						url: '/user/:id',
						templateUrl: 'tpl/page/user'
					})
			}])

		.controller('BaseController', [
			'$scope',
			function($scope){
				$scope.his = his;
				$scope.helper = {};
				$scope.helper.obj_length = function(obj){
					return Object.keys(obj).length;
				}
			}
		])

})();

