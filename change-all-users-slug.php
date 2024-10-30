<?php
/*
Plugin Name: Change All Users Slug
Plugin URI: http://wordpress.org/plugins/change-all-users-slug/
Description: A small plugin which helps you to change all your user's slug in "/firstname-lastname/" (lowercase) format.
Version: 1.0
Author: Grávuj Miklós Henrich
Author URI: http://www.henrich.ro
*/

define( 'CAUS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FORM_ACTION', str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ) );

function caus() {
	$_POST = array_map( 'sanitize_text_field', $_POST );
	$user_list_header = '
    <table cellspacing="0" class="wp-list-table widefat fixed users">
        <thead>
            <tr>
                <th style="" class="manage-column column-username" id="username" scope="col">
                    Username
                </th>
                <th style="" class="manage-column column-name" id="name" scope="col">
                    Display Name
                </th>
				<th style="" class="manage-column column-name" id="nicename" scope="col">
                    Nicename
                </th>
                <th style="" class="manage-column column-email" id="email" scope="col">
                    E-mail
                </th>
                <th style="" class="manage-column column-role" id="role" scope="col">
                    Role
                </th>
                <th style="" class="manage-column column-posts num" id="posts" scope="col">
                    Date Registered
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th style="" class="manage-column column-username" id="username" scope="col">
                    Username
                </th>
                <th style="" class="manage-column column-name" id="name" scope="col">
                    Display Name
                </th>
				<th style="" class="manage-column column-name" id="nicename" scope="col">
                    Nicename
                </th>
                <th style="" class="manage-column column-email" id="email" scope="col">
                    E-mail
                </th>
                <th style="" class="manage-column column-role" id="role" scope="col">
                    Role
                </th>
                <th style="" class="manage-column column-posts num" id="posts" scope="col">
                    Date Registered
                </th>
            </tr>
        </tfoot>	
	';
	$un = 0;
	foreach ( get_users() as $user ) {
		if ( $user->data->user_status == 0 && $user->data->user_nicename == $user->data->user_login ) {
			$user_ids[] = $user->ID;
			$user_nicenames[] = $user->data->user_nicename;
			if ( $user->data->display_name == $user->data->user_login ) {
				$badclass = '<strong class="red">?</strong>';
			} else {
				$badclass = '';
			}
			$user_list[] = '
            <tbody data-wp-lists="list:user" id="the-list">
                <tr class="alternate" id="user-'.$user->ID.'">
                    <td class="username column-username">
                        '.$user->data->user_login.'
                    </td>
                    <td class="name column-name">
                        '.$user->data->display_name.' '.$badclass.'
                    </td>
					<td class="nicename column-name">
                        <strong>'.$user->data->user_nicename.'</strong>
                    </td>
                    <td class="email column-email">
                        '.$user->data->user_email.'
                    </td>
                    <td class="role column-role">
                        '.$user->roles[0].'
                    </td>
                    <td class="posts column-posts num">
                        '.$user->data->user_registered.'
                    </td>
                </tr>
            </tbody>
			';
			if ( $user->data->display_name == $user->data->user_login ) {
				$similar_display_names[] = true;
			}
			$un++;
		}
	}
	echo '</table>';
	if ( $un != 0 ) {
		$users_num = ' <em class="small_caus">(There are <strong class="red">'.$un.'</strong> users with <u>wrong</u> slug)</em>';
	} else {
		$users_num = '';
	}
	$msg = array(
		'title'			=> 'Change All Users Slug',
		'note'			=> '<strong>Note:</strong> After checking the box below and click on change button, all listed users from this page will have the <span class="red">"</span><strong>/firstname-lastname/</strong><span class="red">"</span> slug format too.',
		'specific_note'	=> '<strong>Warning:</strong> It seems one of your users don\'t have a <em>Display Name</em> defined manually or it is the same as the <em>Username</em>. <br />
							The <em>Display Name</em> of a user can be changed from <u>"Edit User" -> "Name"</u> section. <br />
							The purpose of this plugin is to create a slug something like <span class="green">"</span><strong>/firstname-lastname/</strong><span class="green">"</span>. <br />
							If you are okay having users slug just like their usernames, then you don\'t need to use this plugin.',
		'request'		=> 'Please check the checkbox below, if you would like to change all the users slugs.',
		'success'		=> 'Everybody has the <span class="red">"</span><strong>/firstname-lastname/</strong><span class="red">"</span> slug format now. Good job!',
		'error'			=> 'There was an error while changing all user slugs, please try again. It seems one, or more, of your users don\'t have a <em>Display Name</em> defined manually or it is the same as the <em>Username</em>.',
		'good'			=> 'Everybody has the <span class="red">"</span><strong>/firstname-lastname/</strong><span class="red">"</span> slug format now. Nothing else to do here.',
		'form'			=> '<form name="caus_form" method="post" action="'.FORM_ACTION.'">
							<input type="hidden" name="caus" value="Y">
							'.wp_nonce_field('caus-nonce').'
							<input type="checkbox" name="change" value="Y" class="button-checkbox" /> Change all users slug?
							<input type="submit" value="Change" class="button-primary" />
						</form>'
	);
	?>
    <div class="wrap">
    	<?php
		$nonce = $_REQUEST['_wpnonce'];
		if ( $_POST['caus'] == 'Y' && $_POST['change'] == 'Y' && wp_verify_nonce( $nonce, 'caus-nonce' ) ) {
			echo '<h2>'.$msg['title'].'</h2>';
			foreach( $user_ids as $uid ) {
				$info = get_userdata( $uid );
				$display_name = $info->data->display_name;
				if ($display_name) {
					$args = array(
						'ID'            => $uid,
						'user_nicename' => strtolower(sanitize_title($display_name))
					);
					if ( wp_update_user( $args ) ) {
						$un = 0;
					} else {
						$un = 3;
					}
				} else {
					$un = 5;
				}
			}
			if ( $un == 0 ) {
				if ( $similar_display_names != true ) {
					echo '<div class="updated"><p>'.$msg['success'].'</p></div>';
				} else {
					echo '<div class="updated"><p>'.$msg['note'].'</p></div>';
					if ( $similar_display_names ) {
						echo '<div class="error"><p>'.$msg['specific_note'].'</p></div>';
					}
					echo '<div class="error"><p>'.$msg['error'].'</p></div>';
					echo $user_list_header;
					foreach ( $user_list as $ul ) {
						echo $ul;
					}
					echo $msg['form'];
				}
			}
		} elseif ( $_POST['caus'] == 'Y' && $_POST['change'] != 'Y' && wp_verify_nonce( $nonce, 'caus-nonce' ) ) {
			echo '<h2>'.$msg['title'].$users_num.'</h2>';
			echo '<div class="updated"><p>'.$msg['note'].'</p></div>';
			if ( $similar_display_names ) {
				echo '<div class="error"><p>'.$msg['specific_note'].'</p></div>';
			}
			echo '<div class="error"><p>'.$msg['request'].'</p></div>';
			echo $user_list_header;
				foreach ( $user_list as $ul ) {
					echo $ul;
				}
			echo $msg['form'];
		} else {
			echo '<h2>'.$msg['title'].$users_num.'</h2>';
			if( $un > 0 ) {
				echo $user_list_header;
				foreach ( $user_list as $ul ) {
					echo $ul;
				}
				echo '<div class="updated"><p>'.$msg['note'].'</p></div>';
				if ( $similar_display_names ) {
					echo '<div class="error"><p>'.$msg['specific_note'].'</p></div>';
				}
				echo $msg['form'];
			} else {
				echo '<div class="updated"><p>'.$msg['good'].'</p></div>';
			}
		}
		?>
    </div><!-- .wrap -->
<?php
}

