<?php
require_once('config.inc.php');
require_once('include/content.php');
require_once('include/cache.php');
$content = new content();
$cache = new cache($config['redis_ip'],$config['redis_port']);
$indexlist = $content->getTiebaIndexList($config['tieba'],$config['bduss']);
foreach($indexlist['list'] as $v){
    if($cache->is_process($v['info']['id'],$v['lastpost'])){
        continue;
    }
    $status = false;
	if(in_array($v['info']['author_name'],$rule['author']) || $cache->is_process_first($v['info']['id'])) {
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
		echo "title:\t{$v['title']}\t\tauthor:\t{$v['info']['author_name']}\t\tpost_id:\t{$v['info']['first_post_id']}\t\tconn:\t{$v['conn']}\r\n";
	}else{
		$cache->add_process_first($v['info']['id'],$v['info']['first_post_id']);
	}

	//处理贴内回复
	if(!$config['is_post']){
		continue;
	}

    $post_info = $content->getTiebaPostList($v['info']['id']);
    foreach ($post_info[2] as $key => $value) {
        $uinfo = json_decode(htmlspecialchars_decode($post_info[1][$key]),true);
        if($cache->is_process_post($v['info']['id'],$uinfo['content']['post_id'])){
            continue;
        }
        foreach ($rule['content_not_have'] as $havakey) {
            if(strpos($value,$havakey) !== false){
                $content->delTiebaPost($indexlist['tbs'],(string)$v['info']['id'],$indexlist['fid'],$uinfo['content']['post_id'],$config['tieba'],$config['bduss']);
                echo $uinfo['author']['user_name'] . '    ' . $value . "\r\n";
                break;
            }
        }
        $cache->set_tieba_info($v['info']['id'],$uinfo['author']['user_name']);
        $cache->add_process_first($v['info']['id'],$uinfo['content']['post_id']);
    }
    if(count($post_info[1]) == 30){
        $cache->add_process($v['info']['id']);
    }
}
//file-end
