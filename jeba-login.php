<?php
/*
Plugin Name: Jeba Limit Login Attempts
Plugin URI: http://prowpexpert.com
Description: This is Jeba Limit Login Attempts wordpress plugin. WordPress Login security and problems when a hacker use a script to guess the username / password. So I did this following class which lock the system for 30 minutes if an user attempt to login and fail after 3 try.
Author: Md Jahed
Version: 1.0
Author URI: http://prowpexpert.com/
*/
function jeba_wp_latest_jquery_d() {
	wp_enqueue_script('jquery');
}
add_action('init', 'jeba_wp_latest_jquery_d');
if ( ! class_exists( 'Jeba_Limit_Login_Attempts' ) ) {
    class Jeba_Limit_Login_Attempts {

        var $failed_login_limit = 3;                    //Number of authentification accepted
        var $lockout_duration   = 1800;                 //Stop authentification process for 30 minutes: 60*30 = 1800
        var $transient_name     = 'attempted_login';    //Transient used

        public function __construct() {
            add_filter( 'authenticate', array( $this, 'check_attempted_login' ), 30, 3 );
            add_action( 'wp_login_failed', array( $this, 'login_failed' ), 10, 1 );
        }
        public function check_attempted_login( $user, $username, $password ) {
            if ( get_transient( $this->transient_name ) ) {
                $datas = get_transient( $this->transient_name );

                if ( $datas['tried'] >= $this->failed_login_limit ) {
                    $until = get_option( '_transient_timeout_' . $this->transient_name );
                    $time = $this->when( $until );

                    //Display error message to the user when limit is reached 
                    return new WP_Error( 'too_many_tried', sprintf( __( '<strong>ERROR</strong>: You have reached authentification limit, you will be able to try again in %1$s.' ) , $time ) );
                }
            }

            return $user;
        }

        public function login_failed( $username ) {
            if ( get_transient( $this->transient_name ) ) {
                $datas = get_transient( $this->transient_name );
                $datas['tried']++;

                if ( $datas['tried'] <= $this->failed_login_limit )
                    set_transient( $this->transient_name, $datas , $this->lockout_duration );
            } else {
                $datas = array(
                    'tried'     => 1
                );
                set_transient( $this->transient_name, $datas , $this->lockout_duration );
            }
        }
        private function when( $time ) {
            if ( ! $time )
                return;

            $right_now = time();

            $diff = abs( $right_now - $time );

            $second = 1;
            $minute = $second * 60;
            $hour = $minute * 60;
            $day = $hour * 24;

            if ( $diff < $minute )
                return floor( $diff / $second ) . ' secondes';

            if ( $diff < $minute * 2 )
                return "about 1 minute ago";

            if ( $diff < $hour )
                return floor( $diff / $minute ) . ' minutes';

            if ( $diff < $hour * 2 )
                return 'about 1 hour';

            return floor( $diff / $hour ) . ' hours';
        }
    }
}

new Jeba_Limit_Login_Attempts();
 
 
?>