if( !function_exists( load_sortable_user_meta_columns ) ) {
	add_action('admin_init', 'load_sortable_user_meta_columns');
	function load_sortable_user_meta_columns(){
		$args = array(
			'user_nicename'		=> 'User Slug'
		);
		new sortable_user_meta_columns($args);
	}
}
if(	!class_exists( sortable_user_meta_columns ) ) :
	class sortable_user_meta_columns {
		var $defaults = array(
			'nicename', 
			'email', 
			'url', 
			'registered', 
			'user_nicename', 
			'user_email', 
			'user_url', 
			'user_registered', 
			'display_name', 
			'name', 
			'post_count', 
			'ID', 
			'id', 
			'user_login'
		);
		function __construct( $args ) {
			$this->args = $args;
			add_action( 'pre_user_query', array( &$this, 'query' ) );
			add_action( 'manage_users_custom_column',  array( &$this, 'content' ), 10, 3 );
			add_filter( 'manage_users_columns', array( &$this, 'columns' ) );
			add_filter( 'manage_users_sortable_columns', array( &$this, 'sortable') );
		}
		function query( $query ) {
			$vars = $query->query_vars;
			if ( in_array( $vars['orderby'], $this->defaults ) ) return;
			$title = $this->args[$vars['orderby']];
			if( !empty( $title ) ) {
				$query->query_from .= " LEFT JOIN wp_usermeta m ON (wp_users.ID = m.user_id  AND m.meta_key = '$vars[orderby]')";
				$query->query_orderby = "ORDER BY m.meta_value ".$vars['order'];
			}
		}
		function columns( $columns ) {
			foreach( $this->args as $key=>$value ) {
				$columns[$key] = $value;
			}
			return $columns;
		}
		function sortable( $columns ) {
			foreach($this->args as $key=>$value) {
				$columns[$key] = $key;
			}
			return $columns;
		}
		function content( $value, $column_name, $user_id ) {
			$user = get_userdata( $user_id );
			return $user->$column_name;
		}
	}
endif;

function caus_css() {
	wp_register_style( 'caus.css', CAUS_PLUGIN_URL . 'caus.css', array(), '1.0' );
	wp_enqueue_style( 'caus.css' );
}

function register_caus() {
	add_users_page(
		'Change All Users Slug', 
		'Users Slug Setup', 
		'manage_options', 
		'change-all-users-slug', 
		'caus_callback'
	);
}

function caus_callback() {
	echo '<div class="icon32" id="icon-users"><br></div>';
	caus();
}

add_action( 'admin_enqueue_scripts', 'caus_css' );
add_action( 'admin_menu', 'register_caus' );
?>