<?php
# just download the NeteaseMusicAPI.php into directory, require it with the correct path.
# in weapi, you should also put BigInteger.php into same directory, but don't require it.
require_once 'NeteaseMusicAPI_mini.php';

echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
# Initialize
$api = new NeteaseMusicAPI();

$sId=$_POST["sid"];

//获取歌曲信息
$result = $api->detail($sId);
# return JSON, just use it
$data=json_decode($result);

//直链
$sDirectLink="http://music.163.com/song/media/outer/url?id=".$sId.".mp3";
//网易云页面
$sPage="https://music.163.com/#/song?id=".$sId;
//歌曲名
$sTitle=$data->songs[0]->name;
//歌手
$sMainArtist=$data->songs[0]->ar[0]->name;
//Tag
$sTag=$sMainArtist;
//歌手数量
$sArtistCnt=count($data->songs[0]->ar);
//专辑Id
$sAlbumId=$data->songs[0]->al->id;
//获取专辑信息（专辑图片）
$albumresult = $api->album($sAlbumId);
$albumdata=json_decode($albumresult);
//获取歌词信息
$lyricresult = $api->lyric($sId);
$lyricdata=json_decode($lyricresult);

//如果有Feat歌手：修改歌曲名加上Feat;修改Tag加上Feat歌手
if ($sArtistCnt>1) {
	$sTitle=$sTitle." (Ft.";
	for ($curI=0;$curI<$sArtistCnt-1;$curI++) {
		$sFeatArtist[$curI]=$data->songs[0]->ar[$curI+1]->name;
		if ($curI!=0) {
			$sTitle=$sTitle.",";
		}
		$sTitle=$sTitle." ".$sFeatArtist[$curI];
		$sTag=$sTag.",".$sFeatArtist[$curI];
	}
	$sTitle=$sTitle.")";
}
//专辑名
$sAlbumName=$albumdata->album->name;
//专辑图片地址（去除http:）
$sAlbumPicUrl=substr($albumdata->album->picUrl,5);
//歌词
$sLyric=$lyricdata->lrc->lyric;
//替换全角字符
$sLyric=str_replace("’","'",$sLyric);
$sLyric=str_replace("‘","'",$sLyric);
//$sLyric=str_replace("\n","</p><p>",$sLyric);
//删除时间戳
//$sLyric=preg_replace("/\[\d\d\:\d\d\.\d\d\]/", "", $sLyric);
//$sLyric=preg_replace("/\[.*\]/", "", $sLyric);
//$sLyric="<p>".$sLyric."</p>";

$sEngLyc=explode("\n",$sLyric);
//var_dump($sEngLyc);

$line=array();

//将歌词以时间戳为key,歌词内容为value分解成map
for ($curI=0;$curI<count($sEngLyc);$curI++) {
	//匹配到时间戳，后半句排除只有时间戳没有实际歌词的情况
	//PREG_OFFSET_CAPTURE:如果传递了这个标记，对于每一个出现的匹配返回时会附加字符串偏移量(相对于目标字符串的)。 注意：这会改变填充到matches参数的数组，使其每个元素成为一个由 第0个元素是匹配到的字符串，第1个元素是该匹配字符串 在目标字符串subject中的偏移量。
	//http://php.net/manual/zh/function.preg-match.php
	if (preg_match("/\[.*\]/",$sEngLyc[$curI],$matches,PREG_OFFSET_CAPTURE)==1) {
		if (strlen($sEngLyc[$curI])>strlen($matches[0][0])) {
			$time=substr($sEngLyc[$curI],$matches[0][1],strlen($matches[0][0]));
			$bar=substr($sEngLyc[$curI],$matches[0][1]+strlen($matches[0][0]));
	//		echo $time." wtf  ".$bar."<br>";
			$line[$time]=$bar;
		}
	}
}

//var_dump($line);
//echo "<br>";

//如果此歌曲有中文翻译，按时间戳为key将中文歌词跟在每行尾
if (property_exists($lyricdata->tlyric,"lyric")) {
	$sTlyric=$lyricdata->tlyric->lyric;

	$sChnLyc=explode("\n",$sTlyric);
//	var_dump($sChnLyc);


	for ($curI=0;$curI<count($sChnLyc);$curI++) {
		if (preg_match("/\[.*\]/",$sChnLyc[$curI],$matches,PREG_OFFSET_CAPTURE)==1) {
	//		echo $sChnLyc[$curI]." wtf ".$curI." wtf ".$matches[0][0]."<br>";
			if (strlen($sChnLyc[$curI])>strlen($matches[0][0])) {
				$time=substr($sChnLyc[$curI],$matches[0][1],strlen($matches[0][0]));
	//			echo $time." ".gettype($time)."<br>";
				$chnBar=substr($sChnLyc[$curI],$matches[0][1]+strlen($matches[0][0]));
	//			echo $time."   ".$chnBar."<br>";
	//			echo array_key_exists($time,$line);
				if (array_key_exists($time,$line)) {
					$line[$time]=$line[$time]."</p><p>".$chnBar;
				}
			}
		}
	}
	//var_dump($line);
}

//将歌词map再组合成string
$sRealLyric=implode("</p><p>",$line);
//var_dump($sRealLyric);

echo '<span style="font-family: 微软雅黑, &#39;Microsoft YaHei&#39;; background-color: #FFFFFF;">';

//echo "ID=".$sId."<br>";
//echo "Title=".$sTitle."<br>";
//echo "MainArtist=".$sMainArtist."<br>";
//if ($sArtistCnt>1) {
//	echo "FeatArtist=";
//	for ($curI=0;$curI<count($sFeatArtist)-1;$curI++) {
//		echo $sFeatArtist[$curI].",";
//	}
//	echo $sFeatArtist[count($sFeatArtist)-1]."<br>";
//}
//echo "Tag=".$sTag."<br>";
//echo "AlbumID=".$sAlbumId."<br>";
//echo "AlbumName=".$sAlbumName."<br>";
//echo "AlbumPicUrl=".$sAlbumPicUrl."<br>";
//echo "DirectLink=".$sDirectLink."<br>";
//echo "NeteasePage=".$sPage."<br>";
//echo "Lyric:"."<br>".$sLyric;

require("template.php");

//替换模板中对应内容
$templatestr = str_replace("@MainArtist",$sMainArtist,$templatestr);
$templatestr = str_replace("@Title",$sTitle,$templatestr);
$templatestr = str_replace("@DirectLink",$sDirectLink,$templatestr);
$templatestr = str_replace("@NeteasePage",$sPage,$templatestr);
$templatestr = str_replace("@Lyric",$sRealLyric,$templatestr);
$templatestr = str_replace("@AlbumPicUrl",$sAlbumPicUrl,$templatestr);
$templatestr = str_replace("@Description",$_POST["des"],$templatestr);

echo "<p><textarea rows=1 style='width:100%'>".$sMainArtist." - ".$sTitle."</textarea></p><br>";
echo "<p><textarea style='height:60%;width:100%'>".$templatestr."</textarea></p><br>";
echo "<p><textarea rows=1 style='width:100%'>".$sTag."</textarea></p>";

?>