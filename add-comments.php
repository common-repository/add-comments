<?php
/*
Plugin Name: Add Comments
Plugin URI: http://shinraholdings.com/plugins/add-comments
Description: Add multiple comments at once to a post or page. Also allows you to specify any author information you would like.
Version: 1.0.1
Author: bitacre
Author URI: http://shinraholdings.com
License: GPLv2 
	Copyright 2012 Shinra Web Holdings (http://shinraholdings.com)
*/

function addComments_set_plugin_meta( $links, $file ) { 
// defines additional plugin meta links (appearing under plugin on Plugins page)
	$plugin_base = plugin_basename(__FILE__);
    if ( $file == $plugin_base ) {
		$newlinks = array( '<a href="options-general.php?page=addComments">Add Comments</a>' ); 
		return array_merge( $links, $newlinks ); // merge new links into existing $links
	}
	return $links;
}

function addComments_options_init() { 
// adds plugin's options to white list
	register_setting( 'addComments-group', 'addComments-options', 'addComments_validate_input' );
}

function addComments_validate_input( $input ) {
	return $input;
}

function addComments_options_link() { 
// adds link to plugin's settings page under 'settings' on the admin menu 
	add_options_page( 'Add Comments', 'Add Comments', 'manage_options', 'addComments', 'addComments_options_page' );
}

function addComment_select_box( $posttype = 'post' ) {
	
	if( $posttype == 'page' )
		$args = array( 
			'number'		=>	'',
			'post_type'		=>	'page',
			'post_status'	=>	'all' );
	
	else
	$args = array( 
		'numberposts'	=>	'9001',
		'post_type'		=>	'post',
		'post_status'	=>	'all' );
	
	$items = get_posts( $args );
	
	echo '<select name="' . $posttype . 's_list" id="' . $posttype . '">';
	
	foreach( $items as $item )
		echo '<option value="' . $item->ID . '">' . $item->post_title . '</option>';
}

function addComment_text_box( $label, $name, $default=NULL ) { ?>
					<tr>
						<td><label for="<?php echo $name ?>"><?php echo $label ?></label></th>
						<td colspan="2"><input type="text" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $default; ?>"/></td>
					</tr>
<?php 
}

function addComments_add_comments( $id, $author, $email, $url, $ip, $comments ) {

	$explodes = explode( '%*%', $comments );
	if( empty( $explodes ) ) return false;

	foreach( $explodes as $comment ) 							
wp_insert_comment( array(
    'comment_post_ID' => $id,
    'comment_author' => $author,
    'comment_author_email' => $email,
    'comment_author_url' => $url,
    'comment_content' => $comment,
    'comment_type' => '',
    'comment_parent' => 0,
    'user_id' => '',
    'comment_author_IP' => $ip,
    'comment_agent' => 'Add Comments Plugin - http://shinraholdings.com/plugins/add-comments',
    'comment_date' => current_time( 'mysql' ),
    'comment_approved' => 1 ) );

	return count( $explodes );
}

function addComments_options_page() { 
	
	if( $_POST['action'] == 'addcomments' ) {
		echo '<div class="updated settings-error">';
		$id = ( $_POST['post_or_page'] == 'page' ? $_POST['pages_list'] : $_POST['posts_list'] );
		$author = ( isset( $_POST['author_name'] ) ? $_POST['author_name'] : 'annonymous' );
		$email = ( isset( $_POST['author_email'] ) ? $_POST['author_email'] : get_bloginfo('admin_email' ) );
		$url = ( isset( $_POST['author_url'] ) ? $_POST['author_url'] : '' );
		$ip = ( isset( $_POST['author_ip'] ) ? $_POST['author_ip'] : '127.0.0.1' );
		echo addComments_add_comments( $id, $author, $email, $url, $ip, $_POST['comment'] ) 
		. ' comments added to ' . get_the_title( $id ) . '</div>';
	} ?>

	<div class="wrap">
    	<div class="icon32" id="icon-options-general"><br /></div>
		<h2>Add Comments</h2>
		
	
		
		<form method="post" action="">
			
			<table class="form-table">
				<tbody>
					
					<tr><th colspan="3" style="font-size:1.3em">Add where?</th></tr>

					<tr>
						<td><strong>Post</strong></td>
						<td><input type="radio" name="post_or_page" value="post" id="post" checked="checked" /></td>
						<td><?php addComment_select_box( 'post' ); ?></td>
					</tr>
					
					<tr>
						<td><strong>Page</strong></td>
						<td><input type="radio" name="post_or_page" value="page" id="page" /></td>
						<td><?php addComment_select_box( 'page' ); ?></td>
					</tr>

					<tr><th colspan="3" style="font-size:1.3em">Comment author information?</th></tr>
					
					<?php 
					$authors = array(
						array( 'name' => 'author_name', 'label' => 'Author Name', 'default' => 'rss feed comments' ),
						array( 'name' => 'author_email', 'label' => 'Author Email', 'default' => get_bloginfo('admin_email' ) ),
						array( 'name' => 'author_url', 'label' => 'Author URL', 'default' => '' ),
						array( 'name' => 'author_ip', 'label' => 'Author IP', 'default' => '127.0.0.1' ) ); 
						
					foreach( $authors as $author )
						addComment_text_box( $author['label'], $author['name'], $author['default'] );
					?>
					
					<tr>
						<th colspan="2" style="font-size:1.3em">Comments</th>
						<td>(seperate comments with %*%)</td>
					</tr>
					
					<tr>
						<td colspan="3"><textarea name="comment" id="comment" cols="120" rows="10">%*%</textarea></td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="hidden" name="action" value="addcomments" />
				<input type="submit" class="button-primary" value="Add Comments" />
			</p>
		</form>
	</div>
<?php } 

add_filter( 'plugin_row_meta', 'addComments_set_plugin_meta', 10, 2 ); // add plugin page meta links
add_action( 'admin_init', 'addComments_options_init' ); // whitelist options page
add_action( 'admin_menu', 'addComments_options_link' ); // add link to plugin's settings page in 'settings' menu
?>
