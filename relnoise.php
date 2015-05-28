<?php
ob_start();
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
* Plugin Name: Relnoise
* Description: It has two fields Feed ID and Access Token. put this [relatednoise] shortcode into page.
* Version: 1.0
* Author: BizBrolly
* Author URI: http://BizBrolly.com
*/

//action hook for plugin activation
register_activation_hook( __FILE__, 'callback_plugin' );

//callback function
function callback_plugin(){
global $wpdb;
$table_name = $wpdb->prefix . "relatednoise"; 
if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $sql = "CREATE TABLE $table_name (
      `id` int NOT NULL AUTO_INCREMENT,
      `feed_id` text NOT NULL,
	  `access_token` text NOT NULL,
		UNIQUE KEY id (id)
    );";
    //reference to upgrade.php file
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
}

?>
<?php
/********** Insert related noise form data into database table ***********/
$submit_button = isset($_POST['submit_button'])?sanitize_text_field($_POST['submit_button']):'';
if($submit_button!=''){ 
 global $wpdb;
 $accessToken = sanitize_text_field($_POST['access_token']);
 $feed_id = sanitize_text_field($_POST['feed_id']);
 $table_name = $wpdb->prefix . 'relatednoise';
 $myrows = $wpdb->get_results( "SELECT * FROM $table_name" );
 if($accessToken!='' && $feed_id!=''){ // emptiness validation check start
 $accesLen = strlen( $accessToken );
 $feedLen = strlen( $feed_id );
if( ( $accesLen >= 30 && $accesLen <=40) && ( $feedLen >= 30 && $feedLen <=40) ){ // maximum length validation check start	 
 if($submit_button=='Submit' && !$myrows){
$isertmsg = $wpdb->insert( $table_name,
array( 'feed_id' => $feed_id,
'access_token' => $accessToken),
array('%s','%s'));
if($isertmsg){
$relnoisemsg = '<span class="relnoise-msg" style="color:limegreen;">Record has been Inserted Successfully!</span>';
$_SESSION['relnoisemsg']=$relnoisemsg;
}
}
if($submit_button=='Update'){
	$id = sanitize_text_field($_POST['id']);
	$upmsg = $wpdb->query($wpdb->prepare("UPDATE $table_name SET feed_id='".$feed_id."',access_token='".$accessToken."' WHERE id = %d",$id));
	if($upmsg){
	$relnoisemsg = '<span class="relnoise-msg" style="color:limegreen;">Record has been Updated Successfully!</span>';
	$_SESSION['relnoisemsg']=$relnoisemsg;
	}
}
 
 }else{ // maximum length validation check end
	 $relnoisemsg = '<span class="relnoise-msg" style="color:red;">Characters Length of Access Token and Feed ID  must be between 30 to 40.</span>';
	$_SESSION['relnoisemsg']=$relnoisemsg;
 }
 }else{ // emptiness validation check end
	 $relnoisemsg = '<span class="relnoise-msg" style="color:red;">Access Token and Feed ID must not be Blank!</span>';
	$_SESSION['relnoisemsg']=$relnoisemsg;
 }
} 

