<?php
	error_reporting(0); //comment this row during setup, uncomment in production
	header("Content-Type: application/rss+xml; charset=UTF-8");
	if(isset($_GET["rsschannel"])){
		$channel=$_GET["rsschannel"];
		include_once("rss_db.php");
		$feeds=Database::getInstance()->GetFeed($channel);
		$rss ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$rss .= "<rss xmlns:atom=\"http://www.w3.org/2005/Atom\" version=\"2.0\">\n";
		$rss .= "\t<channel>\n";
		$rss .= "\t\t<title><![CDATA[".$feeds[0]["chtitle"]."]]></title>\n";
		$rss .= "\t\t<link>".$feeds[0]["chlink"]."</link>\n";
		$rss .= "\t\t<atom:link rel=\"self\" type=\"application/rss+xml\" href=\"https://".$_SERVER['HTTP_HOST']."/rss.php?rsschannel=".$channel."\"/>\n";
		$rss .= "\t\t<description><![CDATA[".$feeds[0]["chdescription"]."description]]></description>\n";
		foreach($feeds as $feed){
			$rss .= "\t\t<item>\n";
			$rss .= "\t\t\t<title><![CDATA[".substr($feed["title"], 0, strpos($feed["title"], '////')-1)."]]></title>\n";
			if($feed["link"]!="") {
				$rss .= "\t\t\t<link><![CDATA[".$feed["link"]."]]></link>\n";
				$rss .= "\t\t\t<guid><![CDATA[".$feed["link"]."]]></guid>\n";
			}
			else{
				$rss .= "\t\t\t<link>http://startpage.com/".hash("md5",$feed["description"])."</link>\n";
				$rss .= "\t\t\t<guid>http://startpage.com/".hash("md5",$feed["description"])."</guid>\n";
			}
			if($feed["imgurl"]!="")	$rss .= "\t\t\t<description><![CDATA[<img src=\"".$feed["imgurl"]."\">".$feed["description"]."]]></description>\n";
			else $rss .= "\t\t\t<description><![CDATA[".$feed["description"]."]]></description>\n";
			$rss .= "\t\t\t<pubDate>".date('r', strtotime($feed["ptime"]))."</pubDate>\n";
			$rss .= "\t\t</item>\n";
		}
		$rss .= "\t</channel>\n";
		$rss .= "</rss>\n";
		echo $rss;
	}
	else echo "Nothing to show.";
?>