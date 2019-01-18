<?php
mb_language('ja');
mb_internal_encoding("utf-8");

function post_query($query, $url){
	//URLエンコードされたクエリ文字列を生成
	$content = http_build_query($query, '', '&');
	
	$header = array(
		"Content-Type: application/x-www-form-urlencoded",
		"Content-Length: ".strlen($content)
	);
	
	$options =array(
	   'http' =>array(
	      'method' => 'POST',
	      'header' => implode("\r\n", $header),
	      'content' => $content
	   )
	);

	if ( $_SERVER["SERVER_NAME"] === 'localhost') {
		$res = file_get_contents($url, false, stream_context_create($options));
		echo $res;
	}else{
		file_get_contents($url, false, stream_context_create($options));
	}
}

function regist_mailasp($magid, $email, $name="", $sys="autobiz"){
	if ($sys === 'autobiz'){
		$url = 'https://17auto.biz/komish/planmail.php';

		if (!empty($name)){
			//POSTで送りたいデータ
			$query = array(
				'mcode' => 'UTF-8',
				'pid' => $magid, 
				'spflg' => '1',
				'name1' => $name,
				'email' => $email,
				'rgst' => 'entry'
			);
		}else{
			//POSTで送りたいデータ
			$query = array(
				'mcode' => 'UTF-8',
				'pid' => $magid, 
				'spflg' => '1',
				'email' => $email,
				'rgst' => 'entry'
			);
		}
	}
	
	if (!empty($query))
		post_query($query, $url);
}

function cancel_mailasp($email, $magid, $sys="autobiz"){
	if ($sys === 'autobiz'){
		$url = 'https://17auto.biz/komish/mail_cancel.php?cd=04dQauqbPGb3Qi&sbm=cn';

		//POSTで送りたいデータ
		$query = array(
			'mcode' => 'UTF-8',
			'pid' => $magid, 
			'cd' => '04dQauqbPGb3Qi',
			'email' => $email,
		);
	}

	if (!empty($query))
		post_query($query);
}

?>