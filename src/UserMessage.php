<?php
namespace yxtools;

header('content-type:text/html;charset=utf-8');

/**
 * 用户类
 * Class UserMessage
 */

class UserMessage{
    /**
     * @desc： 用户的信息
     * @return array
     */
    public function userMessageGet($us_id){
		$data=\think\Db::name('user')->join('user_detail','us_id=us_usid','left')->join('user_level','le_id=us_level','left')->where('us_id',$us_id)->find();
		unset($data['us_password']);
		unset($data['us_salt']);
		unset($data['us_answer1']);
		unset($data['us_answer2']);
		unset($data['us_answer3']);
		unset($data['us_protect_password']);
		unset($data['us_protect_salt']);
		$data_x['yuan']=$data;
		$data_x['zhuan']=$data;
		$data_x['zhuan']['us_name']=urlencode($data['us_name']);
		$data_x['zhuan']['us_level']=$data['le_name'];
		$data_x['zhuan']['us_nickname']=urlencode($data['us_nickname']);
		$data_x['zhuan']['us_phone_hide']=hide_phone($data['us_phone']);
		$data_x['zhuan']['us_email_hide']=hide_phone($data['us_email']);
		$data_x['zhuan']['us_logintime']=!empty($data['us_logintime'])?date('Y-m-d H:i:s',$data['us_logintime']):'暂无';
		$data_x['zhuan']['us_login_limit_end_time']=!empty($data['us_login_limit_end_time'])?date('Y-m-d',$data['us_login_limit_end_time']):'暂无';
		$data_x['zhuan']['us_publish_limit_end_time']=!empty($data['us_publish_limit_end_time'])?date('Y-m-d',$data['us_publish_limit_end_time']):'暂无';
		$data_x['zhuan']['us_register_time']=!empty($data['us_register_time'])?date('Y-m-d H:i:s',$data['us_register_time']):'暂无';
		return $data_x;
    }
    /**
     * @desc： 用户的密保
     * @param int $us_id
     * @return array
     */
    public function userSecretQuestionGet($us_id){
		$us_questions=\think\Db::name('user_detail')->field('us_question1,us_question2,us_question3')->where('us_usid',$us_id)->find();
		return \think\Db::name('user_secret')->field('mb_id,mb_con')->where(['mb_id'=>['in',$us_questions]])->select();
    }
    /**
     * @desc： 用户的密保验证
     * @param array $data 以问题id为键，以答案为值的数组
     * @param int $us_id 用户id
     * @return boolean
     */
    public function userSecretCheck($data,$us_id){
		$us_questions=\think\Db::name('user_detail')->field('us_question1,us_question2,us_question3,us_answer1,us_answer2,us_answer3')->where('us_usid',$us_id)->find();
		$check_data=[$us_questions['us_question1']=>$us_questions['us_answer1'],$us_questions['us_question2']=>$us_questions['us_answer2'],$us_questions['us_question3']=>$us_questions['us_answer3']];
		foreach($data as $k=>$v){
			if($v!=$check_data[$k]){
				return false;
			}
		}
		return true;
    }
	/**
	 * @desc： 用户的操作日志	
	 * @param int $us_id 用户id
	 * @param boolean $isPage 是否分页
	 * @param boolean $isAdmin 是否后台使用
	 * @return array/object
	 */
	public function userLogGet($us_id,$isPage=true,$isAdmin=true,$where=[],$rows=20,$page=1){
		$input=input('param.');
		if(!empty($input['log_controller'])){
			$where['log_controller']=$input['log_controller'];
		}
		if(!empty($input['log_action'])){
			$where['log_action']=$input['log_action'];
		}
		if(!empty($input['log_time_limit'])){
			$time=explode(' ~ ',$input['log_time_limit']);
			$where['log_time']=['BETWEEN',[strtotime($time[0]),strtotime($time[1])]];
		}
		if(!empty($input['page']) && $input['page']>0){
			$page=$input['page'];
		}
		if($isAdmin){
			if($isPage){
				$data=\think\Db::name('log')->where($where)->order('log_id')->paginate($rows,false,['query' => request()->except('page')]);
			}else{
				$data=\think\Db::name('log')->where($where)->order('log_id')->select();
			}
		}else{
			if($isPage){
				$data['data']=\think\Db::name('log')->where($where)->page($page,$rows)->order('log_id')->select();
				$data['pages']=ceil(\think\Db::name('log')->where($where)->page($page,$rows)->order('log_id')->count()/$rows);
			}else{
				$data=\think\Db::name('log')->where($where)->order('log_id')->select();
			}
		}
		return $data;
	}
	/**
	 * @desc： 用户的通知消息	
	 * @param int $us_id 用户id
	 * @param boolean $isPage 是否分页
	 * @param boolean $isAdmin 是否后台使用
	 * @return array/object
	 */
	public function userNoticeGet($us_id,$isPage=true,$isAdmin=true,$where=[],$rows=20,$page=1){
		$input=input('param.');
		if(!empty($input['notice_creattime_limit'])){
			$creattime=explode(' ~ ',$input['notice_creattime_limit']);
			$where['notice_creattime']=['BETWEEN',[strtotime($creattime[0]),strtotime($creattime[1])]];
		}
		if(!empty($input['notice_readtime_limit'])){
			$readtime=explode(' ~ ',$input['notice_readtime_limit']);
			$where['notice_readtime']=['BETWEEN',[strtotime($readtime[0]),strtotime($readtime[1])]];
		}
		if(!empty($input['notice_deltime_limit'])){
			$deltime=explode(' ~ ',$input['notice_deltime_limit']);
			$where['notice_deltime']=['BETWEEN',[strtotime($deltime[0]),strtotime($deltime[1])]];
		}
		if(!empty($input['notice_state'])){
			$where['notice_state']=$input['notice_state'];
		}
		if(!empty($input['notice_del'])){
			$where['notice_del']=$input['notice_del'];
		}else{
			$where['notice_del']=1;
		}
		if(!empty($input['notice_istop'])){
			$where['notice_istop']=$input['notice_istop'];
		}
		if(!empty($input['page']) && $input['page']>0){
			$page=$input['page'];
		}
		if($isPage){
			if($isAdmin){
				$data=\think\Db::name('user_notice')->where($where)->order('notice_state asc,notice_istop desc,notice_creattime desc')->paginate($rows,false,['query' => request()->except('page')]);
			}else{
				$data=\think\Db::name('user_notice')->where($where)->page($page,$rows)->order('notice_state asc,notice_istop desc,notice_creattime desc')->select();
			}
		}else{
			$data=\think\Db::name('user_notice')->where($where)->order('notice_state asc,notice_istop desc,notice_creattime desc')->select();
		}
		return $data;
	}
	/**
	 * @desc： 用户的积分记录	
	 * @param int $us_id 用户id
	 * @param boolean $isPage 是否分页
	 * @param boolean $isAdmin 是否后台使用
	 * @return array/object
	 */
	public function userJifenLogGet($us_id,$isPage=true,$isAdmin=true,$where=[],$rows=20,$page=1){
		$input=input('param.');
		if(!empty($input['jl_time_limit'])){
			$time=explode(' ~ ',$input['jl_time_limit']);
			$where['jl_time']=['BETWEEN',[strtotime($time[0]),strtotime($time[1])]];
		}
		if(!empty($input['jl_type'])){
			$where['jl_type']=$input['jl_type'];
		}
		if(!empty($input['page']) && $input['page']>0){
			$page=$input['page'];
		}
		if($isPage){
			if($isAdmin){
				$data=\think\Db::name('user_jifenjilu')->where($where)->order('jl_time desc')->paginate($rows,false,['query' => request()->except('page')]);
			}else{
				$data=\think\Db::name('user_jifenjilu')->where($where)->page($page,$rows)->order('jl_time desc')->select();
			}
		}else{
			$data=\think\Db::name('user_jifenjilu')->where($where)->order('jl_time desc')->select();
		}
		return $data;
	}
	/**
	 * @desc： 用户的交易记录	
	 * @param int $us_id 用户id
	 * @param boolean $isPage 是否分页
	 * @param boolean $isAdmin 是否后台使用
	 * @return array/object
	 */
	public function userTradeLogGet($us_id,$isPage=true,$isAdmin=true,$where=[],$rows=20,$page=1){
		$input=input('param.');
		if(!empty($input['trade_time_limit'])){
			$time=explode(' ~ ',$input['trade_time_limit']);
			$where['trade_time']=['BETWEEN',[strtotime($time[0]),strtotime($time[1])]];
		}
		if(!empty($input['trade_money_limit'])){
			$money=explode(' ~ ',$input['trade_money_limit']);
			$where['trade_money']=['BETWEEN',[$money[0],$money[1]]];
		}
		if(!empty($input['trade_way'])){
			$where['trade_way']=$input['trade_way'];
		}
		if(!empty($input['trade_type'])){
			$where['trade_type']=$input['trade_type'];
		}
		if(!empty($input['trade_del'])){
			$where['trade_del']=$input['trade_del'];
		}
		if(!empty($input['page']) && $input['page']>0){
			$page=$input['page'];
		}
		if($isPage){
			if($isAdmin){
				$data=\think\Db::name('user_trade_log')->alias('a')->join('shop b','trade_shop_id=shop_id','left')->field('a.*,b.shop_name as trade_shop')->where($where)->order('trade_time desc')->paginate($rows,false,['query' => request()->except('page')]);
			}else{
				$data['data']=\think\Db::name('user_trade_log')->alias('a')->join('shop b','trade_shop_id=shop_id','left')->field('a.*,b.shop_name as trade_shop')->where($where)->page($page,$rows)->order('trade_time desc')->select();
				$data['pages']=ceil(\think\Db::name('user_trade_log')->where($where)->order('trade_time desc')->count()/$rows);
			}
		}else{
			$data=\think\Db::name('user_trade_log')->alias('a')->join('shop b','trade_shop_id=shop_id','left')->field('a.*,b.shop_name as trade_shop')->where($where)->order('trade_time desc')->select();
		}
		return $data;
	}
	/**
	 * @desc： 用户的登录能力	
	 * @param int $us_id 用户id
	 * @return boolean
	 */
	public function userLoginAble($us_id){
		$us_message=\think\Db::name('user')->where('us_id',$us_id)->find();
		if($us_message['us_login_able']==1){
			return true;
		}else{
			if($us_message['us_login_limit_end_time']<=time()){
				\think\Db::name('user')->where('us_id',$us_id)->update(['us_login_able'=>1]);
				return true;
			}
			return false;
		}
	}
	/**
	 * @desc： 用户的发布能力	
	 * @param int $us_id 用户id
	 * @return boolean
	 */
	public function userPublishAble($us_id){
		$us_message=\think\Db::name('user')->where('us_id',$us_id)->find();
		if($us_message['us_publish_able']==1){
			return true;
		}else{
			if($us_message['us_publish_limit_end_time']<=time()){
				\think\Db::name('user')->where('us_id',$us_id)->update(['us_publish_able'=>1]);
				return true;
			}
			return false;
		}
	}
	/**
	 * @desc： 用户的是否为商户	
	 * @param int $us_id 用户id
	 * @return boolean
	 */
	public function userIsShop($us_id){
		$us_is_shop=\think\Db::name('user')->where('us_id',$us_id)->value('us_is_shop');
		if($us_is_shop==1){
			return true;
		}
		return false;
	}
	/**
	 * @desc： 用户的所有相关时间
	 * @param int $us_id 用户id
	 * @return boolean
	 */
	public function userTimes($us_id){
		$data=[];
		$user=\think\Db::name('user')->where('us_id',$us_id)->find();
		$user_details=\think\Db::name('user_detail')->where('us_usid',$us_id)->find();
		$data['time']['us_logintime']=!empty($user['us_logintime'])?$user['us_logintime']:0;
		$data['time']['us_login_limit_end_time']=!empty($user['us_login_limit_end_time'])?$user['us_login_limit_end_time']:0;
		$data['time']['us_publish_limit_end_time']=!empty($user['us_publish_limit_end_time'])?$user['us_publish_limit_end_time']:0;
		$data['time']['us_register_time']=!empty($user['us_register_time'])?$user['us_register_time']:0;
		foreach($data['time'] as $k=>$v){
			if(!empty($v)){
				$data['time_text'][$k]=date('Y-m-d H:i:s',$v);
			}else{
				$data['time_text'][$k]='暂无';
			}
		}
		return $data;
	}
}
