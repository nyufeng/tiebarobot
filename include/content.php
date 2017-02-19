<?php
include('wcurl.php');
class content{
	private $wcurl;

	public function __construct(){
		$this->wcurl = new wcurl();
	}

	public function getTiebaIndexContent($tieba,$bduss){
		$option = [
            'kw' =>   $tieba,
            'fr'   =>   'index',
        ];
        $httpbuild = http_build_query($option);
        $this->wcurl->setUrl('http://tieba.baidu.com/f?' . $httpbuild);
	    $this->wcurl->addcookie('BDUSS='.$bduss);
	    return $this->wcurl->get();
	}

	public function getTiebaIndexList($tieba,$bduss){
		$data = $this->getTiebaIndexContent($tieba,$bduss);
		$preg = "/<li class=\" j_thread_list clearfix\" data-field='(.*?)'.*?<div class=\"threadlist_title pull_left j_th_tit \">.*?<a.*?>(.*?)<\/a>.*?<span class=\"tb_icon_author \".*?target=\"_blank\">(.*?)<\/a>.*?<div class=\"threadlist_abs threadlist_abs_onlyline \">(.*?)<\/div>.*?<i class=\"icon_replyer\">.*?target=\"_blank\">(.*?)<\/a>/s";
		preg_match_all($preg, $data, $info);
		unset($info[0]);
		$array = [];
		foreach($info[1] as $k => $v){
			@$array['list'][] = [
				'info' => json_decode(htmlspecialchars_decode($v),true),
				'title' => $info[2][$k],
				'author' => $info[3][$k],
				'conn'	=> $info[4][$k],
				'lastpost' => $info[5][$k],
			];
		}
		$preg = '/tbs.*?"(.*?)".*?}.*?PageData.forum.*?id.*?:.*?([0-9]*),/s';
		preg_match($preg, $data, $info);
		$array['tbs'] = $info[1];
		$array['fid'] = $info[2];
		return $array;
	}

	public function delTiebaList($tbs,$tid,$fid,$kw,$bduss){
		$option = [
            'commit_fr'  =>  'pb',
            'ie'  =>  'utf-8',
            'tbs'  => $tbs,
            'kw'  =>  $kw,
            'fid'  =>  $fid,
            'tid'  =>  $tid,
        ];
        $this->wcurl->setUrl('http://tieba.baidu.com/f/commit/thread/delete');
        $this->wcurl->addcookie('BDUSS='.$bduss);
        $res = $this->wcurl->post($option);
	}
}
//file-end
