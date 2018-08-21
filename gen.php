<?php
# just download the NeteaseMusicAPI.php into directory, require it with the correct path.
# in weapi, you should also put BigInteger.php into same directory, but don't require it.
require_once 'NeteaseMusicAPI_mini.php';

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
$sLyric=str_replace("\n","</p><p>",$sLyric);
//删除时间戳
$sLyric=preg_replace("/\[\d\d\:\d\d\.\d\d\]/", "", $sLyric);
$sLyric=preg_replace("/\[.*\]/", "", $sLyric);
$sLyric="<p>".$sLyric."</p>";

header('Content-type: text/html; charset=UTF-8');
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

$templatestr = str_replace("@MainArtist",$sMainArtist,$templatestr);
$templatestr = str_replace("@Title",$sTitle,$templatestr);
$templatestr = str_replace("@DirectLink",$sDirectLink,$templatestr);
$templatestr = str_replace("@NeteasePage",$sPage,$templatestr);
$templatestr = str_replace("@Lyric",$sLyric,$templatestr);
$templatestr = str_replace("@AlbumPicUrl",$sAlbumPicUrl,$templatestr);
$templatestr = str_replace("@Description",$_POST["des"],$templatestr);

echo "<p><textarea rows=1 style='width:100%'>".$sMainArtist." - ".$sTitle."</textarea></p><br>";
echo "<p><textarea style='height:60%;width:100%'>".$templatestr."</textarea></p><br>";
echo "<p><textarea rows=1 style='width:100%'>".$sTag."</textarea></p>";

?>