<?php
/**
 * Created by Virtuous
 * User: Steve Cook
 * Date: 3/15/2016
 * Time: 08:00 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'VirtuousApi' ) ) :

class VirtuousApi {
    
    private $_base_url = "";
    private $_username = "";
    private $_password = "";
    private $_oauth2_token_response = '';
    public $vdrm_virtuous_credentials_loaded = false;

    /**
     * Default constructor
     * @param $baseUrl
     * @param $username
     * @param $password
     */
    function __construct() {

        global $VirtuousUtilities;
        $this->_base_url = get_option( 'virtuous_api_environment' );
        $this->_username = get_option( 'virtuous_api_login_email' );
        $this->_password = $VirtuousUtilities->vdrm_decode_string( get_option( 'virtuous_api_login_password' ) );

        if ( ! empty( $this->_base_url ) ) {
            $this->vdrm_virtuous_credentials_loaded = true;
        }
    }

    /**
     * Gets the oauth token from the Virtuous ApiNot
     */
    function vdrm_login() {

        $url = $this->_base_url . "/Token";
        $oauth2_token_arguments = array(
            "grant_type" => "password",
            "username" => $this->_username,
            "password" => $this->_password
        );

        $this->_oauth2_token_response = $this->vdrm_call_api( $url, '', 'POST', $oauth2_token_arguments );
    }

    /**
     * Logs out the user.
     * @return mixed|string
     */
    function vdrm_logout() {

        $url = $this->_base_url . '/oauth2/logout';
        $this->_oauth2_logout_response = $this->vdrm_call_api( $url, $this->_oauth2_token_response->access_token, 'POST', false );
    }

    /**
     * Verify that these credentials will work
     */
    function vdrm_verify_credentials(  $environment, $username, $password ) {

        global $VirtuousUtilities;
        $token = '';
        $url = $environment . '/Token';
        $oauth2_token_arguments = array(
            "grant_type" => "password",
            "username" => $username,
            "password" => $VirtuousUtilities->vdrm_decode_string( $password )
        );

        $this->_oauth2_token_response = $this->vdrm_call_api( $url, '', 'POST', $oauth2_token_arguments );
        $this->_base_url = $environment;
        if ( key_exists( 'access_token', $this->_oauth2_token_response ) ) {
            $token = $this->_oauth2_token_response->access_token;
            $this->vdrm_logout();
        } 

        if ( ! empty ( $token ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all projects
     * @param subProjectsOnly - boolean, whether or not to only retrieve sub-projects
     * @param $projectTypes - string, comma-delimited project types
     * @param $inventoryStatues - string, comma-delimited inventoryStatuses
     * @param $skip - int or empty string, how many records to skip
     * @param $take - int or empty string, how many records to retrieve
    */
    function vdrm_get_all_projects( $sub_projects_only = 'false', $project_types = '', $inventory_statuses = '', $skip = '', $take = '' ) {

        $this->vdrm_login();
        if ( key_exists( 'access_token', $this->_oauth2_token_response ) ) {
            $url = $this->_base_url . '/api/Project/All';
            $arguments = array(
                'subProjectsOnly' => $sub_projects_only,
                'projectTypes' => $project_types,
                'inventoryStatuses' => $inventory_statuses
            );
            
            if ( ! empty( $skip ) ) {
                $arguments['skip'] = $skip;
            }
            if ( ! empty( $take ) ) {
                $arguments['take'] = $take;
            }

            $response = $this->vdrm_call_api( $url, $this->_oauth2_token_response->access_token, 'GET', $arguments );            
            $this->vdrm_logout();

            return $response;
        } else {
            return false;
        }
    }
    
    /**
     * Get top projects
     * @param $projectTypes - string, comma-delimited project types
     * @param $inventoryStatues - string, comma-delimited inventoryStatuses
     * @param $skip - int or empty string, how many records to skip
     * @param $take - int or empty string, how many records to retrieve
    */
    function vdrm_get_top_projects( $skip = '', $take = '' ) {

        $this->vdrm_login();
        if ( key_exists( 'access_token', $this->_oauth2_token_response ) ) {
            $url = $this->_base_url . '/api/Project/Top';
            $arguments = array();
            if ( ! empty( $skip ) ) {
                $arguments['skip'] = $skip;
            }
            if ( ! empty( $take ) ) {
                $arguments['take'] = $take;
            }

            $response = $this->vdrm_call_api( $url, $this->_oauth2_token_response->access_token, 'GET', $arguments );
            $this->vdrm_logout();
            return $response;
        } else {
            return false;
        }
    }

    function vdrm_get_project( $project_id ) {

        $this->vdrm_login();
        if ( key_exists( 'access_token', $this->_oauth2_token_response ) ) {
            $url = $this->_base_url . '/api/Project/' . $project_id;
            $arguments = array(
                'projectId' => $project_id
            );
            $response = $this->vdrm_call_api( $url, $this->_oauth2_token_response->access_token, 'GET', $arguments );
            $this->vdrm_logout();
            return $response;
        } else {
            return false;
        }
    }

    function vdrm_update_project_status( $project_id ) {

        $this->vdrm_login();
        //durationMinutes is an optional parameter - if left empty, it will default to 10 minutes
        if ( key_exists( 'access_token', $this->_oauth2_token_response ) ) {
            $url = $this->_base_url . '/api/Project/' . $project_id . '/Status?inventoryStatus=Allocated&durationMinutes=15';
            $arguments = array(
                'projectId' => $project_id,
                'inventoryStatus' => 'Allocated',
                'durationMinutes' => '15'
            );
            $response = $this->vdrm_call_api( $url, $this->_oauth2_token_response->access_token, 'PUT', $arguments, true );
            $this->vdrm_logout();
            return $response;
        } else { 
            return false;
        }
    }

    /**
     * Builds the oauth url
     * @param $url
     * @param string $oauthtoken
     * @param string $type
     * @param array $arguments
     * @param bool|true $encode_data
     * @param bool|false $returnHeaders
     * @return mixed|string
     */
    private function vdrm_call_api( $url, $oauthtoken = '', $type = 'GET', $arguments = array(), $encode_data = false, $return_headers = false ) {
        
        $type = strtoupper( $type );

        if ( $type == 'GET' )
        {
            $url .= "?" . http_build_query( $arguments );
        }

        $curl_request = curl_init( $url );

        if ( $type == 'POST' )
        {
            curl_setopt( $curl_request, CURLOPT_POST, 1 );
        }
        elseif ( $type == 'PUT' )
        {
            curl_setopt( $curl_request, CURLOPT_CUSTOMREQUEST, 'PUT' );
        }
        elseif ( $type == 'DELETE' )
        {
            curl_setopt( $curl_request, CURLOPT_CUSTOMREQUEST, 'DELETE' );
        }

        curl_setopt( $curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        curl_setopt( $curl_request, CURLOPT_HEADER, $return_headers );
        curl_setopt( $curl_request, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $curl_request, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl_request, CURLOPT_FOLLOWLOCATION, 0 );

        if ( ! empty( $oauthtoken ) )
        {
            $token = array( "Authorization: Bearer $oauthtoken" );
            curl_setopt( $curl_request, CURLOPT_HTTPHEADER, $token );
        }

        if ( ! empty( $arguments ) && $type !== 'GET' )
        {
            if ( $encode_data ) {
                //encode the arguments as JSON
                $arguments = json_encode( $arguments );
            }
            curl_setopt( $curl_request, CURLOPT_POSTFIELDS, http_build_query( $arguments ) );
        }

        $result = curl_exec( $curl_request );

        $err = curl_error( $curl_request );
        if ( $err ) {
            echo 'Error: ' . $err; 
            exit;
        }
         
        if ( $return_headers )
        {
            //set headers from response
            list( $headers, $content ) = explode( "\r\n\r\n", $result ,2 );
            foreach ( explode( "\r\n",$headers ) as $header )
            {
                header( $header );
            }

            //return the nonheader data
            return trim( $content );
        }

        curl_close( $curl_request );

        //decode the response from JSON
        $response = json_decode( $result );

        return $response;
    }
}

endif;