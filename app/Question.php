<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /*创建问题api*/
    public function add(){
    	if(!user_ins()->is_logged_in())
    		return ['status' => 0, 'msg' => '尚未登录'];
    	if(!rq('title'))
    		return ['status' => 0, 'msg' => 'required title'];
    	$this->title = rq('title');
    	$this->user_id = session('user_id');
    	if(rq('desc'))
    		$this->desc = rq('desc');
    	return $this->save() ? 
	    	['status' => 1, 'id' => $this->id] :
	    	['status' => 0, 'msg' => 'db inset failed'];
    }

    /*更新问题api*/
    public function change(){
    	if(!user_ins()->is_logged_in())
    		return ['status' => 0, 'msg' => 'login required'];

    	if(!rq('id'))
    		return ['status' => 0, 'msg' => 'id is required'];

    	/*获取指定id的model*/
    	$question = $this->find(rq('id'));

    	if(!$question)
    		return ['status' => 0, 'msg' => '问题不存在'];
    	if($question->user_id != session('user_id'))
    		return ['status' => 0, 'msg' => 'perssion denied'];
    	if(rq('title'))
    		$question->title = rq('title');

    	if(rq('desc'))
    		$question->desc = rq('desc');

    	/*保存数据*/
    	return $question->save() ?
    		['status' => 1, 'msg' => '更新成功'] :
    		['status' => 0, 'msg' => 'db update failed'];
    }

    public function read_by_user_id($user_id){
        /*查看用户是否存在*/
        $user = user_ins()->find($user_id);
        if(!$user)
            return err('user not exists');
        $questions = $this->where('user_id', $user_id)->get()->keyBy('id');

        return ['status' => 1, 'data' => $questions];
    }
    
    /*查看问题api*/
    public function read(){
    	if(rq('id'))
    		return ['status' => 1, 'data' => $this->with('answers_with_user_info')->find(rq('id'))];

        if(rq('user_id')){
            $user_id = (rq('user_id') == 'self') ?
                session('user_id') :
                rq('user_id');

            return $this->read_by_user_id($user_id);
        }

    	/*limit条件*/
    	$limit = rq('limit') ?: 15;
    	/*构建query并返回collection对象结果*/
    	// $skip = ((rq('page') ?: 1)-1) * $limit;
        list($limit, $skip) = paginate(rq('page'), rq('limit'));

    	// 获取查询结果
    	$result = $this
    		->orderBy('created_at')
    		->limit($limit)
    		->skip($skip)
    		->get()
    		->keyBy('id');
    	return ['status' => 1, 'data' => $result];
    }

    /*删除问题api*/
    public function remove(){
    	/*检查用户是否登录*/
    	if(!user_ins()->is_logged_in())
    		return ['status' => 0, 'msg' => 'login required'];

    	/*检查传参中是否有id*/
    	if(!rq('id'))
    		return ['status' => 0, 'msg' => 'id is required'];

    	/*获取传参id所对应的model*/
    	$question = $this->find(rq('id'));
    	if(!$question)
    		return ['status' => 0, 'question not found'];

    	/*检查当前用户是否为问题的所有者*/
    	if(session('user_id') != $question->user_id)
    		return ['status' => 0, 'permission denied'];
    	return $question->delete() ?
    		['status' => 1] :
    		['status' => 0, 'msg' => 'db delete failed'];
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function answers(){
        return $this->hasMany('App\Answer');
    }

    public function answers_with_user_info(){
        return $this->answers()->with('user')->with('users');
    }
}
