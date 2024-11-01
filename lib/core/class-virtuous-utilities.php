<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'VirtuousUtilities' ) ) :

class VirtuousUtilities {

	/**
	 * @param  $type
	 * @return string
	 */
	function vdrm_check_user_agent( $type = NULL ) {

	    $user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
	    if ( $type == 'bot' ) {
	        // matches popular bots
	        if ( preg_match( "/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent ) ) {
	            return true;
	            // watchmouse|pingdom\.com are "uptime services"
	        }
	    } else if ( $type == 'browser' ) {
	        // matches core browser types
	        if ( preg_match( "/mozilla\/|opera\//", $userAgent ) ) {
	            return true;
	        }
	    } else if ( $type == 'mobile' ) {
	        // matches popular mobile devices that have small screens and/or touch inputs
	        // mobile devices have regional trends; some of these will have varying popularity in Europe, Asia, and America
	        // detailed demographics are unknown, and South America, the Pacific Islands, and Africa trends might not be represented, here
	        if ( preg_match( "/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $user_agent ) ) {
	            // these are the most common
	            return true;
	        } else if ( preg_match( "/mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /", $user_agent ) ) {
	            // these are less common, and might not be worth checking
	            return true;
	        }
	    }
	    return false;
	}

	/**
	 * @return array
	 */
	function vdrm_extract_variables() {

	    $pathInfo = isset( $_SERVER['REQUEST_URI'] )
	        ? $_SERVER['REQUEST_URI']
	        : $_SERVER['REDIRECT_URL'];

	    $params = preg_split( '|/|', $pathInfo, -1, PREG_SPLIT_NO_EMPTY );	   
	    return $params;
	}	

	function vdrm_encode_string( $input_string ){

	    $sBase64 = base64_encode( $input_string );
	    return strtr( $sBase64, '+/', '-_' );
	}

	function vdrm_decode_string( $input_string ){

	    $sBase64 = strtr( $input_string, '-_', '+/' );
	    return base64_decode( $sBase64 );
	}	
}

endif;