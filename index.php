<?php
$videolink = $_GET['link'];
function checkServer($domains=array(), $url){
    foreach($domains as $domain){
        if(strpos($url, $domain) > 0){
            return true;
        }else{
            return false;
        }
    }
}
function downloadMP3($videolink){
    parse_str(parse_url($videolink, PHP_URL_QUERY), $parms);
    $id = $parms['v'];
    $output = "download/".$id.".mp3";
    if(file_exists($output)){
        return $output;
    }else{
        $descriptorspec = array(
            0 => array(
                "pipe",
                "r"
            ) , // stdin
            1 => array(
                "pipe",
                "w"
            ) , // stdout
            2 => array(
                "pipe",
                "w"
            ) , // stderr
        );
        $cmd = 'youtube-dl --extract-audio --audio-quality 0 --audio-format mp3 --output download/"'.$id.'.%(ext)s" '.$videolink;
        $process = proc_open($cmd, $descriptorspec, $pipes);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $ret = proc_close($process);
        if($errors){
            //print($errors);
        }
        return $output;
    }
}

function getVideoName($url){
    $youtube = "http://www.youtube.com/oembed?url=". $url ."&format=json";
    $curl = curl_init($youtube);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $return = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($return, true);
	return $json['title'];
}

if(!empty($videolink)){
	if(!checkServer(array("youtube.com","youtu.be"), $videolink)){
		$response_array['error'] = 'true';
		$response_array['message'] = 'Link koji ste unijeli nije youtube link!';
		echo json_encode($response_array);
		exit;
	}
		
	$downloadpath = downloadMP3($videolink);
	header('Content-type: octet/stream');
	header('Content-disposition: attachment; filename="'.getVideoName($videolink).'.mp3"');
	header('Content-Length: '.filesize($downloadpath));
	readfile($downloadpath);
	exit;
}else{
	$response_array['error'] = 'true';
	$response_array['message'] = 'Niste unijeli youtube link!';
	echo json_encode($response_array);
	exit;
}
