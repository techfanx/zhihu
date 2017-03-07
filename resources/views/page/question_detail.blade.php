<div ng-controller="QuestionDetailController" class="container question-detail">
	<div class="card">
		<h1> [[Question.current_question.title]]
		<small ng-if="his.id == Question.current_question.user_id" ng-click="Question.show_update_form = !Question.show_update_form" class="gray"><span ng-if="Question.show_update_form">取消</span>修改问题</small></h1>

		<!-- 默认隐藏的修改问题模块 -->
		<div class="desc"> [[Question.current_question.desc]] </div>
			<form class="gray-card well" ng-if="Question.show_update_form" name="question_update_form" ng-submit="Question.update()">
				<div class="input-group">
					<label>问题标题</label>
					<input type="text"
						   name="title"
						   ng-minlength="5"
						   ng-maxlength="255" 
						   required 
						   type="text"
						   ng-model-options="{debounce: 500}"
						   ng-model="Question.current_question.title">
					
				</div>
				<div class="input-group">
					<label>问题描述</label>
					<textarea name="desc" type="text" ng-model="Question.current_question.desc"></textarea>
				</div>
				<div class="input-group">
					<button type="submit" ng-disabled="question_add_form.title.$invalid" class="primary">提交</button>
				</div>
			</form>

		<div>
			<span class="gray">
				回答数:[[Question.current_question.answers_with_user_info.length]]
			</span>
		</div>
		<div class="hr"></div>
		<div class="center well gray" ng-if="!Question.current_question.answers_with_user_info.length">还没有人回答，快来抢沙发</div>
		<div class="answer-block">
			<div ng-if="!Question.current_answer_id || Question.current_answer_id == item.id" ng-repeat="item in Question.current_question.answers_with_user_info" class="item-set clearfix">
				<div class="vote"> 
					<div ng-click="Question.vote({id: item.id, vote: 1 })" class="up-vote" >
						赞[[item.upvote_count]]
					</div>
					<div ng-click="Question.vote({id: item.id, vote: 2})" class="down-vote">
						踩[[item.downvote_count]]
					</div>
				</div>
				<div class="feed-item-content">
					<div class="title">
						<div ><span ui-sref="user({id: item.user.id})">[[item.user.username]]</span></div>
						<div>[[item.content]]</div>

						<!-- 编辑修改回答 -->
						<div class="gray" > 
							<span ng-click="item.show_block = !item.show_block" class="anchor"><span ng-if="item.show_block">取消</span>评论</span>
							<span class="anchor"  ng-if="item.user_id == his.id">
								<span ng-click="Answer.answer_form = item" class="gray" ng-if="item.user_id == his.id" >
									编辑
								</span> 
								<span ng-click="Answer.delete(item.id)" ng-if="item.user_id == his.id" >
									删除
								</span> [[item.updated_at]] 
							</span>
						</div>
						<br/>

						<!-- 隐藏的评论区模块 -->
						<comment-block answer-id="item.id" class="right-item" ng-if="item.show_block"></comment-block>
						<dir class="hr"></dir>
					</div>
				</div>
			</div>

			<!-- 添加问题回答模块 -->
			<div>
				<form name="answer_form" class="answer_form" ng-submit="Answer.add_or_update(Question.data.id)">
					<div class="input-group">
						<textarea type="text"
								placeholder="添加回答" 
							  name="content"
							  rows="5"
							  required 
							  ng-model="Answer.answer_form.content">
						</textarea>
					</div>
					<div class="input-group">
						<button ng-disabled="answer_form.$invalid" class="primary" type="submit">提交</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>