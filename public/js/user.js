;(function(){
	'use strict';
	angular.module('user', [
		'answer',
		'question'
	])
		.service('UserService', ['$state', '$http',
				function($state, $http){
					var me = this;
					me.signup_data = {};
					me.login_data = {};
					me.data={};

					/*这是个坑，没写完*/
					me.read = function(param){
						return $http.post('/api/user/read', param)
							.then(function(r){
								if(r.data.status){
									if(param.id == 'self' || angular.isNumber(parseInt(param.id))){
										me.current_user = r.data.data;
									me.data[param.id] = r.data.data;
									}else if(r.data.msg === 'login required'){
										$state.go('login');
									}
								}else{
									if(r.data.msg == 'login required')
										$state.go('login');
								}
							},function(e){

							})
					}

					me.signup = function(){
						$http.post('/api/signup', me.signup_data)
							.then(function(r){
								if(r.data.status){
									me.signup_data = {};
									$state.go('login');
								}

							},function(e){

							})
					}

					me.login = function(){
						$http.post('/api/login', me.login_data)
							.then(function(r){
								if(r.data.status){
							console.log('登陆成功');
									location.href='/';
									/*$state.go('home');*/
								}else{
									me.login_failed = true;
								}
							},function(){ //此处逗号不能忘记添加

							})
					}

					me.username_exist = function(){
						$http.post('/api/user/exist', 
							{username: me.signup_data.username})
							.then(function(r){
								if(r.data.status && r.data.data.count)
									me.signup_username_exist = true;
								else
									me.signup_username_exist = false;
							}, function(){
								console.log('e', e);
							})
					}
				}])

		.controller('SignupController', [
				'$scope',
				'UserService',
				function($scope, UserService){ //此处的Scope，需要作为从参数传入到function当中
					$scope.User = UserService;

					$scope.$watch(function(){
						return UserService.signup_data;
					}, function(n, o){
						if(n.username != o.username)
							UserService.username_exist();
					}, true);
				}]).controller('SignupController', [
				'$scope',
				'UserService',
				function($scope, UserService){ //此处的Scope，需要作为从参数传入到function当中
					$scope.User = UserService;

					$scope.$watch(function(){
						return UserService.signup_data;
					}, function(n, o){
						if(n.username != o.username)
							UserService.username_exist();
					}, true);
				}])

				.controller('LoginController', [
				'$scope',
				'UserService',
				function ($scope, UserService) {
					$scope.User = UserService;
				}])

		.controller('UserController', [
			'$scope',
			'$stateParams',
			'UserService',
			'QuestionService',
			'AnswerService',
			function($scope, $stateParams, UserService, QuestionService, AnswerService){
				$scope.User = UserService;
				UserService.read($stateParams);
				/*当user_id是用户点击其他用户对应的其他用户user_id时，该怎么处理？*/
				AnswerService.read({'user_id': $stateParams.id})
					.then(function(r){
						if(r)
							UserService.his_answers = r;
					});

				QuestionService.read({'user_id': $stateParams.id})
					.then(function(r){
						if(r)
							UserService.his_questions = r;
					});
			}
		])

})();