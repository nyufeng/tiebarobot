<?php
require_once('config.inc.php');
require_once('include/content.php');
require_once('include/cache.php');
$content = new content();
$cache = new cache($config['redis_ip'],$config['redis_port']);
$indexlist = $content->getTiebaIndexList($config['tieba'],$config['bduss']);
foreach($indexlist['list'] as $v){
	$status = false;
	if(in_array($v['author'],$rule['author']) || $cache->is_process_first($v['info']['id'])){
		$status = true;
		goto del;
	}
	if(empty($rule['title_must'])){
		$status = true;
	}else{
		foreach($rule['title_must'] as  $havekey) {
	        if(strpos(htmlspecialchars_decode($v['title']),$havekey) !== false){
	            $status = true;
	          	break;
	        }
	    }
	}
    if($status === false){
    	goto del;
    }
    foreach ($rule['content_not_have'] as $havakey) {
    	if(strpos($v['conn'],$havakey) !== false){
            $status = false;
            goto del;
        } 
    }
	del:
	if($status === false){
		$content->delTiebaList($indexlist['tbs'],(string)$v['info']['id'],$indexlist['fid'],$config['tieba'],$config['bduss']);
		echo "title:\t{$v['title']}\t\tauthor:\t{$v['author']}\t\tpost_id:\t{$v['info']['first_post_id']}\t\tconn:\t{$v['conn']}\r\n";
	}else{
		$cache->add_process_first($v['info']['id']);
	}
}
//file-end
