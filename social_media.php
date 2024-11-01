<?php 
  /**
  * @package wpSysSocial
  * @version 1.0
  */

  class wpSysSocial { 

  /**
  * Function name: __construct()
  * Contructor to initialize code
  * Parameters: none
  */

  public function __construct(){
    add_shortcode('ft_sys', array($this, 'sys_ft_function'));
    add_action('wp', array($this, 'sys_output_buffer'));
  }

  /**
  * Function name: sys_output_buffer()
  * Function for Output Buffer
  * Parameters: none
  */

  public function sys_output_buffer() {

    global $post;
    $pid            = $post->ID;
    $url            = get_permalink($pid);
    $front_page_set = esc_attr( get_option('sys_ft_default_page') );

    $page_front  = basename($front_page_set);
    $page_back   = basename($url);

    if($page_front == $page_back){
      ob_start();
    }
  } 
  
  /**
  * Function name: sys_ft_function()
  * Function to execute shortcode
  * Parameters: $atts (To get shortcode paramenters if passed any)
  */

  public function sys_ft_function(){
    global $post;
    $pid            = $post->ID;
    $url            = get_permalink($pid);
    $front_page_set = esc_attr( get_option('sys_ft_default_page') );

    if( $front_page_set == '' ){
      $data = '';
      $data .= __('Please set the link this post/page from SYS Settings > General Settings','sys');
      return $data;
      exit;
    }else{
      @session_start();
    }

    wp_enqueue_style('sys_skeleton',SYS_URL_PATH.'css/skeleton.css"');
    wp_enqueue_style('sys_custom',SYS_URL_PATH.'css/custom.css"');

    require(SYS_DIR_PATH.'services/Twitter/twitteroauth.php');

    //Twitter App Settings
    define('TWITTER_CONSUMER_KEY', esc_attr( get_option('sys_twitter_app_secret') ));
    define('TWITTER_CONSUMER_SECRET', esc_attr( get_option('sys_twitter_api_secret') ));
    define('TWITTER_OAUTH_CALLBACK', esc_attr( get_option('sys_twitter_callback_url') ));

    $method = isset($_REQUEST['method']);
    if( $method  == 'tauth'){
    	$url = $this->twitter_auth();
    	wp_redirect($url);
      exit;
    }

    if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
      $_SESSION['oauth_status'] = 'oldtoken';
      session_destroy();
      $message = __('This authorization code has been used. Click <a href="'.$url.'">here</a> to Try again!','sys');
      echo $message;
      exit;
    }

    if (isset($_REQUEST['oauth_token'])) {
      $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

      $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

      //save new access tocken array in session
      $_SESSION['access_token'] = $access_token;
      unset($_SESSION['oauth_token']);
      unset($_SESSION['oauth_token_secret']);

    if (200 == $connection->http_code) {
      $_SESSION['status'] = 'verified';  
      } else {
      session_destroy();
      }

    if($_SESSION['status'] == 'verified'){
      $data = $this->manage_twitter_post();
      return $data;
      exit;
      }
    
    }

    if (isset($_POST["update_twitter"]) && $_POST["update_twitter"] == '1'){
      $text = htmlspecialchars(sanitize_text_field( $_POST['text'] ) );
      $base64 = htmlspecialchars(sanitize_text_field( $_POST['base64']) );
      $url_path = htmlspecialchars(sanitize_text_field( $_POST['url_path'] ) );
      $data = $this->update_twitter($text, $base64, $url_path);
      return $data;
      exit;
      }else{
      require( SYS_DIR_PATH.'services/Facebook/autoload.php' );

      //Facebook App Settings
      $_APP_ID      = esc_attr( get_option('sys_app_id'));
      $_APP_SECRET  = esc_attr( get_option('sys_app_secret'));
      $_API_VERSION = esc_attr( get_option('sys_api_version'));
      $callback_url = esc_url( get_option('sys_callback_url') );

      $fb = new Facebook\Facebook(array(
      'app_id'                => $_APP_ID,
      'app_secret'            => $_APP_SECRET,
      'default_graph_version' => $_API_VERSION,
      ));

      $helper = $fb->getRedirectLoginHelper();
      $_SESSION['FBRLH_state']=$_GET['state'];

    if (isset($_POST["update_fb"]) && $_POST["update_fb"] == '1') {
      $text = htmlspecialchars(sanitize_text_field($_POST['text']));
      $token = htmlspecialchars(sanitize_text_field($_POST['token']));
      $url_path = htmlspecialchars($_POST['path']);
      $link_array = explode('/',$url_path);
      $image = end($link_array);
      $path = SYS_DIR_PATH."cache/".$image;
      $data = $this->update_fb($text, $token, $path, $fb);
      return $data;
      exit;
    }else{
      try {
      $accessToken = $helper->getAccessToken();
      }catch(Facebook\Exceptions\FacebookResponseException $e) {

      // When Graph returns an error
      _e($e->getMessage().' Click <a href="'.$url.'">here</a> to Try again!','sys');
      exit;
      }catch(Facebook\Exceptions\FacebookSDKException $e) {

      // When validation fails or other local issues
      _e($e->getMessage().' Click <a href="'.$url.'">here</a> to Try again!','sys');
      exit;
    }// End if Update FB

    if (isset($accessToken)) {
      // Logged in!
      $_SESSION['facebook_access_token'] = (string) $accessToken;
      $data = $this->overlay();
      return $data;
      die();                      
      } elseif ($helper->getError()) {
      // The user denied the request
      exit;
    }// End If AccessToken

    $permissions          = ['email', 'user_posts','publish_actions'];
    $loginUrl             = $helper->getLoginUrl($callback_url, $permissions);
    $_SESSION['loginUrl'] = $loginUrl;
    $twitter_url          = get_permalink( $post->ID ).'?method=tauth';
    $default_image        = esc_url( get_option('sys_default_image') );

    $data  = '<div class="container">';
    $data .= '<div class="row">';
    $data .= '<div class="header">';
    $data .= '<h1>'.esc_attr( get_option('sys_heading') ).'</h1>';
    $data .= '<img class="profile" src="'.$default_image.'"/>';
    $data .= '</div>';
    $data .= '<div class="content">';
    $data .= '<br/>';
    $data .= '<p>'.esc_attr( get_option('sys_para') ).'</p>';
    $data .= '<a class="button button-primary" href="'.htmlspecialchars($loginUrl).'">'. __("Log in to Facebook","sys").'</a><br/>';
    $data .= '<a class="button button-twitter" href="'.htmlspecialchars($twitter_url).'">'. __("Sign in with Twitter","sys").'</a>';
    $data .= '</div>';
    $data .= '<footer class="footer">';
    $data .= '</footer>';
    $data .= '</div>';
    $data .= '</div>';
    return $data;
  }
  }
  }

  /**
  * Function name: update_twitter()
  * Function to tweet updated profile picture and message on Twitter
  * Parameters: $text (text to tweet), $base64 (base64 image for twitter), $url_path (url_path of image to display)
  */

  public function update_twitter($text, $base64, $url_path){
    $access_token = $_SESSION['access_token'];
    $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $info = @getimagesize($url_path);
    $extension = @image_type_to_extension($info[2]);
    $loginUrl = $_SESSION['loginUrl'];

    $extension = str_replace('.','',$extension);

    $result = $connection->post('account/update_profile_image', array('image' => $base64.';type=image/'.$extension.';filename='.$id));

    // post a tweet
    $status = $connection->post(
    "statuses/update", [
        "status" => "$text"
    ]
    );

    $data  = '<div class="container">';
    $data .= '<div class="row">';
    $data .= '<div class="header">';
    $data .= '<h1>'.__("Thank you for your support!","sys").'</h1>';
    $data .= '<img class="profile" src="'.$url_path.'" alt="">';
    $data .= '</div>';
    $data .= '<div class="content">';
    $data .= '<br/>';
    $data .= __("Your new profile picture has been added to your timeline on Twitter, Would you like to also change your Facebook profile picture?<br/><br/>","sys");
    $data .= '</div>';
    $data .= '<a class="button button-primary" href="'.htmlspecialchars($loginUrl).'" style="color:#fff !important;">'. __("Log in to Facebook","sys").'</a>';
    $data .= '</div>';
    $data .= '</div>';
    return $data;
  }

  /**
  * Function name: grab_image();
  * Function to create tempory image
  * Parameters: $url, $saveto
  */

   public function grab_image($url,$saveto){
    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $raw=curl_exec($ch);
    curl_close ($ch);
    if(file_exists($saveto)){
        unlink($saveto);
    }
    $fp = fopen($saveto,'x');
    fwrite($fp, $raw);
    fclose($fp);
  }

  /**
  * Function name: manage_twitter_post()
  * Function for pre post display of Twitter Profile picture and message
  * Parameters: none
  */

  public function manage_twitter_post(){

    global $post;
    $pid = $post->ID;
    
    $access_token = $_SESSION['access_token'];
    $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

    /* If method is set change API call made. Test is called by default. */
    $result = $connection->get('account/verify_credentials');

    $screen_name = $result->screen_name;
    $id = $result->id;

    // Access the profile_image_url element in the array
    $photo = str_replace('_normal','',$result->profile_image_url);

    $info = @getimagesize($photo);
    //$extension = @image_type_to_extension($info[2]);
    $extension = '.jpg';
    
    $this->grab_image($photo, SYS_DIR_PATH."cache/".$id.$extension);

    $path = SYS_DIR_PATH."cache/".$id.$extension;
    $service = 'twitter';

    $this->create_temp_image($id, $path, $service);
    
    $url_path =  SYS_URL_PATH."cache/".$id.$extension;

    $base64 = $this->base64_encode_image ($path);
    
    $sys_share_phrase = esc_textarea(( get_option('sys_twitter_share_message') ));
    $data  = '<div class="container">';
    $data .= '<div class="row">';
    $data .= '<div class="header">';
    $data .= '<h1>'. __("Your new profile picture is ready!","sys").'</h1>';
    $data .= '<img class="profile" src="'. $url_path.'" alt="">';
    $data .= '</div>';
    $data .= '<div class="content" style="margin-bottom:15px;">';
    $data .= '<form action="'.esc_attr(get_permalink($pid)).'" method="post" name="update_twitter">';
    $data .= '<label for="update"><h2>'.__("Add a supporting phrase to your timeline:","sys").'</h2></label>';
    $data .= '<textarea class="u-full-width" name="text" style="height: 120px !important;">'.$sys_share_phrase.'</textarea>';
    $data .= '<a class="button button-twitter" onclick="document.update_twitter.submit();">'.__("Tweet","sys").'</a>';
    $data .= '<input name="update_twitter" value="1" type="hidden">';
    $data .= '<input name="base64" value="'.$base64.'" type="hidden">';
    $data .= '<input name="url_path" value="'.$url_path.'" type="hidden">';
    $data .= '</form>';
    $data .= '</div>';
    $data .= '</div>';
    $data .= '</div>';

    return $data;
  }

  /**
  * Function name: base64_encode_image()
  * Function to convert image to base64 for Twitter
  * Parameters: $filename, $filetype
  */

  public function base64_encode_image ($filename=string,$filetype=string) {
    if ($filename) {
      $imgbinary = fread(fopen($filename, "r"), filesize($filename));
      return @base64_encode($imgbinary);
    }// End if
  }

  /**
  * Function name: twitter_auth()
  * Function to for Twitter login authorization
  * Parameters: none
  */

  public function twitter_auth(){
    global $post;
    $pid = $post->ID;
    $url = get_permalink($pid);

    $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
    $request_token = $connection->getRequestToken(TWITTER_OAUTH_CALLBACK);
    $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
    
    switch ($connection->http_code) {
      case 200:
        $url = $connection->getAuthorizeURL($token);
        return $url;
        break;
    
      default:
        $message = __('There was a problem connecting to Twitter, Click <a href="'.$url.'">here</a> to Try again!','sys');
        return $message;
    }// End Switch Case
  }

  /**
  * Function name: update_fb()
  * Function for post profile picture displayed on Facebook with message
  * Parameters: $text, $token, $path, $fb
  */

  public function update_fb($text, $token, $path, $fb){

    //Upload image
    $url = $this->upload_image_to_fb($path,$token,$fb,$text);

    $link_array = explode('/',$path);
    $image = end($link_array);
    $url_path = SYS_URL_PATH."cache/".$image;
    $twitter_url   = get_permalink( $post->ID ).'?method=tauth';
    $make_profile_pic = SYS_URL_PATH."images/make_profile_pic.png";

    $data  = '<div class="container">';
    $data .= '<div class="row">';
    $data .= '<div class="header">';
    $data .= '<h1>'.__("Thank you for your support!","sys").'</h1>';
    $data .= '<img class="profile" src="'.$url_path.'" alt="">';
    $data .= '</div>';
    $data .= '<div class="content">';
    $data .= '<br/>';
    $data .= __("Your new profile picture has been added to an album in your Facebook photos called <strong>'".esc_attr( get_option('sys_facebook_album_name'))."'</strong>. Click the button below to set the image as your profile picture (Facebook doesn't allow us to do this for you).","sys");
    $data .= '<br/>';
    $data .= __("<em>example:</em>&nbsp;","sys");
    $data .= '<img src="'.$make_profile_pic.'" alt="Make your Profile Picture"/><br/><br/>';
    $data .= '</div>';
    $data .= '<a class="button button-primary" href="'.htmlspecialchars($url).'" target="_blank" style="color:#fff !important;">'. __("Make your profile picture!","sys").'</a>';
    $data .= '<br/>'.__("OR","sys").'<br/>';
    $data .= '<a class="button button-twitter" href="'.htmlspecialchars($twitter_url).'">'. __("Sign in with Twitter","sys").'</a>';
    $data .= '</div>';
    $data .= '</div>';
    
    return $data;
    session_write_close();
  }

  /**
  * Function name: upload_image_to_fb()
  * Function to upload profile picture on Facebook with message
  * Parameters: $text, $token, $path, $fb
  */

  public function upload_image_to_fb($path,$token,$fb,$text)
  {

    $image = [
      'caption' => $text,
      'source' => $fb->fileToUpload($path),
    ];
    try {
      // Returns a `Facebook\FacebookResponse` object
      $response = $fb->post('/me/photos', $image, $token);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Update FB Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    $graphNode = $response->getGraphNode();

    if(isset($graphNode["id"]) && is_numeric($graphNode["id"]))
      {
        $url = "https://www.facebook.com/photo.php?fbid=".$graphNode['id']."&type=3&makeprofile=1";
      }

    return $url;
  }

  /**
  * Function name: overlay()
  * Function to add overlay image
  * Parameters: none
  */

  public function overlay(){

    global $post;
    $pid = $post->ID;
    $token = $_SESSION['facebook_access_token'];
    $output = $this->curl_access_fb_data($token);
    $r = @json_decode($output, true);
    $id = $r['id'];
    $path = SYS_DIR_PATH."cache/".$id.".jpg";
    $url_path = SYS_URL_PATH."cache/".$id.".jpg";
    $_SESSION['path'] = $path;
    $_SESSION['id'] = $id;

  	//create temp image
    $service = 'fb';
    $this->create_temp_image($id, $path, $service);
  	
    $sys_share_phrase = esc_textarea( get_option('sys_share_phrase') );
    $data  = '<div class="container">';
    $data .= '<div class="row">';
    $data .= '<div class="header">';
    $data .= '<h1>'. __("Your new profile picture is ready!","sys").'</h1>';
    $data .= '<img class="profile" src="'. $url_path.'" alt="">';
    $data .= '</div>';
    $data .= '<div class="content" style="margin-bottom:15px;">'; 
    $data .= '<form action="'.esc_attr(get_permalink($pid)).'" method="post" name="update_facebook">';
    $data .= '<label for="update"><h2>'.__("Add a supporting phrase to your timeline:","sys").'</h2></label>';
    $data .= '<textarea class="u-full-width" name="text" style="height: 120px !important;">'.$sys_share_phrase.'</textarea>';
    $data .= '<a class="button button-primary" onclick="document.update_facebook.submit();">'.__("Share on Facebook","sys").'</a>';
    $data .= '<input name="update_fb" value="1" type="hidden">';
    $data .= '<input name="token" value="'.$token.'" type="hidden">';
    $data .= '<input name="path" value="'.$url_path.'" type="hidden">';
    $data .= '</form>';
    $data .= '</div>';
    $data .= '</div>';
    $data .= '</div>';

    return $data;
  }

  /**
  * Function name: curl_access_fb_data();
  * Function to connect to FB using curl
  * Parameters: $token
  */

  public function curl_access_fb_data($token){

    $ch = @curl_init();
    @curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?access_token=".$token);
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = @curl_exec($ch);
    @curl_close($ch);

    return $output;
  }

  /**
  * Function name: get_fb_photo();
  * Function to connect to FB for fetching photo using curl
  * Parameters: $url
  */

  public function get_fb_photo($url){
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_URL, $url);
     $result = curl_exec($ch);
     curl_close($ch);
     return $result;
  }

  /**
  * Function name: create_temp_image();
  * Function to create tempory image
  * Parameters: $id, $path, $service
  */

  public function create_temp_image($id, $path, $service){
    $base_image = @imagecreatefrompng(SYS_DIR_PATH."images/template480.png");

    if($service == 'fb'){
     $photo_url = "http://graph.facebook.com/".$id."/picture?width=480&height=480&redirect=false";
     $result = $this->get_fb_photo($photo_url);
     $photo_object = (json_decode($result, true));
     $photo = $photo_object['data']['url'];
     $this->grab_image($photo, $path);
    }

    $this->resizeImage($path, 480, 480 );
    
    $photo = @imagecreatefromjpeg($path);
    $overlay_image_link = @esc_attr( get_option('sys_overlay_image'));
    $link_array = @explode('/',$overlay_image_link);
    $overlay_image = @end($link_array);
    $overlay = @imagecreatefrompng(SYS_DIR_PATH."images/".$overlay_image);
    @imagesavealpha($base_image, true);
    @imagealphablending($base_image, true);

    if(file_exists($path)){
      @unlink($path);
    }

    @imagecopyresampled($base_image, $photo, 0, 0, 0, 0, 480, 480, 480, 480);
    @imagecopy($base_image, $overlay, 0, 0, 0, 0, 480, 480);
    @imagejpeg($base_image, $path);
  }

  /**
  * Function name: resizeImage();
  * Function to resizeImage
  * Parameters: $filename, $max_width, $max_height
  */

  public function resizeImage($filename, $max_width, $max_height){

    @list($orig_width, $orig_height) = @getimagesize($filename);

    $width = $orig_width;
    $height = $orig_height;

    # taller
    if ($height < 480) {
      $width = ($max_height / $height) * $width;
      $height = $max_height;
    }

    # wider
    if ($width < 480) {
      $height = ($max_width / $width) * $height;
      $width = $max_width;
    }

    $image_p = imagecreatetruecolor($width, $height);

    $info = @getimagesize($filename);
    $extension = @image_type_to_extension($info[2]);

    switch ($extension) {
      case '.gif':
        $image = @imagecreatefromgif($filename);
        break;
      
      case '.jpeg':
        $image = @imagecreatefromjpeg($filename);
        break;
      
      case '.jpg':
        $image = @imagecreatefromjpeg($filename);
        break;
      
      case '.png':
        $image = @imagecreatefrompng($filename);
        break;
    }//End Switch Case

    if(file_exists($filename)){
      unlink($filename);
    }

    @imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

    @imagejpeg($image_p, $filename);
  }

  /**
  * Function name: deleteCache();
  * Function to remove Cache
  * Parameters: $dir
  */

  public function deleteCache($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
           rrmdir($dir."/".$object);
         else
           unlink($dir."/".$object); 
       } 
     } 
   } 
   return 1;
  }

  /**
  * Function name: countfiles();
  * Function to count image files in Cache directory
  * Parameters: $dir
  */

  public function countfiles($dir) {

  $count = 0;
  if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
          $count++;
         else
          $count++; 
       } 
     } 
   } 
   return $count;
  }

}// end of Class wpSysSocial

//Create object for wpSysSocial Class
$wpSysSocial = new wpSysSocial();