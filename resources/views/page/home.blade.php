<div ng-controller="HomeController" class="home container card">
	<h1>最近动态</h1>
	<div class="hr"></div>
	<div ng-repeat="item in Timeline.data" class="item">
		<div class="item-set clearfix">

			<!-- 点赞、点踩的模块 -->
			<div ng-if="item.question_id" class="vote">
				<div ng-click="Timeline.vote({id: item.id, vote: 1 })" class="up-vote">赞[[ item.upvote_count ]]</div>
				<div ng-click="Timeline.vote({id: item.id, vote: 2})" class="down-vote">踩[[item.downvote_count]] </div>
			</div>
			<div class="item-content">

				<!-- 对应NO.1 -->
				<div ng-if="!item.question_id" class="content-act"><span ui-sref="user({id: item.user.id})">[[item.user.username]] 添加了问题</span> </div>
				<!-- 对应NO.2 -->
				<div ng-if="item.question_id" class="content-act">[[item.user.username]]添加了回答 </div>
				

				<!-- 以下两者显示的数据结构的差异，返回的json格式有所不同 -->
				<!-- NO.1后台question表返回的数据，有问题详情，因此直接显示 -->
				<div class="title"><a ui-sref="question.detail({id: item.id})"> [[item.title]] </a></div>

				<!-- NO.2后台answer表返回的数据，多对一，with('question')，返回数据 -->
				<div ng-if="item.question_id" class="title"><span ui-sref="question.detail({id:item.question.id})"> Q: [[item.question.title]]</span> </div>
				<div class="content-owner">
					<span class="username" ui-sref="user({id: item.user.id})">[[item.user.username ]] </span>
					<span class="desc">欢迎大家订阅我的公众号，同时我的知乎专栏是。。。</span>
				</div>
				<div class="content-main">
					[[ item.content ]]
					<div class="gray">
						<a ng-if="item.question_id" ui-sref="question.detail({id: item.question_id, answer_id: item.id})"> 
							[[item.updated_at]] 
						</a>
						<a ng-if="!item.question_id" ui-sref="question.detail({id: item.id})"> 
							[[item.updated_at]] 
						</a>
					</div>
				</div>
				<div class="action-set">
					<div class="follow"><a href="#">关注专栏</a></div>
					<span ng-click="item.show_block = !item.show_block"><span ng-if="item.show_block">取消</span>评论</span>
					<div class="admire"><a href="#">感谢</a></div>
					<div class="share"><a href="#">分享</a></div>
				</div>
				<!-- 隐藏的评论区模块 -->
						<comment-block answer-id="item.id" class="right-item" ng-if="item.show_block"></comment-block>
			</div>
		</div>
		<div class="hr"></div>
	</div>
	<div ng-if="Timeline.pending" class="center">加载中...</div>
	<div ng-if="Timeline.no_more_data" class="center">没有更多数据了...</div>
</div>
