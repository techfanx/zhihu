<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /*添加评论api*/
    public function add(){
    	/*检查用户是否登录*/
    	if(!user_ins()->is_logged_in())
    		return ['status' => 0, 'msg' => 'login required'];

    	/*检查是否有content参数*/
    	if(!rq('content'))
    		return ['status' => 0, 'msg' => 'empty content'];

    	/*检查是否存在question_id或者answer_id*/
    	if(
    		(!rq('question_id') && !rq('answer_id')) || //none
    		(rq('question_id') && rq('answer_id')) //all

    	)
    		return ['status' => 0, 'msg' => 'question_id or answer_id is required'];

    	if(rq('question_id')){
    		/*评论问题*/
    		$question = question_ins()->find(rq('question_id'));
    		/*检查问题是否存在*/
    		if(!$question)
    			return ['status' => 0, 'msg' => 'question not exists'];
    		$this->question_id = rq('question_id');
    	}else{
    		/*检查答案是否存在*/
    		$answer = answer_ins()->find(rq('answer_id'));
    		if(!$answer)
    			return ['status' => 0, 'msg' => 'answer not exists'];
    		$this->answer_id = rq('answer_id');
    	}

    	/*检查是否在回复评论*/
    	if(rq('reply_to')){
    		$target = $this->where('reply_to', rq('reply_to'))->first();
    		/*检查目标评论是否存在*/
    		if(!$target)
    			return ['status' => 0, 'msg' => 'target comment not exists'];
    		/*检查是否在回复自己的评论*/
    		if($target->user_id == session('user_id'))
    			return ['status' => 0, 'msg' => 'you can not reply to yourself'];
    		$this->reply_to = rq('reply_to');
    	}

    	/*保存数据*/
    	$this->content = rq('content');
    	$this->user_id = session('user_id');
    	return $this->save() ? 
    		['status' => 1, 'id' => $this->id] :
    		['status' => 0, 'msg' => 'db insert failed'];

    }

    /*查看评论api*/
    public function read(){

    	if(!rq('question_id') && !rq('answer_id'))
    		return ['status' => 0, 'msg' => 'question_id or answer_id are requird'];

    	if(rq('question_id')){
    		$question = question_ins()
                -with('user')->find(rq('question_id'));
    		if(!$question)
    			return ['status' => 0, 'msg' => 'question not exists'];
    		$data = $this->where('question_id', rq('question_id'))->get();
    	}else{
            /*主键查找*/
    		$answer = answer_ins()
                ->find(rq('answer_id'));
    		if(!$answer)
    			return ['status' => 0, 'msg' => 'answer not exists'];
    		$data = $this->with('user')->where('answer_id', rq('answer_id'))->get();
    	}
    	return ['status' => 1, 'data' => $data->keyBy('id')];
    }

    /*删除评论api*/
    public function remove(){
    	if(!user_ins()->is_logged_in())
    		return ['status' => 0, 'msg' => 'login require'];

    	if(!rq('id'))
    		return ['status' => 0, 'msg' => 'id is required'];

    	$comment = $this->find(rq('id'));
    	if(!$comment)
    		return ['status' => 0, 'msg' => 'comment not exists'];

    	if($comment->user_id != session('user_id'))
    		return ['status' => 0, 'msg' => 'permission denied'];

    	/*先删除此评论下所有的回复*/
    	$this->where('reply_to', rq('id'))->delete();
    	return $comment->delete() ? 
    		['status' => 1] :
    		['status' => 0, 'msg' => 'db delete failed'];
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
}
