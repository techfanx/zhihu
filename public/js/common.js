;(function(){
	'use strict';

	angular.module('common', [])
		.service('TimelineService',[
			'$http',
			'AnswerService',
			function($http, AnswerService){
				var me = this;
				me.data = [];
				me.current_page = 1;
				
				/*获取首页数据*/
				me.get = function(conf){
					if(me.pending || me.no_more_data) return;

					me.pending = true;

					conf = conf || {page: me.current_page}

					/*调用后台api获取时间线所需数据*/
					$http.post('/api/timeline', conf)
						.then(function(request){
							if(request.data.status){
								/*当服务器刷新数据时，添加数据到data*/
								if(request.data.data.length){
									me.data = me.data.concat(request.data.data);

									/*统计每一条回答的票数*/
 									me.data = AnswerService.count_vote(me.data);
 									/*完成一次后台api的调用，则当前页加1，用以下次调用*/
 									me.current_page++;
								}else
								/*如果返回api返回空，则在前台显示没有更多数据*/
								me.no_more_data = true;
							}
							else{
								console.log('network error');
							}
						},function(e){
							console.error('network error');
						})
						/*无论成功或者失败，都将pending重置为false*/
						.finally(function(){
							me.pending = false;
						})
				}

				/*在时间线中投票*/
				me.vote = function(conf){
					/*调用核心投票功能*/
					if(AnswerService.vote(conf))
						AnswerService.update_data(conf.id);
					else
						console.log('you are voting yourself');
				}
			}
		])

		.controller('HomeController', [
			"$scope",
			"TimelineService", 'AnswerService',
			function($scope, TimelineService, AnswerService){
				var $win;
				$scope.Timeline = TimelineService; // 在这样的一个作用域里。。。Timeline被设置为TimelineService

				TimelineService.get();
				
				$win = $(window)
				$win.on('scroll', function(){
					if($win.scrollTop() - ($(document).height() - $win.height()) > -10){
						TimelineService.get();
					}
				})

				/* 监控数据变化（handwrite）*/
				/*$scope.$watch(function(){
					return AnswerService.data;
				}, function(new_data, old_data){
					var timeline_data = TimelineService.data;
					for(var k in new_data){
						for(var i = 0; i < TimelineService.data.length; i++){
							if(k == timeline_data[i].id){
								timeline_data[i] = new_data[k];
							}
						}
					}
					TimelineService.data = AnswerService.count_vote(TimelineService.data);
 				}, true)*/

 				/*监控回答数据的变化，如果回答数据有变化同时更新其他模块中的回答数据*/
        $scope.$watch(function () {
        		/*检测AnserService.data的变化*/
            return AnswerService.data;
        }, function (new_value, old_value) {
        		/* 短变量名 */
            var timeline_data = TimelineService.data;
            /* 检测到新值，则将其赋给TimelineServicedata */
            for (var k in new_value) {
                /*更新时间线中的回答数据*/
                for (var i=0; i<timeline_data.length; i++) {
                	/*满足两个条件就更新数据，一是*/
                   if (k == timeline_data[i].id && timeline_data[i].question_id) {
                       timeline_data[i] = new_value[k];
                   }
                }
            }
            /*为了同步显示，需重新计票*/
            TimelineService.data = AnswerService.count_vote(TimelineService.data);
        }, true);

			}
		])

})();