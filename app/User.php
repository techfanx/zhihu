<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Request;
use Hash;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    /*注册api*/
    public function signup(){
        $has_username_and_password = $this->has_username_and_password();
        if(!$has_username_and_password)
            return err('用户名和密码皆不可为空');
        $username = $has_username_and_password[0];
        $password = $has_username_and_password[1];
        /*检查用户名是否存在*/
        $user_exists = $this->where('username', $username)->exists();
        if($user_exists)
            return err('用户存在');
        /*加密码*/
        $hashed_password = bcrypt($password);
        /*存入数据库*/
        $user = $this;
        $user->password = $hashed_password;
        $user->username = $username;

        if(!$user->save())
            return err('db insert failed');
        else
            return suc(['user_id' => $user->id]);
    }

    /*读取用户信息api*/
    public function read(){
        $id = rq('id');
        if(!$id)
            return err('id is required');

        if($id === 'self'){
            if(!session('user_id')){
                return err('login required');
            }
            $id = session('user_id');
        }

        $gets = ['id', 'username', 'avator_url', 'intro'];
        $user = $this->find($id, $gets);
        $data = $user->toArray();
        $answer_count = answer_ins()->where('user_id', $id)->count();
        $question_count = question_ins()->where('question', $id);
        $data['answer_count'] = $answer_count;
        $data['question_count'] = $question_count;

        return suc($data);
        // $answer_count = $user->answers()->count();
        // $question_count = $user->questions()->count();
    }

    /*登录api*/
    public function login(){
        $has_username_and_password = $this->has_username_and_password();
        if(!$has_username_and_password)
            return err('用户名和密码皆不可为空');
        $username = $has_username_and_password[0];
        $password = $has_username_and_password[1];

        $user = $this->where('username', $username)->first();
        if(!$user)
            return err('用户不存在');
        
        /*检查密码是否正确*/
        $hashed_password = $user->password;
        if(!Hash::check($password, $hashed_password))
            return err('密码有误');
        
        /*将用户信息写入session*/
        session()->put('username', $user->username);
        session()->put('user_id', $user->id);
        return suc(['user_id' => $user->id]);
    }

    /*判断用户是否登录*/
    public function is_logged_in(){
        /*如果session中存在user_id，就返回user_id，否则返回false*/
        return is_logged_in();
    }

    /*登出api*/
    public function logout(){
        session()->forget('username');
        session()->forget('user_id');
        return redirect('/');
        return ['status' => 1];
    }

    public function has_username_and_password(){
        $username = rq('username');
        $password = rq('password');
        /*检查用户名和密码是否为空*/
        if($username && $password)
            return [$username, $password];
        else
            return false;
    }

    /*修改密码api*/
    public function change_password(){
        if(!$this->is_logged_in())
            return err('login require');

        if(!rq('old_password') || !rq('new_password'))
            return err('old_password new_password)are required');

        $user = $this->find(session('user_id'));

        if(!Hash::check(rq('old_password'), $user->password))
            return err('invalid old_password');

        $user->password = bcrypt(rq('new_password'));
        return $user->save() ? 
            suc() :
            err('db update failed');
    }

    /*重置密码*/
    public function reset_password(){
        /*检查是否是机器人*/
        if($this->is_robot())
            return err('max frequency reached');
        if(!rq('phone'))
            return err('phone is required');
        /*获取电话号码对应的model*/
        $user = $this->where('phone', rq('phone'))->first();
        
        /*检查用户是否存在*/
        if(!$user)
            return err('invalid phone number');

        /*生成验证码*/
        $captcha = $this->generate_captcha();
        
        $user->phone_captcha = $captcha;
        if ($user->save()) {
            
            /*如果验证码保存成功，发送验证码短信*/
            $this->send_sms();

            /*记录发送时间（为下次调用做准备）*/
            $this->update_robot_time();
            session('last_action_time', time());
            return suc();
        }else{
            return err('db insert failed');
        }
    }

    public function validate_reset_password(){

        if(is_robot(2))
            return err('max frequency reached');

        if(!rq('phone_captcha') || !rq('phone_captcha') || !rq('new_password'))
            return err('phone, new_password and phone_captcha are required');

        $user = $this->where([
            'phone' => rq('phone'),
            'phone_captcha' => rq('phone_captcha')
        ])->first();

        if(!$user)
            return err('invalid phone or invalid captcha');

        /*加密新密码*/
        $user->password = bcrypt(rq('new_password'));
        $this->update_robot_time();
        return $user->save() ?
            suc() :
            err('db update failed');
    }

    /*以时间来验证是否是机器人*/
    public function is_robot($time = 10){
        /*如果session中没有last_sms_time说明接口从未被调用过*/
        if(!session('last_action_time'))
            return false;
        $current_time = time();
        $last_action_time = session('last_action_time');

        return ($current_time - $last_action_time < $time);
    }

    /*更新机器人行为时间*/
    public function update_robot_time(){
        session()->set('last_action_time', time());
    }

    /*发送短信*/
    public function send_sms(){
        return true;
    }

    /*生成验证码*/
    public function generate_captcha(){
        return rand(1000, 9999);
    }

    public function questions(){
        return $this
            ->belongsToMany('App\Question')
            ->withPivot('vote')
            ->withTimestamps();
    }

    public function answers(){
        return $this
            ->belongsToMany('App\Answer')
            ->withPivot('vote')
            ->withTimestamps();
    }

    public function exist(){
        return suc(['count' => $this->where(rq())->count()]);
    }
}