<?php

function theme_t_wp_process_salesportal_login( $entry ){
  $creds = array();
  $creds['user_login'] = $entry[1];
  $creds['user_password'] = $entry[2];
  $creds['remember'] = false;
  $user = wp_signon( $creds, false );
  if($user->roles[0]=="salesrep"){
    header("Location: /credit-application/");
  } else {
    header("Location: /wp-admin/");
  }
  
}

add_action( 'gform_after_submission_2', 'theme_t_wp_process_salesportal_login', 10, 2 );

function send_email($entry,$data,$pdfcode){
  $url = 'https://api.sendgrid.com/api/mail.send.json';
  $fields = array(
    'api_user' => 'usernamehere',
    'api_key' => 'passwordhere',
    'to' => 'tstalcupjr@gmail.com',
    'toname' => 'Credit User',
    'subject' => 'New Credit Application',
    'text' => 'New Credit Application',
    'html' => 'New Credit Application',
    'from' => 'admin@tjstalcup.com',
    'fromname' => 'Credit Application',
    'replyto' => 'tstalcupjr@gmail.com',
    'files['.$pdfcode.'.pdf]' => '@'.'/data/25/3/22/119/3022771/user/3356342/htdocs/tjstalcup-com/fpdf/tmp/'.$pdfcode.'.pdf'
  );
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  $retValue = curl_exec($ch);
  curl_close($ch);
  unlink('/data/25/3/22/119/3022771/user/3356342/htdocs/tjstalcup-com/fpdf/tmp/'.$pdfcode.'.pdf');
}

$result = add_role(
    'salesrep',
    __( 'Sales Rep' ),
    array(
        'read'         => true,  // true allows this capability
        'edit_posts'   => false,
        'delete_posts' => false, // Use false to explicitly deny
    )
);