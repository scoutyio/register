<?php

class Register extends Controller {

	function __construct(){
		parent::__construct();
	}

	function index(){

		$this->view->meta = '<meta property="og:type" content="article" />' .
							'<meta property="og:description" content="Join Footballers FC" />' .
							'<meta property="og:image" content="' . _RES_  . 'assets/img/top_logo.png" />' .
							'<meta property="og:image:type" content="image/png" />' .
							'<meta property="og:image:width" content="200" />' .
							'<meta property="og:image:height" content="200" />';
		$eqApp = new Apps();
		if(isset($_SESSION["fcSc_id"])){
			$eqApp->redirect('/u/v/' . $_SESSION["fcSc_username"]);
		}

		$this->view->header = "ready";
		$this->view->title = "Register";
		$this->view->loadPage();
		$this->view->render('index');
		$this->view->loadFooter();
	}

	public function newMember() {

		header('Content-type: application/json');

		$eqApp = new Apps();
		$where = array('email = "'.$_POST['email'].'"');
		$mem   = $eqApp->api('members', 0, 0, 0, 1, 1, $where);

		if(count($mem)) {
			$response = array(
				'r' => 'fail', 
				'html' => 'Sorry, but there is already a member with this email.'
			);
		} else {

			$latest_q   = 'SHOW TABLE STATUS 
	                       LIKE 
	                       "cms_content"';
	        $latest_r   = mysql_query($latest_q);
	        $latestA    = mysql_fetch_array($latest_r);
	        $latest     = $latestA['Auto_increment'];

			$fname      = $_POST['fname'];
			$lname      = $_POST['lname'];
			$email      = $_POST['email'];
			$username   = $_POST['username'];
			$status     = $_POST['status'];
			$dob        = $date = DateTime::createFromFormat('d/m/Y', $_POST['dob'])->format('Y-m-d');
			$gender     = $_POST['gender'];
			$address    = $_POST['address'];
			$country    = $_POST['country'];
			$locality   = $_POST['locality'];
			$lat        = $_POST['lat'];
			$long       = $_POST['long'];
			$postcode   = $_POST['postcode'];
			$areaCode   = $_POST['areaCode'];
			$tel        = $_POST['tel'];
			$password   = $_POST['password'];
			$numclients = $_POST['numclients'];
			$intentions = $_POST['intentions'];

			//make their resources directory 
			if (!file_exists('uploads/')) {
                mkdir('uploads/', 0777);
            }

            if (!file_exists('uploads/' . $latest)) {
                mkdir('uploads/' . $latest, 0777);
            }

            if (!file_exists('uploads/' . $latest . '/photo')) {
                mkdir('uploads/' . $latest . '/photo', 0777);
            }
            $bgnum = rand(1,5);
            $bgimg = '../../../res/assets/bg-images/bg-' . $bgnum . '.jpg';
			/* QUERY TO ADD NEW MEMBER*/
			$query = 'INSERT INTO cms_content
						(recordset,
						pluginid,
						membertype,
						fname,
						lname,
						username,
						email,
						dob,
						gender,
						address,
						country,
						locality,
						datejoined,
						latitude,
						longitude,
						postcode,
						areacode,
						tel,
						password,
						level,
						' . ($status == 'agent' ? 'numclients,intentions,' : '') . '
						tackling,
						marking,
						positioning,
						heading,
						passing,
						dribbling,
						shootingpower,
						finishing,
						technique,
						stamina,
						strength,
						aggression,
						speed,
						offtheball,
						creativity
						' . ($status == 'premium' ? ',paid' : '') . ',
						banner)
					VALUES (
						"members",
						"1",
						"'.$status.'",
						"'.$fname.'",
						"'.$lname.'",
						"'.$username.'",
						"'.$email.'",
						"'.$dob.'",
						"'.$gender.'",
						"'.$address.'",
						"'.$country.'",
						"'.$locality.'",
						"'.date("Y-m-d").'",
						"'.$lat.'",
						"'.$long.'",
						"'.$postcode.'",
						"'.$areaCode.'",
						"'.$tel.'",
						"'.$password.'",
						"client",
						' . ($status == 'agent' ? '"' . $numclients . '","' . $intentions . '",' : '') . '
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0",
						"0"
						' . ($status == 'premium' ? ',"no"' : '') . ',
						"' . $bgimg . '")';
			$run = mysql_query($query) or die (mysql_error());
			if($run) {

				$datetime = date("Y-m-d H:i:s");
				 /* CREATE TOKEN FOR NEW EMAIL ACTIVATION */
	            $token1 = md5(uniqid(rand(), true));
	            $insert_token_q = 'INSERT INTO cms_content
									(recordset,
									pluginid,
									recordid,
									sender,
									isread,
									link,
									type,
									content,
									datecreated,
									tokenid,
									emailactivation,
									isinitial)
								  VALUES (
									"notifications",
									"1",
									"'. $latest .'",
									"0",
									"no",
									"'._SITEROOT_.'u/activate",
									"admin",
									"Your email still needs activation",
									"'.$datetime.'",
									"'.$token1.'",
									"yes",
									"yes")';

				$insert_r = mysql_query($insert_token_q);
	            $url      = _SITEROOT_ . 'u/activate/' . $token1;
	            /* SEND EMAIL NOTIFICATION */
				require 'plugins/mail/Model/mail_model.php';
				$mail = new Mail_Model();

	            $from    = "Footballers FC";
				$sender  = "FFC Team";
				$subject = "Welcome to Footballers FC";
				$to = array(
					$_POST['email'] => $_POST['fname']. ' ' . $_POST['lname']
				);
				$message = file_get_contents('plugins/mail/views/new-member/index.phtml');
				$message = str_replace("{{name}}",$fname,$message);
				$message = str_replace("{{action_url}}",$url,$message);
				$message = str_replace("{{username}}",$username,$message);
				$message = str_replace("{{site_root}}",_SITEROOT_,$message);
				$message = str_replace("{{product_name}}",$from,$message);
				$message = str_replace("{{sender_name}}",$sender,$message);
				/* SEND IT*/
				$mail->mail($to,$subject,$message);
				//create a token if the user is premium so that incase they dont pay they must pay.
				if($status == 'premium') {
					$token = md5(uniqid(rand(), true));
					$insert_token_q = 'INSERT INTO cms_content
									(recordset,
									pluginid,
									recordid,
									sender,
									isread,
									link,
									type,
									content,
									datecreated,
									tokenid,
									paymentpending,
									isinitial)
								  VALUES (
									"notifications",
									"1",
									"'. $latest .'",
									"0",
									"no",
									"'._SITEROOT_.'payments/paynow",
									"admin",
									"Your payment is still pending",
									"'.$datetime.'",
									"'.$token.'",
									"yes",
									"yes")';
					$insert_r = mysql_query($insert_token_q);
		    		$_SESSION["fcSc_paymentpending"] = 1;
		    		$_SESSION['fcSc_profile_inc_basic'] = 1;
		    		$_SESSION['fcSc_profile_inc_basic-2'] = 1;
		    		$_SESSION['fcSc_profile_inc_bio'] = 1;
		    		$_SESSION['fcSc_profile_inc_positions'] = 1;
		    		$_SESSION['fcSc_profile_inc_skills'] = 1;
				}
				if($status == 'agent') {

		            $from    = "Footballers FC";
					$sender  = "FFC Team";
					$subject = "A scout has joined";
					$to = array(
						'admin@gofootballpro.com' => 'Footballers FC',
						'eddythemeddy@gmail.com' => 'Anubir Singh',
						'ilan.slazenger@gmail.com' => 'Ilan Slazenger'
					);
					$message  = 'Hello,<br/><br/>'; 
					$message .= 'A scout has just signed up to our site!<br/><br/>';
					$message .= 'Their name: <strong>' . $fname . ' ' . $lname . '</strong><br/>';
					$message .= 'Their username: <strong>' . $username . '</strong><br/>';
					$message .= 'Their email: <strong>' . $email . '</strong><br/>';
					$message .= 'Their location: <strong>' . $locality . '</strong><br/>';
					$message .= 'Their phone: <strong>' . $tel . '</strong><br/>';
					$message .= 'How many clients do they have?: <strong>' . $numclients . '</strong><br/>';
					$message .= 'What are their intentions?: <strong>' . $intentions . '</strong><br/>';
					$message .= '<br/><br/>Thank you,<br/>FFC Team';
					$mail->mail($to,$subject,$message);
				}
				//success create sessions
				$_SESSION["fcSc_id"]      	 = $latest;
				$_SESSION["fcSc_fname"]      = $fname;
				$_SESSION["fcSc_lname"]      = $lname;
	    		$_SESSION["fcSc_email"]      = $email;
	    		$_SESSION['fcSc_membertype'] = $status;
	    		$_SESSION["fcSc_username"]   = $username;
			    $_SESSION["fcSc_custom_url"] = _SITEROOT_ . 'u/v/' . $username;
			    $_SESSION["fcSc_photo"]      = ($mem["photo"] ? _SITEROOT_ . 'uploads/' . $mem["id"] . '/photo/' . $mem["photo"] : _RES_ . 'assets/img/profile-generic.jpg');
			    $_SESSION["fcSc_emailactivation"] = 1;

			    if($status == "premium") {
			    	$red = _SITEROOT_ . 'payments/paynow';
			    } else {
			    	$red = _SITEROOT_ . 'u/v/' . $username;
			    }

				$response = array(
					'r'   => 'success', 
					'loc' => $red
				);
			} else {
				$response = array(
					'r' => 'fail',
					'msg' => 'Sorry there was a techinical error, please try again later'
				);
			}
		}

		echo json_encode($response);
		exit;
	}

