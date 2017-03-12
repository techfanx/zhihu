<!DOCTYPE html>
<html lang="zh" ng-controller="BaseController" ng-app="zhihu" user-id={{session('user_id')}}>
<head>
	<meta charset="utf-8">
	<title>知乎</title>
	<link rel="stylesheet" type="text/css" href="/node_modules/normalize-css/normalize.css">
	<link rel="stylesheet" type="text/css" href="/css/base.css">
	<script src="/node_modules/jquery/dist/jquery.js"></script>
	<script src="/node_modules/angular/angular.js"></script>
	<script src="/node_modules/angular-ui-router/release/angular-ui-router.js"></script>
	<script src="/js/base.js"></script>
	<script src="/js/user.js"></script>
	<script src="/js/question.js"></script>
	<script src="/js/answer.js"></script>
	<script src="/js/common.js"></script>
</head>
<body>
<div class="navbar clearfix">
	<div class="container">
		<div class="fl">
		<div class="navbar-item brand"><a ui-sref="home">知乎</a></div>
		<form ng-submit="Question.go_add_question()" id="quick_ask" ng-controller="QuestionAddController">
				<div class="navbar-item">
					<input type="text" ng-model="Question.new_question.title" >
				</div>
				<div class="navbar-item">
					<button type="submit">提问</button>
				</div>
		</form>
		</div>
		<div class="fr">
			<a ui-sref="home" class="navbar-item">首页</a>
			@if(is_logged_in())
				<!-- 添加self参数 -->
				<a ui-sref="user({id: 'self'})" class="navbar-item">{{session('username')}}</a>
				<a href="{{ url('/api/logout') }}">登出</a>
			@else
				<a ui-sref="login" class="navbar-item">登录</a>
				<a ui-sref="signup" class="navbar-item">注册</a>
			@endif
		</div>
	</div>
</div>

<div class="page">
	<div ui-view>
		
	</div>
</div>
	
</html>