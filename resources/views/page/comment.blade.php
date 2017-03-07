<div ng-controller="BaseController" class="comment-block">
    <div class="comment-item-set">
        <div ng-if="!helper.obj_length(data)" class="comment-item gray clearfix">暂无评论</div>
        <div ng-if="helper.obj_length(data)" ng-repeat="item in data" class="comment-item clearfix">
            <div class="user">[[item.user.username]]：</div>
            <div class="comment-content">
                [[item.content]]
            </div>
        </div>
    </div>
    <div class="input_group">
        <form ng-submit="_.add_comment()" class="comment-form clearfix">
            <input type="text" placeholder="说些什么..." ng-model="Answer.new_comment.content">
            <button type="submit">提交</button>
        </form>
    </div>
</div>