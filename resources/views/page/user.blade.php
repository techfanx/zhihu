	<div ng-controller="UserController" class="user container card">
		<h1>用户详情</h1>
		<div class="hr"></div>
		<div class="basic">
			<div class="info-item clearfix">
				<div>username</div>
				<div> [[User.current_user.username]] </div>
			</div>
			<div class="info-item clearfix">
				<div>intro</div>
				<div> [[User.self_data.intro || '暂无介绍']] </div>
				
			</div>
		</div>
		<div ng-repeat="item in User.self_data">
			
		</div>

		<br/>
		<br/>
		<br/>
		<br/>
		<h2>用户回答</h2>
		<div ng-repeat="item in User.his_answers" >
			<div class="item">
				<div class="title">Question: [[item.question.title]] </div>
				<div class="comment">
				
					<div class="comment-content">
						<div class="comment">A: [[item.content]] </div> 
					</div>
					<div class="time">更新时间：[[item.updated_at]] </div>
				</div>
				<div class="hr"></div>
			</div>
		</div>
		<br/>
		<br/>
		<br/>
		<br/>
		<br/>

		<h2>用户提问</h2>
		<div ng-repeat="item in User.his_questions">
			<div class="question-item-set">
				<div class="item">Q: [[item.title]] </div>
				<div class="time"> 问题编辑时间: [[item.created_at]] </div>
			</div>
			
			<br/>
			<br/>
		</div>
	</div>