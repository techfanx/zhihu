;(function(){
	'use strict';
	angular.module('answer', [])
		.service('AnswerService', [
			'$http',
			'$state',
			function($http, $state){
				var me = this;
				me.data = [];
				/*me.his = {
        	id: parseInt($('html').attr('user-id'))
        };*/
				/**
				 * 统计票数
				 * @param {array}  用于统计票数的数据
				 * 此数据可以是问题也可以是回答
				 * 如果是问题将会跳过统计
				 */



				me.count_vote = function(answers){
					
					/*迭代所有的数据*/
					for(var i=0; i < answers.length; i++){
						
						/*封装单个数据*/
						var votes, item = answers[i];
						
						/*如果不是回答也没有users元素，说明本条不是回答，或者回答没有任何票数*/
						if(!item['question_id']) continue;

						me.data[item.id] = item;

						if(!item['users'])
							continue;
						
						/*每条回答的默认赞同票和反对票都为零*/
						item.upvote_count = 0;
						item.downvote_count = 0;

						/*users是所有投票用户的用户信息*/
						votes = item['users'];

						if(votes){
							for(var j=0; j<votes.length; j++){
								var v = votes[j];

								/**
								 * 获取pivot元素中的用户投票信息
								 * 如果是1将增加一赞同票
								 * 如果是2将增加一反对票
								 */
								if(v['pivot'].vote == 1)
									item.upvote_count++;
								if(v['pivot'].vote == 2)
									item.downvote_count++;
							}
						}
					}
					return answers;
				}

				/**
				 * @conf为id和vote的配置项
				 * 当没有传递该数据，警告
				 * 传递数据，返回一个promise对象
				 */
				me.vote = function(conf){
					if(!conf.id || !conf.vote){
						console.log('id and vote are required');
						return false;
					}

					/*先取出对应请求的回答id对应的数据*/
					var answer = me.data[conf.id];

					/*取出id下answer中的users数据*/
					var users = answer.users;

					if(answer.user_id == his.id){
						console.log('you are voting yourself!');

						return false;
					}

					/**
					 * 遍历已投票的用户，并将其中的id一一与全局变量中的his.id比对，
					 * 如果session中当前登录用户已存在于点赞用户列表中，那么设置conf.vote
					 * 的值为3，即我们将取消掉当前用户此前的投票
					 */
					for(var i = 0; i < users.length; i++){
						if(users[i].id == his.id && conf.vote == users[i].pivot.vote)
							conf.vote = 3;
					}
					
					/**
					 * 如果conf.vote=3，那么在后台api当中我们将删除当前登录用户上一次的点赞或者点踩记录
					 * 并且在执行vote方法后，调用vote_count方法，重新计票。
					 */
					return $http.post('/api/answer/vote', conf)
						.then(function(r){
							if(r.data.status)
								return true;
							/*如果返回的msg为要求登录，则跳转到登录界面*/
							else if(r.data.msg = 'login required')
								$state.go('login');
							else
								return false;
						}, function(){
							return false;
						});
				}

				me.submit_form = function(){
					
				}
				/*点赞后更新数据（AnswerService当中的）*/
				me.update_data = function(id){
					return $http.post('/api/answer/read', {id: id})
						.then(function(r){
							me.data[id] = r.data.data;
						})
					// if(angular.isNumeric(input))
					// 	var id = input;
					// if(angular.isArray(input))
					// 	var id_set = input;
				}

				me.read = function(params){
					return $http.post('api/answer/read', params)
						.then(function(r){
							if(r.data.status){
								me.data = angular.merge({}, me.data, r.data.data);
								return r.data.data;
							}
							return false;
						})
				}

				/*删除回答*/
				me.delete = function(id){
					if(!id){
						console.error('id is required');
						return;
					}

					$http.post('/api/answer/remove', {id: id})
						.then(function(r){
							if(r.data.status){
								$state.reload();
							}
						})
				}

        me.add_comment = function () {
            return $http.post('api/comment/add', me.new_comment)
                .then(function (request) {
                    if (request.data.status) {
                        return true;
                    }else {
                        return false;
                    }
                });
        }

				/*增加或者修改回答*/
				me.add_or_update = function(question_id){
					if(!question_id){
						return "question_id is required";
					}

					
					/*form表单当中的数据模型，包含了answer_form.content*/
					/*此处相当于增加了api必须的question_id信息，即明确将答案添加到哪一个question中*/
					me.answer_form.question_id = question_id;

					if(me.answer_form.id){
						$http.post('api/answer/change', me.answer_form)
							.then(function(r){
								me.answer_form = {};
								$state.reload();
							})
					}else{
						$http.post('api/answer/add', me.answer_form)
							.then(function(r){
								me.answer_form = {};
								$state.reload();
							});
					}
				}

			}])
		/*angular中的指令*/
		.directive('commentBlock', ['$http', 'AnswerService',
            function ($http, AnswerService) {
                var block = {};
                block.scope = {
                    answer_id: '=answerId'
                }

                block.templateUrl = 'tpl/page/comment';
                block.link = function (scope, ele, attr) {
                    scope._ = {};
                    scope.Answer = AnswerService;
                    scope.data = {};
                    scope.getObjectLength = getObjectLength;
                    ele.on('click', function () {
                    });
                    get_comment_data();
                    scope._.add_comment = function () {
                        AnswerService.new_comment.answer_id = scope.answer_id;
                        AnswerService.add_comment()
                            .then(function (r) {
                                if(r) {
                                		/*如果添加评论成功，则将评论的模型数据清空*/
                                    AnswerService.new_comment = {};
                                    /*重新获取评论数据*/
                                    get_comment_data();
                                }
                            });
                    }
                    function get_comment_data() {
                        $http.post('api/comment/read', {answer_id: scope.answer_id})
                            .then(function (r) {
                                if (r.data.status) {
                                    scope.data = r.data.data;
                                }
                            });
                    }
                    function getObjectLength(data) {
                        return Object.keys(data).length;
                    }
                };
                return block;
            }
        ])
    ;
})();