	public function validateEmail() { 
		
		header('Content-type: application/json');
		$fails = array();
		if (time() < strtotime('+13 years', strtotime($_POST['dob']))) {
			array_push($fails,'dob');
		}
		$query_q = 'SELECT id, username, email FROM 
				    cms_content 
				    WHERE 
				    recordset = "members"
				    AND (username LIKE "'.$_POST['username'].'" OR email LIKE "'.$_POST['email'].'")';
		$query_r = mysql_query($query_q);
		$query_n = mysql_num_rows($query_r);
		if($query_n) {
			$row = mysql_fetch_array($query_r);
			if($row['email'] == $_POST['email']) {
				array_push($fails, 'email');
			}
			if($row['username'] == $_POST['username']) {
				array_push($fails, 'username');
			}
		}
		if(count($fails)){
			$response = array(
				'r' => 'fail',
				'fails' => $fails
			);
		} else {
			$response = array(
				'r' => 'success'
			);
		}
		echo json_encode($response);
		exit;
	}

	/*
		Creating a new token in case they lost their email
	*/
	public function newtoken() {
		//validating that this token is acutally theirs by their session
		//and token type
		$q_q = 'SELECT emailactivation,tokenid,recordid,timestamp FROM cms_content 
				WHERE 
				recordset = "notifications" 
				AND recordid = "' . $_SESSION['fcSc_id'] . '"
				AND emailactivation = "yes"';
		$q_r = mysql_query($q_q) or die(mysql_error());
		$query_n = mysql_num_rows($q_r);
		if($query_n) {

			require 'plugins/mail/Model/mail_model.php';
			$mail = new Mail_Model();
			//if it is their token create a new tokenid
			$token1 = md5(uniqid(rand(), true));
			$url     = _SITEROOT_ . 'u/activate/' . $token1;
			//update that token to the new token
			$q_q = 'UPDATE cms_content SET tokenid = "' .$token1. '" 
					WHERE recordid = "' . $_SESSION['fcSc_id'] . '"
					AND recordset = "notifications"';
			$q_r = mysql_query($q_q);
			//
			$from    = "Footballers FC";
			$sender  = "FFC Team";
			$subject = "Welcome to Footballers FC";
			$to = array(
				$_SESSION['fcSc_email'] => $_SESSION['fcSc_fname']. ' ' . $_SESSION['fcSc_lname']
			);
			$message = file_get_contents('plugins/mail/views/new-member/index.phtml');
			$message = str_replace("{{name}}",$_SESSION['fcSc_fname'],$message);
			$message = str_replace("{{action_url}}",$url,$message);
			$message = str_replace("{{site_root}}",_SITEROOT_,$message);
			$message = str_replace("{{username}}",$_SESSION['fcSc_username'],$message);
			$message = str_replace("{{product_name}}",$from,$message);
			$message = str_replace("{{sender_name}}",$sender,$message);
			/* SEND IT*/
			$mail->mail($to,$subject,$message);
			echo json_encode(array("r"=>"success","msg"=>"New Token Sent!"));
		} else {
			echo json_encode(array("r"=>"error","msg"=>"Nice Try!"));
		}
	}
}

?>