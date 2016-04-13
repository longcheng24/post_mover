<?php
/*
Plugin Name: Post Mover
Plugin URI: http://www.longcheng24.com  
Description: duplicate Regular post type to custom post type 
Version: 1.0  
Author: Long Cheng  
Author URI: http://www.longcheng24.com  
License: LC  
*/ 



/* When Active */ 
//register_activation_hook(__FILE__,'ci_install');   
 
/* When Deactive */ 
//register_deactivation_hook( __FILE__, 'ci_remove' );  
 
/*function ci_install() {  
   
 }
function ci_remove() {  
    
}  */
/* if WordPress dashboard */ 
if( is_admin() ) {  
    /*  hook admin_menu  */ 
    add_action('admin_menu', 'permv_menu');  
}  
 
function permv_menu() {  
    /* add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);  */ 
    add_options_page('Welcome to Post Mover', 'Post Mover', 'administrator','post_mover', 'permv_html_page');  
} 
function permv_html_page() {  
?>  
    <div>  
        <h2>Post Mover</h2> 
        <p style="margin-top:30px"><p>
        <form method="post" name="perform">  
            <?php
				$args = array(
					   
				);
					
				$output = 'names'; // names or objects, note names is the default
				$operator = 'and'; // 'and' or 'or'
					
				$post_types = get_post_types( $args, $output, $operator );
				$post_categories = get_categories();
				
				
				function rd_duplicate_post_as_draft($post_id,$target){
					
					global $wpdb;
					
					$post = get_post( $post_id );
					$current_user = wp_get_current_user();
					$new_post_author = $current_user->ID;
					$args = array(
						'comment_status' => $post->comment_status,
						'ping_status'    => $post->ping_status,
						'post_author'    => $post->post_author, //$new_post_author
						'post_date'      => $post->post_date,
						'post_content'   => $post->post_content,
						'post_excerpt'   => $post->post_excerpt,
						'post_name'      => $post->post_name,
						'post_parent'    => $post->post_parent,
						'post_password'  => $post->post_password,
						'post_status'    => 'publish',
						'post_title'     => $post->post_title,
						'post_type'      => $target,
						'to_ping'        => $post->to_ping,
						'menu_order'     => $post->menu_order
					);
					
					$new_post_id = wp_insert_post( $args );
					$taxonomies = get_object_taxonomies($post->post_type); 
					foreach ($taxonomies as $taxonomy) {
						$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
						wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
					}
					$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
					if (count($post_meta_infos)!=0) {
						$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
						foreach ($post_meta_infos as $meta_info) {
							$meta_key = $meta_info->meta_key;
							$meta_value = addslashes($meta_info->meta_value);
							$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
						}
						$sql_query.= implode(" UNION ALL ", $sql_query_sel);
						$wpdb->query($sql_query);
					}
					
				}
				
				
				if ( 'Duplicate' == $_REQUEST['action'] ) {
					$from = $_REQUEST['pm_post_category'];
					$pt_from = $_REQUEST['pm_post_type_f'];
					$to = $_REQUEST['pm_post_type'];
					$argsloop = array('category_name' => $from,'posts_per_page' => -1,"post_type" => $pt_from);
					$allposts = wp_get_recent_posts( $argsloop );
					foreach($allposts as $post){
						rd_duplicate_post_as_draft($post["ID"],$to);
					}
				}
            	
					
				
				
            ?>
            <p>
            	<b>Choose a category you want to move from<b><br />
                <select name="pm_post_category" id="pm_post_category">
                	<?php
						foreach ($post_categories as $post_category){ ?>
                        <option value="<?php  echo $post_category->slug ?>"><?php echo $post_category->slug ?></option>
                        <?php }?>
                </select>
            </p>
            
            <p>
            	<b>Choose a post type you want to move from<b><br />
                <select name="pm_post_type_f" id="pm_post_type_f">
                	<?php
						foreach ($post_types as $post_type){ ?>
                        <option value="<?php  echo $post_type ?>"><?php echo $post_type ?></option>
                        <?php }?>
                </select>
            </p>
            
            <hr>
            
            <p>
            	<b>Choose a post type you want to move to<b><br />
                <select name="pm_post_type" id="pm_post_type">
                	<?php
						foreach ($post_types as $post_type){ ?>
                        <option value="<?php  echo $post_type ?>"><?php echo $post_type ?></option>
                        <?php }?>
                </select>
            </p>
 
            <p>  
                <input type="submit" name="action" value="Duplicate" class="button-primary" />
            </p>  
        </form>
        
    </div>  
<?php  
} 
?>  
