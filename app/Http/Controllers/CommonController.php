<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class CommonController extends Controller
{
    // 时间线api
    public function timeline(){
    	list($limit, $skip) = paginate(rq('page'), rq('limit'));

        /*获取问题数据*/
        $questions = question_ins()
            ->with('user') // 这个问题是谁回答的
            ->limit($limit)
            ->skip($skip)
            ->orderBy('created_at', 'desc')
            ->get();

        /*获取回答数据*/
        $answers = answer_ins()
            ->with('question') // 这个答案是属于哪个问题的
            ->with('user') // 这个答案是谁写的
            ->with('users') // 这个答案有哪些人点赞
            ->limit($limit)
            ->skip($skip)
            ->orderBy('created_at', 'desc')
            ->get();

    	/*合并数据*/
    	$data = $questions->merge($answers);

    	/*将合并的数据按时间排序*/
    	$data = $data->sortByDesc(function($item){
    		return $item->created_at;
    	});
    	$data = $data->values()->all();

    	return ['status' => 1, 'data' => $data];
    }
}
