<?php
/**
 * Plugin Name: Wp Admin Log
 * Plugin URI: http://wp.xperts.club/
 * Description: Wprdpress admin activity log
 * Version: 1.0.0
 * Author: Kishore Chowdary
 * Author URI: http://www.xperts.club/
 * Requires at least: 4.4
 * Tested up to: 4.7
 *
 * Text Domain: wpadminlogs
 * Domain Path: /languages/
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WpAdminLogs' ) ) :


final class WpAdminLog{
	
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		
		add_action( 'activated_plugin', array($this,"activated_plugin"),10,2);
		add_action( 'deactivated_plugin',array($this,"deactivated_plugin"),10,2);
		add_action( 'deleted_plugin', array($this,"deleted_plugin"),10,2);
		
		
	}
	
	function get_plugin_details($plugin){
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		if(isset($all_plugins[$plugin])) return $all_plugins[$plugin];
		else return "";
	}
	
	public static function install() {
		global $wpdb;
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		
		$table= " CREATE TABLE {$wpdb->prefix}wp_logs (  id bigint(20) NOT NULL AUTO_INCREMENT,  user_ip char(32) NOT NULL,  user_name char(32) NOT NULL,  plugin_name char(32) NOT NULL, plugin_task char(32) NOT NULL, access_date datetime NULL default null, details longtext NULL,PRIMARY KEY  (id) ) $collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $table );
		
		
	}
	
	public function activated_plugin($plugin, $network_wide){
		global $wpdb;
		$table_name = $wpdb->prefix . 'wp_logs';
		
		$user_ip = $this->get_the_user_ip();
		$current_user = wp_get_current_user();
		$plugin_data = $this->get_plugin_details($plugin);

		$wpdb->insert( 
			$table_name, 
			array( 
				'user_ip' =>		$user_ip, 
				'user_name' =>  	$current_user->user_login, 
				'plugin_name' => 	$plugin,
				'plugin_task'=>		'activation',
				'access_date' => 	current_time( 'mysql' ),
				'details' => 		maybe_serialize($plugin_data),
			) 
		);
	}
	
	public function deactivated_plugin($plugin, $network_deactivating){
		global $wpdb;
		$table_name = $wpdb->prefix . 'wp_logs';
		
		$user_ip = $this->get_the_user_ip();
		$current_user = wp_get_current_user();
		$plugin_data = $this->get_plugin_details($plugin);

		$wpdb->insert( 
			$table_name, 
			array( 
				'user_ip' =>		$user_ip, 
				'user_name' =>  	$current_user->user_login, 
				'plugin_name' => 	$plugin,
				'plugin_task'=>		'deactivation',
				'access_date' => 	current_time( 'mysql' ),
				'details' => 		maybe_serialize($plugin_data),
			) 
		);
	}
	
	public function deleted_plugin($plugin,$deleted){
		global $wpdb;
		$table_name = $wpdb->prefix . 'wp_logs';
		
		$user_ip = $this->get_the_user_ip();
		$current_user = wp_get_current_user();
		$plugin_data = $this->get_plugin_details($plugin);

		$wpdb->insert( 
			$table_name, 
			array( 
				'user_ip' =>		$user_ip, 
				'user_name' =>  	$current_user->user_login, 
				'plugin_name' => 	$plugin,
				'plugin_task'=>		'deletion',
				'access_date' => 	current_time( 'mysql' ),
				'details' => 		maybe_serialize($plugin_data),
			) 
		);
	}
	
	
	


	function get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return apply_filters( 'wp_admin_log_get_ip', $ip );
	}

	
}

new WpAdminLog();

endif;