/********** Delete  related noise selected record from database table where "id" matched start***********/
$DeleteShortcode = isset($_REQUEST['DeleteShortcode'])?sanitize_text_field($_REQUEST['DeleteShortcode']):'';
if($DeleteShortcode!=''){
global $wpdb;
$table_name = $wpdb->prefix . 'relatednoise';
$delmsg = $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id = %d",$DeleteShortcode));	
if($delmsg){
$relnoisemsg = '<span class="relnoise-msg" style="color:limegreen;">Record has been Deleted Successfully!</span>';
$_SESSION['relnoisemsg']=$relnoisemsg;
header('Location:'.$_SERVER['PHP_SELF'].'?page=noise-plugin');
}
}
/********** Delete  related noise selected record from database table where "id" matched end***********/
?>
<?php 
/******************* Generate Shortcode [relatednoise] Start *************************/
function form_creation(){
global $wpdb;
$table_name = $wpdb->prefix . 'relatednoise';
$helloworld_id = $wpdb->get_results("SELECT * FROM $table_name");
$accessID = $helloworld_id[0]->access_token; 
$feedID = $helloworld_id[0]->feed_id;
 ?>
<script type="text/javascript" id="<?php echo $accessID?>">
(function(d, s, t, f){
 var ss = f.substring(0,5);
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(ss)) {return;}
 js = d.createElement(s); js.id = ss;
 js.src = [ 'https:', '', 'relatednoise.com', 'loader', f, t].join('/');
 fjs.parentNode.insertBefore(js, fjs);
}(document, 'script',
"<?php echo $feedID;?>",
"<?php echo $accessID;?>"));
</script>
<?php } 
add_shortcode('relatednoise', 'form_creation');
/******************* Generate Shortcode [relatednoise] End *************************/
?>
<?php 
/********** plug-in set up function for admin ***********/
add_action('admin_menu', 'noise_plugin_setup_menu'); 
function noise_plugin_setup_menu(){
		
        add_menu_page( 'Noise Plugin Page', 'Related Noise', 'manage_options', 'noise-plugin', 'noise_init' );
}
function noise_init(){
?>
<div class="realated-noise-main">
<h2>Related Noise <?php echo isset($_SESSION['relnoisemsg'])?$_SESSION['relnoisemsg']:'';?></h2> 
<?php 
global $wpdb;
$table_name = $wpdb->prefix . 'relatednoise';
$myrows = $wpdb->get_results( "SELECT * FROM $table_name" );
if($myrows){
?>

<table class="wp-list-table widefat fixed users">
<thead>
<tr>
<th class="manage-column column-name">Feed ID</th>
<th class="manage-column column-name">Access Token</th>
<th class="manage-column column-name">Shortcode</th>
<th class="manage-column column-name">Action</th>
<th class="manage-column column-name">
<img width="35" src="<?php echo plugins_url('img/Related_Noise.jpg', __FILE__);?>"/>
</th>
</tr>
</thead>
<?php foreach($myrows as $rows){ ?>
<tr>
<td><?php echo $rows->feed_id;?></td>
<td><?php echo $rows->access_token;?></td>
<td>[relatednoise]</td>
<td>
<a href="<?php echo $_SERVER['PHP_SELF'].'?page=noise-plugin&EditShortcode='.$rows->id; ?>">Edit</a> |
<a href="<?php echo $_SERVER['PHP_SELF'].'?page=noise-plugin&DeleteShortcode='.$rows->id;?>" onclick="return confirm('Are you sure?')">Delete</a> 
</td>
</tr>
<?php } ?>
<tfoot>
<tr>
<th class="manage-column column-name">Feed ID</th>
<th class="manage-column column-name">Access Token</th>
<th class="manage-column column-name">Shortcode</th>
<th class="manage-column column-name">Action</th>
<th class="manage-column column-name">
<img width="35" src="<?php echo plugins_url('img/Related_Noise.jpg', __FILE__);?>"/>
</th>
</tr>
</tfoot>
</table>

<?php } 

/********** Update related noise selected record where "id" matched start***********/
$EditShortcode = isset($_REQUEST['EditShortcode'])?sanitize_text_field($_REQUEST['EditShortcode']):'';
$record='';
if($EditShortcode!=''){
global $wpdb;
$table_name = $wpdb->prefix . 'relatednoise';
$record = $wpdb->get_results( "SELECT * FROM $table_name WHERE id='".$EditShortcode."'" );
}
/********** Update related noise selected record where "id" matched end***********/
?>
<form action="" id="access_feed" method="POST">
<input type="hidden" name="id" value="<?php echo isset($record[0])?$record[0]->id:'';?>"/>
<div class="row">
<span>Feed ID:</span> <input type="text" name="feed_id" id="feed_id" value="<?php echo isset($record[0])?$record[0]->feed_id:'';?>" maxlength="40">
</div>
<div class="row">
<span>Access Token:</span> <input type="text" name="access_token" id="access_token" value="<?php echo isset($record[0])?$record[0]->access_token:'';?>" maxlength="40">
</div>
<div class="row">
<input type="submit" name="submit_button" Value="<?php echo isset($record[0])?'Update':'Submit';?>"/>
</div>
</form>
<div>
<?php
}

 add_action( 'admin_enqueue_scripts','enqueue_admin_styles');
 add_action( 'admin_enqueue_scripts','enqueue_admin_scripts');
function enqueue_admin_scripts() {
	//wp_register_script('script', plugins_url('js/jquery-latest.js.js', __FILE__), array('jquery'),'1.1', true);
	//wp_enqueue_script('script');
	
	wp_register_script('my_amazing_script', plugins_url('js/custom-jquery.js', __FILE__), array('jquery'),'1.1', true);
	wp_enqueue_script('my_amazing_script');
}

function enqueue_admin_styles() {
	wp_enqueue_style( 'style', plugins_url('css/custom-style.css', __FILE__),array());
}
?>