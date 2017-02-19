<?php
class cache{
	public $redis;

	public function __construct($ip = '127.0.0.1',$port = '6379'){
		$this->redis = new redis();
		$this->redis->connect($ip,$port);
	}

	public function is_process_first($id){
		return $this->redis->sIsMember('tieba_process_first',$id);
	}

	public function add_process_first($id){
		$this->redis->sAdd('tieba_precoss_first',$id);
	}
}
//file-end
