<?php require_once('plugins/querylist.class.php');?>
<?php
header("Content-type:text/html;charset=utf-8");
#设置执行时间不限时 
set_time_limit(0);
#清除并关闭缓冲，输出到浏览器之前使用这个函数。
ob_end_clean();
#控制隐式缓冲泻出，默认off，打开时，对每个 print/echo 或者输出命令的结果都发送到浏览器。
ob_implicit_flush(1);

$bi=false;
$bj=false;
$parentPath = iconv("UTF-8", "GBK", "Cache");
if(file_exists($parentPath.'/i')){
	$bi = file_get_contents($parentPath.'/i');	
}

if(file_exists($parentPath.'/j')){
	$bj = file_get_contents($parentPath.'/j');
}

$isInitI = false;
$isInitJ = false;

echo "Start<br>";
for ($i=1; $i < 104; $i++) { 
	if($bi && !$isInitI){
		$i = $bi;
		$isInitI = true;
	}
	echo $i.'<br>';
	if($i==1){
		$url = 'http://www.mmjpg.com/';	
	}
	else{
		$url = 'http://www.mmjpg.com/home/'.$i;
	}
	$reg = array(
				'title' =>	array('.title a','text'),
				'img' =>	array('a img','src'),
				'href' =>	array('a','href'));
	$rang = '.pic ul li';
	$arr = query_web_contents($url,$reg,$rang);
	$arrCount = count($arr,0);
	for ($j=0; $j < $arrCount; $j++) { 

		if($bj && !$isInitJ){
			$j = $bj;
			$isInitJ = true;
		}

		echo "$j/$arrCount/$i/104<br>";
		$title = $arr[$j]['title'];
		$mUrl = $arr[$j]['href'];

		//TODO:创建文件夹
		$dir = iconv("UTF-8", "GBK", "Images/".$title);
        if (!file_exists($dir)){
            mkdir ($dir,0777,true);
            echo $title.'<br>';
        } else {
            echo $title.'<br>';
        }

		//详细页面页码
		$url = $arr[$j]['href'];
		$reg = array(
					'page' =>	array('','text'));
		$rang = '#page a';
		$detailArr = query_web_contents($url,$reg,$rang);
		$dCount = count($detailArr);
		$pageCount = $detailArr[$dCount-2]['page'];
		
		for ($k=1; $k < $pageCount+1; $k++) { 
			//详细页面图片
			$url =$mUrl.'/'.$k;
			$reg = array(
						'img' =>	array('img','src'));
			$rang = '#content a';
			$imgArr = query_web_contents($url,$reg,$rang);
			$img = $imgArr[0]['img'];
			echo $img.'<br>';
			$arrFileName = explode('/',$img); 
			$filename= $arrFileName[count($arrFileName)-1]; 

			if(!file_exists($dir.'/'.$filename)){
				file_put_contents($dir.'/'.$filename,curl_file_get_contents($img));
				echo '<----------<br>';
			}
		}

		file_put_contents($parentPath.'/j',$j+1);
	}

	file_put_contents($parentPath.'/i',$i+1);
}

echo "End<br>";

function query_web_contents($url,$reg,$rang){
	echo $url.'<br>';
	$qy = new QueryList($url,$reg,$rang,'curl','utf-8');
	return $qy->jsonArr;
}

//获取远程图片
function curl_file_get_contents($durl){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $durl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$headers = array();
	$headers[] = 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
	$headers[] = 'Accept-Encoding:gzip, deflate';
	$headers[] = 'Accept-Language:zh-CN,zh;q=0.9';
	$headers[] = 'Cache-Control:max-age=0';
	$headers[] = 'Connection:keep-alive';
	$headers[] = 'Cookie:__guid=182899121.4565135689721234000.1542012867087.895; monitor_count=19';
	$headers[] = 'DNT:1';
	$headers[] = 'Host:fm.shiyunjj.com';
	$headers[] = 'If-Modified-Since:Mon, 05 Nov 2018 01:10:13 GMT';
	$headers[] = 'If-None-Match:"5bdf9875-2979e"';
	$headers[] = 'Referer:http://www.mmjpg.com/mm/1531/3';
	$headers[] = 'Upgrade-Insecure-Requests:1';
	$headers[] = 'User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36';

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$r = curl_exec($ch);
	curl_close($ch);

	return $r;
 }