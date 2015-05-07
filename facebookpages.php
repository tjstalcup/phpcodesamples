<?php

require('data.php');
session_start();

require('lib/oauth2/Client.php');
require('lib/oauth2/GrantType/IGrantType.php');
require('lib/oauth2/GrantType/AuthorizationCode.php');

const CLIENT_ID     = 'XXXXX';
const CLIENT_SECRET = 'XXXXX';


$redirect_uri = $domain.'facebook.php?client=';
define('REDIRECT_URI',$redirect_uri);
const AUTHORIZATION_ENDPOINT = 'https://graph.facebook.com/oauth/authorize';
const TOKEN_ENDPOINT         = 'https://graph.facebook.com/oauth/access_token';

if(isset($_GET['id'])){
	$id = urldecode($_GET['id']);
	$client_id = $_SESSION['client_id'];
	$name = urldecode($_GET['name']);
	
	$fb_site_id = '';
	$fb_user_id = $_SESSION['fb_user_id'];

	$client = new OAuth2\Client(CLIENT_ID, CLIENT_SECRET);
	$client->setAccessToken(urldecode($_GET['token']));
	$response = $client->fetch('https://graph.facebook.com/'.$id);
	$page = $response['result'];
	$url = $page['link'];
	$likes = $page['likes'];
	$shares = '0';

	$q = mysqli_query($con,"SELECT fb_page_id FROM fb_page WHERE fb_unique_identifier='$id' AND client_id='$client_id'");
	if(mysqli_num_rows($q)){
		//exists
		$r = mysqli_fetch_object($q);
		$fb_site_id = $r->fb_page_id;
	} else {
		//doesnt exist
		mysqli_query($con,"INSERT INTO fb_page VALUES(NULL,'$id','$client_id','$name','$url','$likes','$shares','1',NOW(),NOW())");
		$fb_site_id = mysqli_insert_id($con);
		mysqli_query($con,"INSERT INTO fb_user_pages VALUES(NULL,'$fb_user_id','$fb_site_id',NOW(),NOW(),'0')");
	}
	// redirect back to this page so that Coaches can add multiple FB Pages
	header("Location: facebookPages.php?client=$client_id");

}