<?php
	require_once("./config.php");
	$temp = array();

	$json_result = array(
		"serviceName" => null,
		"status" => null,
		"count" => 0,
		"info" => null,
	);
	$db_obj = mysqli_connect(IP_ADDR, USER_NAME, USER_PASS) or die(mysqli_error());
	mysqli_select_db($db_obj, DB_NAME) or die('problem with server selection');
	
	$type = $_REQUEST['type'];
	$json_result["serviceName"] = $type;
	$json_result["status"] = 'error';
	
	function addComment($content)
	{
		global $db_obj;
		
		date_default_timezone_set('GMT');
		$dt = new DateTime();		
		$strDate = $dt->format('Y-m-d H:i:s');

		$sql_query = "INSERT INTO comment(time, content) VALUES('".$strDate."', '".$content."')";
		
		mysqli_query($db_obj, $sql_query);
		
		$commentid = mysqli_insert_id($db_obj);
		
		return $commentid;
	}
	
	switch($type){
		case 'register':
			$name = $_REQUEST['name'];
			$password = $_REQUEST['password'];
			$email = $_REQUEST['email'];

			$sql_query = "SELECT * FROM users WHERE name='".$name."'";
			$result = 	mysqli_query($db_obj, $sql_query);
			$count = mysqli_num_rows($result);

			if( $count == 0 ){
				$sql_query = "INSERT INTO users(`name`, `password`, `email`) VALUES('".$name."', '".$password."', '".$email."')";
				mysqli_query($db_obj, $sql_query);
				$json_result["status"] = "OK";
			}
			else {
				$json_result["status"] = "Error";
			}
			echo json_encode($json_result);
			break;
		case 'login':
			$name = $_REQUEST['name'];
			$password = $_REQUEST['password'];
			
			$sql_query = "SELECT id FROM users WHERE name='".$name."' AND password='".$password."'";
			$result = 	mysqli_query($db_obj, $sql_query);
			$count = mysqli_num_rows($result);
			if($count > 0) {
				while($res_array = mysqli_fetch_array($result)) {
					$json_result['info'] = $res_array['id'];
				}
				$json_result["status"] = "OK";
			}
			else {
				$json_result["status"] = "Error";
			}
			echo json_encode($json_result);
			break;
			
		case 'getuserprofile':
			$name = $_REQUEST['name'];
			
			$sql_query = "SELECT * FROM users WHERE name='".$name."'";
			$result = 	mysqli_query($db_obj, $sql_query);
			$count = mysqli_num_rows($result);
			
			$sql_query = "SELECT * FROM follow WHERE following='".$name."'";
			$followerresult = mysqli_query($db_obj, $sql_query);
			$followercount = mysqli_num_rows($followerresult);
			
			$sql_query = "SELECT * FROM follow WHERE follower='".$name."'";
			$followingresult = mysqli_query($db_obj, $sql_query);
			$followingcount = mysqli_num_rows($followingresult);

			while($res_array = mysqli_fetch_array($result)) {
				$json_result["info"] = array(
					"email" => $res_array['email'],
					"fullname" => $res_array['fullname'],
					"website" => $res_array['website'],
					"aboutme" => $res_array['aboutme'],
					"phone" => $res_array['phone'],
					"gender" => $res_array['gender'],
					"invitecount" => $res_array['invitecount'],
					"itunecount" => $res_array['sharecount'],
					"sharecount" => $res_array['sharecount'],
					"following" => $followingcount,
					"follower" => $followercount
				);
			}
			$json_result["status"] = "OK";
			
			echo json_encode($json_result);
			
			break;
		
		case 'setuserprofile':
			$name = $_REQUEST['name'];
			
			$email = $_REQUEST['email'];
			$fullname = $_REQUEST['fullname'];
			$website = $_REQUEST['website'];
			$aboutme = $_REQUEST['aboutme'];
			$phone = $_REQUEST['phone'];
			$gender = $_REQUEST['gender'];
						
			$sql_query = "UPDATE users SET email='" . $email . "', fullname='" . $fullname . "', website='" . $website . 
										"', aboutme='" . $aboutme . "', phone='" . $phone . "', gender=" . $gender . 
										"WHERE name = '" . $name . "'";
			$result = mysqli_query($db_obj, $sql_query);

			$json_result["status"] = "OK";
			
			echo json_encode($json_result);
			
			break;
			
		case 'uploadphoto':
			$name = $_REQUEST['name'];
			
			if ( !empty($_FILES)  ) {
				move_uploaded_file($_FILES["image"]["tmp_name"],	"userimage/" . $name . ".jpg");
				$json_result["status"] = "OK";
			}
			else {
				$json_result["status"] = "Error - no file";
			}			
			
			echo json_encode($json_result);
			
			break;
			
		case 'changepassword':
			$name = $_REQUEST['name'];
			$password = $_REQUEST['password'];
			
			$sql_query = "UPDATE users SET password='" . $password . "' WHERE name = '" . $name . "'";
			mysqli_query($db_obj, $sql_query);

			$json_result["status"] = "OK";
			
			echo json_encode($json_result);
						
			break;
			
		case 'updatecountvalue':
			$name = $_REQUEST['name'];
			$invitecount = $_REQUEST['invite'];
			$itunecount = $_REQUEST['itune'];
			$sharecount = $_REQUEST['share'];
			
			$sql_query = "UPDATE users SET invitecount=" . $invitecount . ", itunecount=" . $itunecount . ", sharecount=" . $sharecount . " WHERE name = '" . $name . "'";
			$result = mysqli_query($db_obj, $sql_query);

			$json_result["status"] = "OK";
			
			echo json_encode($json_result);
						
			break;
			
		case 'isfollowing':
			$name = $_REQUEST['name'];
			$following = $_REQUEST['following'];
			
			$sql_query = "SELECT * FROM follow WHERE follower='".$name."' AND following='".$following."'";
			$result = 	mysqli_query($db_obj, $sql_query);
			$count = mysqli_num_rows($result);
			
			$json_result['info'] = 0;
			if($count > 0) {
				$json_result['info'] = 1;
			}
			
			$json_result["status"] = "OK";
			echo json_encode($json_result);			

			break;
			
		case 'follow':
			$name = $_REQUEST['name'];
			$following = $_REQUEST['following'];
			
			$sql_query = "INSERT INTO follow(`follower`, `following`) VALUES('".$name."', '".$following."')";
			$result = mysqli_query($db_obj, $sql_query);
			
			$json_result["status"] = "OK";
			echo json_encode($json_result);		
			
			break;	
			
		case 'unfollow':
			$name = $_REQUEST['name'];
			$following = $_REQUEST['following'];
			
			$sql_query = "DELETE FROM follow WHERE follower='".$name."' AND following='".$following."'";
			$result = mysqli_query($db_obj, $sql_query);

			$json_result["status"] = "OK";
			echo json_encode($json_result);			
			
			break;
			
		case 'searchuser':
			$name = $_REQUEST['name'];
			
			$sql_query = "SELECT name FROM users";
			$result = mysqli_query($db_obj, $sql_query);
	
			$count = 0;		
			while($res_array = mysqli_fetch_array($result)) {
				if ($res_array['name'] == $name)
					continue;				
				if (strpos($res_array['name'], $name) !== false) {
					array_push($temp, $res_array['name']);
					$count++;	
				}
			}
			
			$json_result["status"] = "OK";
			$json_result["info"] = $temp;
			$json_result["count"] = $count;
			
			echo json_encode($json_result);
			
			break;
						
		// video apis
		case 'get_videolikenum':
			$username = $_REQUEST['username'];
			$youtubeID = $_REQUEST['youtubeid'];
			$sql_query = "SELECT * FROM videolike WHERE youtubeid='".$youtubeID."'";
			$result = mysqli_query($db_obj, $sql_query);
			$likecount = mysqli_num_rows($result);
	
			$liked = 0;		
			while($res_array = mysqli_fetch_array($result)) {
				if ($res_array['username'] == $username) {
					$liked = 1;
					break;
				}
			}

			$sql_query = "SELECT * FROM videocomment WHERE youtubeid='".$youtubeID."'";
			$result = mysqli_query($db_obj, $sql_query);
			$commentcount = mysqli_num_rows($result);
			
			array_push($temp, $likecount);
			array_push($temp, $commentcount);
			array_push($temp, $liked);
			
			$json_result["status"] = "OK";
			$json_result["info"] = $temp;
			echo json_encode($json_result);

			break;
			
		case 'like_video':
			$username = $_REQUEST['username'];
			$youtubeID = $_REQUEST['youtubeid'];
			
			$sql_query = "SELECT * FROM videolike WHERE username='".$username."' AND youtubeid='".$youtubeID."'";
			$result = mysqli_query($db_obj, $sql_query);
			$likecount = mysqli_num_rows($result);
			
			if ($likecount == 0) {			
				$sql_query = "INSERT INTO videolike(`username`, `youtubeid`) VALUES('".$username."', '".$youtubeID."')";
				$result = mysqli_query($db_obj, $sql_query);
			}

			$json_result["status"] = "OK";
			echo json_encode($json_result);

			break;
			
		case 'unlike_video':
			$username = $_REQUEST['username'];
			$youtubeID = $_REQUEST['youtubeid'];
			
			$sql_query = "DELETE FROM videolike WHERE username='".$username."' AND youtubeid='".$youtubeID."'";
			$result = mysqli_query($db_obj, $sql_query);

			$json_result["status"] = "OK";
			echo json_encode($json_result);

			break;
			
		case 'getvideocomment':			
			$youtubeID = $_REQUEST['youtubeid'];

			$sql_query = 	"SELECT 
								videocomment.username, 
								comment.time, 
								comment.content 
							FROM videocomment 
							INNER JOIN comment
							ON videocomment.commentid=comment.id
							WHERE videocomment.youtubeid='".$youtubeID."' ".
							"ORDER BY comment.time DESC";;
							
			$result = mysqli_query($db_obj, $sql_query);
			$count = mysqli_num_rows($result);
			
			$i = 0;
			
			while($res_array = mysqli_fetch_array($result)) {
				$temp[$i] = array(
					"username" => $res_array['username'],
					"time" => $res_array['time'],
					"content" => $res_array['content'],
				);

				$i = $i + 1;
			}
						
			$json_result["status"] = "OK";
			$json_result["count"] = $count;
			$json_result["info"] = $temp;
			echo json_encode($json_result);
			
			break;
			
		case 'addvideocomment':
			$username = $_REQUEST['username'];
			$youtubeID = $_REQUEST['youtubeid'];
			$content = $_REQUEST['content'];
			
			$commentid = addComment($content);			
			
			$sql_query = "INSERT INTO videocomment(username, youtubeid, commentid) VALUES('".$username."', '".$youtubeID."', ".$commentid.")";
			mysqli_query($db_obj, $sql_query);
			
			$json_result["status"] = "OK";
			$json_result["count"] = $count;
						
			echo json_encode($json_result);
			
			break;
			
		// music apis			
		case 'getmusic':
			$username = $_REQUEST['username'];
			$sql_query = "SELECT * FROM music ORDER BY time DESC LIMIT 20";
			$result = mysqli_query($db_obj, $sql_query);
			$count = mysqli_num_rows($result);
			
			$i = 0;
			
			while($res_array = mysqli_fetch_array($result)) {
				
				$like_sql_query = "SELECT username FROM musiclike WHERE musicid=".$res_array['id'];
				$likeresult = mysqli_query($db_obj, $like_sql_query);
				$likecount = mysqli_num_rows($likeresult);
				$liked = 0;
				
				$likedusers = array();
				while($like_res_array = mysqli_fetch_array($likeresult)) {
					if ($like_res_array['username'] == $username) {
						$liked = 1;
					}
					array_push($likedusers, $like_res_array['username']);
				}
				
				$comment_sql_query = "SELECT username FROM musiccomment WHERE musicid=".$res_array['id'];
				$commentresult = mysqli_query($db_obj, $comment_sql_query);
				$commentcount = mysqli_num_rows($commentresult);

//				$imagepath = "http://twoservices.net/work/tian/orion/song/image/" . $res_array['imagefile'];
//				$songpath = "http://twoservices.net/work/tian/orion/song/" . $res_array['songfile'];

				$temp[$i] = array(
					"id" => $res_array['id'],
					"name" => $res_array['name'],
					"itunelink" => $res_array['itunelink'],
					"time" => $res_array['time'],
					"imagefile" => $res_array['imagefile'],
					"songfile" => $res_array['songfile'],
					"likes" => $likecount,
					"likedusers" => $likedusers,
					"liked" => $liked,
					"comments" => $commentcount
				);

				$i = $i + 1;
			}
			
			$json_result["status"] = "OK";
			$json_result["count"] = $count;
			$json_result["info"] = $temp;
			echo json_encode($json_result);
			
			break;
			
		case 'like_music':
			$username = $_REQUEST['username'];
			$musicID = $_REQUEST['musicid'];
			
			$sql_query = "SELECT * FROM musiclike WHERE username='".$username."' AND musicid=".$musicID;
			$result = mysqli_query($db_obj, $sql_query);
			$likecount = mysqli_num_rows($result);
			
			if ($likecount == 0) {			
				$sql_query = "INSERT INTO musiclike(`username`, `musicid`) VALUES('".$username."', ".$musicID.")";
				mysqli_query($db_obj, $sql_query);
				
				$sql_query = "SELECT type FROM music WHERE id = '" . $musicID . "'";
				$result = mysqli_query($db_obj, $sql_query);
				$res_array = mysqli_fetch_array($result);
				
				$json_result["info"] = $res_array['type'];
				$json_result["status"] = "OK";
			}

			echo json_encode($json_result);
			break;
			
		case 'unlike_music':
			$username = $_REQUEST['username'];
			$musicID = $_REQUEST['musicid'];
			
			$sql_query = "DELETE FROM musiclike WHERE username='".$username."' AND musicid=".$musicID;
			$result = mysqli_query($db_obj, $sql_query);
			
			$sql_query = "SELECT type FROM music WHERE id = '" . $musicID . "'";
			$result = mysqli_query($db_obj, $sql_query);
			$res_array = mysqli_fetch_array($result);
			
			$json_result["info"] = $res_array['type'];
			$json_result["status"] = "OK";
			echo json_encode($json_result);

			break;
			
		case 'getmusiclikecount':
			$username = $_REQUEST['username'];

			$sql_query = 	"SELECT 
								musiclike.username, 
								music.type 
							FROM musiclike 
							INNER JOIN music
							ON musiclike.musicid=music.id
							WHERE musiclike.username='".$username."'";
							
			$result = mysqli_query($db_obj, $sql_query);
			$count = mysqli_num_rows($result);
			$hiphop = 0;
			$rnb = 0;
			$afrobeat = 0;
			
			while ($res_array = mysqli_fetch_array($result)) {
				if ($res_array['type'] == 1)
					$hiphop++;
				else if ($res_array['type'] == 2)
					$rnb++;
				else if ($res_array['type'] == 3)
					$afrobeat++;
			}
			
			$json_result["status"] = "OK";
			$json_result["count"] = $count;
			$json_result["info"] = array(
				"hiphop" => $hiphop,
				"rnb" => $rnb,
				"afrobeat" => $afrobeat
			);
			
			echo json_encode($json_result);

			break;
			
		case 'getmusiccomment':			
			$musicID = $_REQUEST['musicid'];

			$sql_query = 	"SELECT 
								musiccomment.username, 
								comment.time, 
								comment.content 
							FROM musiccomment 
							INNER JOIN comment
							ON musiccomment.commentid=comment.id
							WHERE musiccomment.musicid='".$musicID."' ".
							"ORDER BY comment.time DESC";
							
			$result = mysqli_query($db_obj, $sql_query);
			$count = mysqli_num_rows($result);
			
			$i = 0;
			
			while($res_array = mysqli_fetch_array($result)) {
				$temp[$i] = array(
					"username" => $res_array['username'],
					"time" => $res_array['time'],
					"content" => $res_array['content']
				);

				$i = $i + 1;
			}
						
			$json_result["status"] = "OK";
			$json_result["count"] = $count;
			$json_result["info"] = $temp;
			echo json_encode($json_result);
			
			break;
			
		case 'addmusiccomment':
			$username = $_REQUEST['username'];
			$musicID = $_REQUEST['musicid'];
			$content = $_REQUEST['content'];
			
			$commentid = addComment($content);			
			
			$sql_query = "INSERT INTO musiccomment(username, musicid, commentid) VALUES('".$username."', '".$musicID."', ".$commentid.")";
			mysqli_query($db_obj, $sql_query);
			
			$json_result["status"] = "OK";
			$json_result["count"] = $count;
						
			echo json_encode($json_result);
			
			break;
	}

?>
