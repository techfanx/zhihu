<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    /*添加回答api*/
    public function add(){
    	/*检查用户是否登录*/
    	if(!user_ins()->is_logged_in())
    		return err(['login required']);

    	/*检查参数中是否存在question_id和content*/
    	if(!rq('question_id') || !rq('content'))
    		return err(['question_id and content required']);

    	/*检查问题是否存在*/
    	$question = question_ins()->find(rq('question_id'));
    	if(!$question)
    		return err(['question not exists']);

    	/*检查问题是否重复回答*/
    	$answer = $this->where(['question_id' => rq('question_id'), 'user_id' => session('user_id')])->count();
    	if($answer)
    		return err(['duplicated answers']);
    	
    	/*保存数据*/
    	$this->content = rq('content');
    	$this->question_id = rq('question_id');
    	$this->user_id = session('user_id');

    	return $this->save() ?
    		suc(['id' => $this->id]) :
    		err(['db insert failed']);
    }

    /*回答更改api*/
    public function change(){
    	
    	/*校验登录信息*/
    	if (!user_ins()->is_logged_in())
    		return err(['login require']);

    	/*检查id和content*/
    	if (!rq('id') || !rq('content'))
    		return err(['id and content are required']);

    	/*获取指定id Model*/
    	$answer = $this->find(rq('id'));
    	if(!$answer)
    		return err(['answer not exists']);
    	
    	/*检查权限*/
    	if($answer->user_id != session('user_id'))
    		return err(['perssion denied']);
    	/*写入内容并保存*/
    	$answer->content = rq('content');
    	$answer->save();
    	return $answer->save() ? suc(['id' => $answer->id]) : err(['update failed']);
    }

    public function remove(){
        if(!user_ins()->is_logged_in()){
            return err('login required');
        }
        $id = rq('id');
        if(!$id){
            return err('id is required');
        }
        $answer = $this->find($id);
        if(!$answer){
            return err('answer is not exists');
        }
        $answer->delete();
        return suc();
    }

    /*查看回答api*/
    public function read(){
    	/*校验传参是否有包含id与question_id*/
    	if(!rq('id') && !rq('question_id') && !rq('user_id'))
    		return err(['id,question_id or user_id is required']);
    	
    	/*获取传参id的Model*/

        if(rq('user_id')){
            $user_id = rq('user_id') === 'self' ?
                session('user_id') :
                rq('user_id');
            return $this->read_by_user_id($user_id); // laravel自动将其转成json格式
        }

    	/**
         * 此处如果少了with question，则时间线上点击vote按钮，会是问题标题消失
         * 究其原因是数据结构中少了question
         */
        /*查询单个的answer*/
        if(rq('id')){
	    	$answer = $this
                ->with('question')
                ->with('user')
                ->with('users')
                ->find(rq('id'));
	    	if(!$answer)
	    		return err(['answer not exists']);

            $answer = $this->count_vote($answer);

	    	return ['status' => 1, 'data' => $answer];
	    	
	    }

	    if(!question_ins()->find(rq('question_id')))
	    	return err(['question not exists']);
    	/*查看同一问题下的所有答案*/
    	$answer = $this
    		->where('question_id', rq('question_id'))
    		->get()/*
    		->keyBy('id')*/;
    	return ['status' => 1, 'data' => $answer];
    }

    public function count_vote($answer){
        $upvote_count = 0;
        $downvote_count = 0;

        foreach($answer->users as $user){
            if($user->pivot->vote == 1)
                $upvote_count++;
            else
                $downvote_count++;
        }
        $answer->upvote_count = $upvote_count;
        $answer->downvote_count = $downvote_count;
        return $answer;
    }

    public function read_by_user_id($user_id){
        $user = user_ins()->find($user_id);
        if(!$user)
            return err('user not exists');

        $answer = $this
            ->with('question')
            ->where('user_id', $user_id)
            ->get()
            ->keyBy('id');
        return suc($answer);
    }
    // 一个答案可以是多个用户点赞，因此是一对多的关系
    public function users(){ // 这里的多对多关系是正确的，只是原始数据缺乏，所以返回json数据时，users->[]
        return $this
            ->belongsToMany('App\User')
            ->withPivot('vote')
            ->withTimestamps(); 
    } 

    public function user(){
        return $this
            ->belongsTo('App\User');
    }

    public function vote(){
        if(!user_ins()->is_logged_in())
            return err(['login required']);

        if(!rq('id') || !rq('vote'))
            return err(['id and vote are required']);
        /*检查此用户是否相同问题下投过票*/

        $answer = $this->find(rq('id'));
        if(!$answer)
            return err(['answer not exists']);

        /*1为赞同票，2为反对票, 3为清空*/
        $vote = rq('vote');
        if($vote != 1 && $vote != 2 && $vote != 3)
            return ['status' => 0, 'msg' => 'invalid vote'];

        /*检查此用户是否在相同问题下投过票，如果投过票，清空删除投票结果*/
        $voted = $answer
            ->users()
            ->newPivotStatement()
            ->where('user_id', session('user_id'))
            ->where('answer_id', rq('id'))
            ->delete();

        if($vote == 3)
            return ['status' => 1];

        /*在连接表中增加数据*/
        /*在多对多关系中创建的连接表*/
        $answer->users()->attach(session('user_id'), ['vote' => $vote]);
        return suc(['投票成功']);
    }

    public function question(){
        return $this->belongsTo('App\Question');
    }
}
