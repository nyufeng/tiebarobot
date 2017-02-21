<?php
class cache{
	public $redis;

	public function __construct($ip = '127.0.0.1',$port = '6379'){
		$this->redis = new redis();
		$this->redis->connect($ip,$port);
	}

	public function is_process($id,$last_post){
		if($this->redis->sIsMember('tieba_process',$id)){
			return true;
		}
		$info = $this->redis->get('tieba_info:' . $id);
		if($info && $info == $last_post){
			return true;
		}
		return false;
	}

	public function is_process_first($id){
		return $this->redis->exists('tieba_precoss_first:'.$id);
	}

	public function is_process_post($id,$post_id){
		return $this->redis->sIsMember('tieba_precoss_first:'.$id,$post_id);
	}

	public function add_process_first($id,$post_id){
		$this->redis->sAdd('tieba_precoss_first:'.$id,$post_id);
	}

	public function set_tieba_info($id,$uname){
		$this->redis->set('tieba_info:'.$id,$uname);
	}

	public function add_process($id){
		$this->redis->sAdd('tieba_process',$id);
		$this->redis->delete('tieba_precoss_first:'.$id);
		$this->redis->delete('tieba_info:'.$id);
	}
}
//file-end
