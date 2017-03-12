;(function(){
	'use strict';
	angular.module('question', [])
	
			.service('QuestionService', [
				'$http',
				'$state',
				'AnswerService',
				function($http, $state, AnswerService){
					var me = this;
					me.new_question = {}; //默认等于一个空对象
					me.data = {};
					me.go_add_question = function(){
						$state.go('question.add');
					}

					
					/*某问题下的详细信息*/
					me.read = function(params){
						/*后台api返回数据*/
						return $http.post('/api/question/read', params)
							.then(function(r){
								var its_answers;
								/*接收数据*/
								if(r.data.status){
									/*如果有参数id，则将数据存入到对应data数组中，这样避免，这样我们就只要特定的数据*/
									if(params.id){
										me.data = me.current_question = r.data.data;
										me.its_answers = me.current_question.answers_with_user_info;
										// console.log(me.its_answers.length);
										/*调用AnswerService中的count_vote方法，统计票数*/
										me.its_answers = AnswerService.count_vote(me.its_answers);
									}

									else{
										/*如果没有参数id，说明我们查看的是全部问题，这时候不能替代赋值，而是要将新数据与之前的合并*/
										me.data = angular.merge({}, me.data, r.data.data);
									}
									return r.data.data;
								}
								return false;
							})
					}

					me.vote = function(conf){
						/*调用核心模块*/
						if(AnswerService.vote(conf))
							me.update_answer(conf.id);
						else
							console.log('you are voting yourself');

						/*调用核心模块*/
						/*AnswerService.vote(conf)
							.then(function(r){
								if(r){
									console.log(r);
									me.update_answer(conf.id);
								}else{
									console.log('投票失败');
								}
							})*/
					} 
					
					me.update_answer = function(answer_id){
						$http.post('/api/answer/read', {id: answer_id})
							.then(function(r){
								if(r.data.status){
									/*遍历当前答案*/
									for(var i = 0; i < me.its_answers.length; i++){
										var answer = me.its_answers[i];
										if(answer.id == answer_id){
											me.its_answers[i] = r.data.data;
											AnswerService.data[answer_id] = r.data.data;
										}
									}
								}
							})
					}


					me.add = function(){
						if(!me.new_question.title)
							return;
						$http.post('/api/question/add', me.new_question)
							.then(function(r){
								if(r.data.status)
									me.new_question = {};
								else{
									$state.go('login');
									return;
								}
								$state.go('home');
							},function(e){
								
							})
					}

					me.update = function(){
						if(!me.current_question.title){
							console.log('title is required');
							return false;
						}
						return $http.post('/api/question/change', me.current_question)
							.then(function(r){
								if(r.data.status){
									me.show_update_form = false;
								}
							})

					}
				}])

			.controller('QuestionController', ['$scope', 'QuestionService',
            function ($scope, QuestionService) {
                $scope.Question = QuestionService;
            }
        ])
			
			.controller('QuestionAddController', [
				'$scope',
				'QuestionService',
				function($scope, QuestionService){
					$scope.Question = QuestionService;
				}
			])

			.controller('QuestionDetailController', [
				'$scope',
				'$stateParams',
				'QuestionService',
				'AnswerService',
				function($scope, $stateParams, QuestionService, AnswerService){
					$scope.Question = QuestionService;
					$scope.Answer = AnswerService;
					/*根据传递的参数，读取我们所需要的question*/
					QuestionService.read($stateParams); // 这一步存在严重问题
					if($stateParams.answer_id)
						QuestionService.current_answer_id = $stateParams.answer_id;
					else
						QuestionService.current_answer_id = null;
				}
			])

})();