<?php
	//Converts dates from facebook strings to SQL datetime
	function toDateTime($fdate){
		if(preg_match("/([0-9]*) hr(s)?/", $fdate, $match)) {$date=date_parse(date("Y-m-d H:i:s",strtotime('-'.$match[1].' hours')));}//some hours ago
		else if(preg_match("/[a-zA-Z]* [0-9]* at [0-9]*:[0-9]* [A|P]M/", $fdate)) {$date=date_parse_from_format("Y M d ?? h:i a", date("Y")." ".$fdate);}//this month at a specific time
		else if(strpos($fdate,"Yesterday") !== false){//yesterday
			$date=date_parse(date("Y-m-d H:i", strtotime('-1 days')));
			$righthour=date_parse_from_format("????????? ?? h:i a", $fdate);
			$date['hour']=$righthour['hour'];
			$date['minute']=$righthour['minute'];
		}
		else if(preg_match("/([0-9]*) min(s)?/", $fdate, $match)) {$date=date_parse(date("Y-m-d H:i:s",strtotime('-'.$match[1].' minutes')));}//some minutes ago
		else if(preg_match("/[a-zA-Z]* [0-9]*, 20[0-9][0-9]/", $fdate)) {$date=date_parse_from_format("M d, Y H:i", $fdate." 12:0");}//a long time ago, not this year
		else if(preg_match("/[a-zA-Z]* [0-9]*/", $fdate)) {$date=date_parse_from_format("Y M d H:i", date("Y")." ".$fdate." 12:0");} //a long time ago, but in this year
		else {$date=date_parse(date("Y-m-d h:i"));} //??? set today, just to avoid error
		return $date['year']."-".$date['month']."-".$date['day']." ".$date['hour'].":".$date['minute'].":".$date['second'];
	}

	//get real image url
	function fixImage($imglink){
		if(substr($imglink,0,16)=="https://external"){
			preg_match('/(.*?)&url=(.*?)&cfs=/',$imglink, $match);
			if(isset($match[2])) $imglink = urldecode($match[2]);
		}
		return $imglink;
	}

	//get real url
	function toNativeUrl($url){
		if(preg_match('/https:\/\/l\.facebook\.com\/l\.php\?u=(.*?)&h=.*?/',$url, $match)) return urldecode($match[1]);
		else if(preg_match('/https:\/\/www\.facebook\.com\/photo\.php\?.*/',$url)) return $url;
		else if(preg_match('/(.*)\?.*/',$url,$match)) return "https://www.facebook.com".$match[1];
		else return "https://www.facebook.com".$url;
	}

	//get array with fields ready to be filled
	function getCleanArray($pageid){
		$clean= Array();
		$clean['_6m7 _3bt9']=$pageid;
		$clean['mbs _6m6 _2cnj _5s6c']="";
		$clean['_5pbx userContent _3576']="";
		$clean['scaledImageFitWidth img']=NULL;
		return $clean;
	}

	ignore_user_abort(true);
	$fake_user_agent = array("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36","Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:68.0) Gecko/20100101 Firefox/68.0");
	ini_set('max_execution_time', 45);//This script could takes a long time to run
	include_once("rss_db.php");//Include database connection
	$db= Database::getInstance();
	$pageid= $db->getOlderPage();//Get the page with older update
	$doc = new \DOMDocument(); 
	$last_title; //memorizes the title of the last feed entered	

	//setupping curl to get pages in English
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_USERAGENT, $fake_user_agent[rand(0,1)]);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Language: en']);
	curl_setopt($ch, CURLOPT_URL, "https://www.facebook.com/pg/".$pageid."/posts/");

	$data = curl_exec($ch);
	curl_close($ch);

	@$doc->loadHTML($data);
	$xpath = new \DOMXpath($doc);
	//query to get text content, img and url (see README.md for more info)
	$posts = $xpath->query('//div[@class="_1xnd"]//*[@class="l_c3pyo2v0u" or
	@class="_5pbx userContent _3576" or
	@class="mbs _6m6 _2cnj _5s6c" or
	@class="_5pbx userContent _3ds9 _3576" or
	@class="_6m7 _3bt9" or
	@class="scaledImageFitWidth img" or
	@class="scaledImageFitHeight img" or
	@class="_52c6" or
	@class="_5pcq" or
	@class="timestampContent" or
	@class="_4-u2 _4-u8"
	]');

	$db->cleanItem($pageid);

	$postArr= Array();
	$currentPost= getCleanArray($pageid);

	$limit=$posts->length;
	$n=-1;
	//cycle that associates the 'class' found of the same post
	for($i=0;$i<$limit;$i++) {
		//if this is the the first 'class' of a new post
		if($posts[$i]->getAttribute("class")=="_4-u2 _4-u8"){
			if($n>=0) $postArr[$n]=$currentPost;
			$n++;
			$currentPost= getCleanArray($pageid);
		}
		//else this is just one field of the same post
		else{
			if($posts[$i]->getAttribute("href")!="")
				$currentPost[$posts[$i]->getAttribute("class")]=toNativeUrl($posts[$i]->getAttribute("href"));	
			else $currentPost[$posts[$i]->getAttribute("class").""]=$posts[$i]->textContent.$posts[$i]->getAttribute("src");
		}
	}

	foreach($postArr as $postToAdd){
		//From the time described as a string to SQL datetime
		$postToAdd["timestampContent"]=toDateTime($postToAdd["timestampContent"]);

		//Get direct image url
		$postToAdd["scaledImageFitWidth img"]=fixImage($postToAdd["scaledImageFitWidth img"]);
		if(isset($postToAdd["scaledImageFitHeight img"])) $postToAdd["scaledImageFitWidth img"]=fixImage($postToAdd["scaledImageFitHeight img"]);

		//if there is no preview, then the link is the one belonging to the post
		if(!isset($postToAdd["_52c6"])) $postToAdd["_52c6"]=$postToAdd["_5pcq"];

		//if the post is text-only then the title of the main link is = to the text
		if(isset($postToAdd["_5pbx userContent _3ds9 _3576"])) $postToAdd['mbs _6m6 _2cnj _5s6c'].=$postToAdd["_5pbx userContent _3ds9 _3576"];

		//get a good description: preview subtitle, preview title, post text
		$desc=htmlentities( $postToAdd['_6m7 _3bt9']."\n\n".$postToAdd['mbs _6m6 _2cnj _5s6c']."\n\n".$postToAdd['_5pbx userContent _3576'] ,ENT_COMPAT,'UTF-8');

		//this fix image-only posts that have no title and need to have different primary key
		if($pageid==$postToAdd['_6m7 _3bt9']) $postToAdd['_6m7 _3bt9']=$postToAdd['_6m7 _3bt9']." ////".hash("md5",$desc.$postToAdd['scaledImageFitWidth img']);
		else $postToAdd['_6m7 _3bt9']=substr($postToAdd['_6m7 _3bt9'],0,115);

		$db->createItem(
			htmlentities($postToAdd['_6m7 _3bt9'],ENT_COMPAT,'UTF-8'),
			$postToAdd["_52c6"],
			$desc,
			$postToAdd['scaledImageFitWidth img'],
			$postToAdd['timestampContent'],
			$pageid
		);
	}

	$db->registerUpdate($pageid);

	echo "<html><head><title>Nothing to show.</title></head><body><p>Website under construction.</p></body></html>"; //just to show something
?>