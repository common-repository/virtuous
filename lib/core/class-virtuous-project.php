<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'VirtuousProject' ) ) :

class VirtuousProject {

    function __construct() {

        $this->_init();
    }

    private function _init() {

        add_action( 'wp_ajax_nopriv_update_project_status', array( $this, 'vdrm_update_project_status' ) );
        add_action( 'wp_ajax_update_project_status', array( $this, 'vdrm_update_project_status' ) );
        add_action( 'wp_ajax_nopriv_check_project_inventory_status', array( $this, 'vdrm_check_project_inventory_status' ) );
        add_action( 'wp_ajax_check_project_inventory_status', array( $this, 'vdrm_check_project_inventory_status' ) );
        add_shortcode( 'virtuous_projects', array( $this, 'vdrm_virtuous_projects_func' ) );
        add_shortcode( 'virtuous_project_details', array( $this, 'vdrm_virtuous_project_details_func' ) );
    }

    public function vdrm_get_top_projects( $return_unique_only = false ) {

        global $VirtuousApi;

        $projects = $VirtuousApi->vdrm_get_top_projects('', '100');
        
        if ( $projects ) {
            // get all the unique project types from the Virtuous API
            if ( $return_unique_only ) {
                $unique_projects = array();
                foreach ( $projects->list as $project ) {
                    if ( ! in_array( $project->projectType, $unique_projects ) ) {
                        array_push( $unique_projects, $project->projectType );
                    }
                }
                return $unique_projects;
            } else {
                return $projects;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $atts
     * @return string
     */
    public function vdrm_get_all_projects( $sub_projects_only = 'false', $project_types = '', $inventory_statuses = '', $skip = '', $take = '' ) {

        global $VirtuousApi;

        $response = $VirtuousApi->vdrm_get_all_projects( $sub_projects_only, $project_types, $inventory_statuses, $skip, $take );

        if ( $response ) {
            $cnt = 0;

            $projects = new stdClass();
            $projects->total = $response->total;
            $projects->projects = array();

            foreach ( $response->list as $p ) {

                $project = new stdClass();
                $project->id = $p->id;
                $project->name = $p->name;
                $project->project_type = $p->projectType;
                $project->image = $p->photoUrl;

                $custom_fields = array();
                foreach ( $p->customFields as $key => $val ) {
                    $custom_fields[$key] = $val;
                }

                $project->custom_fields = (object)$custom_fields;
                $projects->projects[$cnt] = $project;

                $cnt++;
            }

            return $projects;            
        } else {
            return false;
        }

    }

    /**
     * Display child details and link to support
     * @param $atts
     * @return string
     */
    public function vdrm_get_project( $id ) {

        if ( ! empty( $id ) ) {

            $VirtuousApi = new VirtuousApi();
            $p = $VirtuousApi->vdrm_get_project( $id );

            if ( $p ) {
                $project = new stdClass();
                $project->id = $id;
                $project->image = $p->photoUrl;
                $project->name = $p->name;
                $project->description = $p->description;
                $project->project_type = $p->projectType;
                $project->is_sub_project = $p->isSubProject = 1 ? 'true' : 'false';
                $project->inventory_status = $p->inventoryStatus;
                $project->gift_specifications = $p->giftSpecifications;
                $project->parent_id = $p->parentId;
                $project->parent_name = $p->parentName;

                $custom_fields = array();
                if ( ! empty( $p->customFields ) ) {
                    foreach ( $p->customFields as $key => $val ) {
                        if ($key == 'dateofBirth') {
                            $custom_fields['birth date'] = $val;
                            $age = $this->_vdrm_calculate_age( $val );
                            $custom_fields['age'] = $age;
                        } else {
                            $custom_fields[$key] = $val;
                        }
                    }
                }

                $project->custom_fields = (object)$custom_fields;

                return $project;

            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    function vdrm_update_project_status() {

        global $VirtuousApi;
        $project_id = $_POST['project_id'];
        $response = $VirtuousApi->vdrm_update_project_status( $project_id );
    }


    function vdrm_check_project_inventory_status() {

        global $VirtuousApi;

        $project_id = $_POST['project_id'];
        $project = $VirtuousApi->vdrm_get_project( $project_id );

        if ( $project->inventoryStatus == 'Allocated' ) {
            $response_array = array( 'error' => 'true' );
        } else {
            $response_array = array( 'error' => 'false' );
        }

        header("Content-Type: application/json");
        $response = json_encode( $response_array );
        echo $response;
        exit;
    }

    private function _vdrm_calculate_age( $date_of_birth ) {

        $birth_date = $date_of_birth;
        $child_birth = strtotime( $birth_date );
        $now = strtotime( date( 'Y-m-d' ) );
        $diff = $now - $child_birth;
        $age = floor( $diff / ( 365 * 60 * 60 * 24 ) );
        return $age;
    }

    /**
     * @param $atts
     * @return string
     */
    function vdrm_virtuous_projects_func( $atts ) {

        $virtuous_api_project_types_serialized = get_option( 'virtuous_api_project_types' );
        if ( ! empty( $virtuous_api_project_types_serialized ) ) {
            $virtuous_api_project_types_array = unserialize( $virtuous_api_project_types_serialized );
        }
        $virtuous_api_project_types = implode( ', ', array_keys( $virtuous_api_project_types_array ) );

        extract( shortcode_atts(array(
          'project_types' => $virtuous_api_project_types,
          'inventory_statuses' => 'Available',
          'items_per_page' => '12'
        ), $atts ) );

        global $VirtuousUtilities;
        global $VirtuousProject;

        $uri = $_SERVER['REQUEST_URI'];
        $base = $_SERVER['SERVER_NAME'];

        // page
        $get_variable = $VirtuousUtilities->vdrm_extract_variables();
        $page = isset( $get_variable[1] ) ? $get_variable[1] : 1;
        $next_page = $page + 1;
        $previous_page = $page;
        if ( $page == 1 ) {
            // We set it to 2 so when the next line subtracts 1 it will be 1
            $previous_page = 2;
        }
        $previous_page--;

        if ( $page > 1 ) {
            $skip = ( $page - 1 ) * $items_per_page;
        } else {
            $skip = '';
        }

        $take = $items_per_page;

        $response = $VirtuousProject->vdrm_get_all_projects( 'true', $project_types, $inventory_statuses, $skip, $take );

        if ( $response ) {
            
            $total_items = $response->total;
            $page_count = intval( $total_items / $items_per_page );
            $remainder = ( ( $total_items % $items_per_page ) == 0 ) ? 0 : 1;
            $page_count += $remainder;
            $return_val = '';
            $is_mobile = $VirtuousUtilities->vdrm_check_user_agent( 'mobile' );

            if ( $is_mobile ) {
                $return_val .= '<div class="uk-margin-large-top uk-flex uk-flex-center uk-flex-wrap">';
            } else {
                $return_val .= '<div class="uk-margin-large-top uk-flex uk-flex-space-between uk-flex-wrap">';
            }

            foreach ( $response->projects as $project ) {

                $project_id = $project->id;
                $name = $project->name;
                $image_url = $project->image;
                $project_url = 'http://' . $base . '/' . $virtuous_api_project_types_array[$project->project_type]['slug'] . '/' . $project_id;

                $return_val .= '<div class="uk-margin-top">' .
                    '<a href="' . $project_url . '/"><img class="uk-border-rounded" style="border-width: 0px;" src="' . $image_url . '" alt="Child Image" width="250px" /></a><br/><br/>' .
                    '<div class="uk-text-small" style="text-align: center">' .
                    '<span class="uk-text-large uk-text-bold">' . $name . '</span><br/>' .
                    '</span>' .
                    '<a href="' . $project_url . '/" class="x-btn x-btn-square x-btn-regular">Details</a><br/><br/>' .
                    '</div>' .
                    '</div>';
            }

            $return_val .= '</div>';

            // pagination controls
            $return_val .= '<div class="uk-margin-top">';
            if ( $page > 1 ) {
                $return_val .= '<a class="x-btn x-btn-square x-btn-regular uk-align-left" href="' . $uri . '?page=' . $previous_page . '"><< Previous</a>';
            }

            if ( $page < $page_count ) {
                $return_val .= '<a class="x-btn x-btn-square x-btn-regular uk-align-right" href="' . $uri . '?page=' . $next_page . '">Next >></a></div>';
            }
        } else {
            $return_val = 'We encountered an error retrieving projects';
        }

        return $return_val;
    }

    /**
     * Display child details and link to support
     * @param $atts
     * @return string
     */
    function vdrm_virtuous_project_details_func( $atts ) {

        global $VirtuousUtilities;
        $get_variable = $VirtuousUtilities->vdrm_extract_variables();
        $id = $get_variable[count( $get_variable ) - 1];
        $return_val = '';
        $ajax_url = plugin_dir_url(  __FILE__  ) . 'listener.php';
        $support_button_text = '';
        $base_support_url = '';

        extract( shortcode_atts(array(
          'update_status' => 'false',
          'check_status' => 'false'
        ), $atts ) );

        $virtuous_api_project_types_serialized = get_option( 'virtuous_api_project_types' );
        if ( ! empty( $virtuous_api_project_types_serialized ) ) {
            $virtuous_api_project_types_array = unserialize( $virtuous_api_project_types_serialized );
        }
        $virtuous_api_project_types = implode( ', ', array_keys( $virtuous_api_project_types_array ) );

        if ( ! empty( $id ) && is_numeric( $id ) ) {

            global $VirtuousProject;
            $project = $VirtuousProject->vdrm_get_project( $id );

            if ( $project ) {
            
                $include_support_button = $virtuous_api_project_types_array[$project->project_type]['include_support_button'];//get_option( 'virtuous_api_include_support_button' );
                if ( $include_support_button ) {
                    $support_button_text = $virtuous_api_project_types_array[$project->project_type]['support_button_text'];//get_option( 'virtuous_api_support_button_text' );
                    $base_support_url = $virtuous_api_project_types_array[$project->project_type]['support_page_url'];//get_option( 'virtuous_api_support_form_base_url' );
                }

                $support_url = '';
                $support_url_params = '';
                $age = '';

                $support_url_params = '?integration=virtuous' .
                    rawurlencode( 
                        '&virtuous[isRestrictedToGiftSpecifications]=true' .
                        '&virtuous[id]=' . $project->id .
                        '&virtuous[name]=' . $project->name .
                        '&virtuous[description]=' . $project->description .
                        '&virtuous[photoUrl]=' . $project->image .
                        '&virtuous[parentId]=' . $project->parent_id .
                        '&virtuous[parentName]=' . $project->parent_name .
                        '&virtuous[isSubProject]=' . $project->is_sub_project .
                        '&virtuous[inventoryStatus]=' . $project->inventory_status .
                        '&virtuous[projectType]=' . $project->project_type
                     );

                for ( $cnt = 0; $cnt < count( $project->gift_specifications ); $cnt++ ) {
                    $support_url_params .= rawurlencode( '&virtuous[giftSpecifications][' . $cnt . '][amount]=' . $project->gift_specifications[$cnt]->amount .
                        '&virtuous[giftSpecifications][' . $cnt . '][frequency]=' . $project->gift_specifications[$cnt]->frequency );
                }

                $custom_fields_url = '';
                $custom_fields_html = '';
                foreach ( $project->custom_fields as $key => $val ) {
                    if ( ! empty( $val ) ) {                        
                        $custom_fields_url .= '&virtuous[custom_fields][' . $key . ']=' . $val;
                        $custom_fields_html .= '<strong>' . str_replace( '_', ' ', ucwords($key) ) . '</strong>: ' . $val . '<br/>';
                    }
                }

                $support_url_params .= rawurlencode( $custom_fields_url );
                $support_url = $base_support_url . $support_url_params;
                $support_url = str_replace( '%3D', '=', $support_url );
                $support_url = str_replace( '%26', '&', $support_url );
                
                $return_val .= '<div style="border-bottom-style:solid; padding-bottom:30px; padding-top:20px;" class="uk-grid uk-margin-top">' .
                    '<div class="uk-width-small-1-1 uk-width-medium-1-2 uk-width-large-1-2">' .
                    '<img src="' . $project->image . '" class="uk-align-center uk-width-2-3 uk-border-rounded" />' .
                    '</div>' .
                    '<div class="uk-width-small-1-1 uk-width-medium-1-2 uk-width-large-1-2 uk-text-top uk-text-left-large uk-text-center-small">' .
                    '<h2 style="padding-top:0px; margin-top:10px;">' . $project->name . '</h2>' .
                    '<p>' . $project->description . '</p>' .
                    '<p>' . $custom_fields_html . '</p>' .
                    '<button class="x-btn" type="button" onclick="window.history.back();">Back to Projects</button>';

                if ( $include_support_button ) {
                    $return_val .= '&nbsp;&nbsp;<button class="x-btn" type="button" onclick="redirect_to_giving_fuel(\'' . $support_url . '\', \'' . $id . '\', \'' . $update_status . '\', \'' . $check_status . '\')">' . $support_button_text . '</button>';
                }

                $return_val .= '<div id="vrdm_inv_error_container" style="display: none;"><p id="vrdm_inv_error_msg" /></div>' .
                    '</div>' .                    
                    '</div>';
            } else {

                $return_val = 'We encountered an error retrieving this project';
            }

        } else {
            $return_val = '<div class="uk-grid">' .
                '<div class="uk-text-muted"><p>No details to display</p>' .
                '<p><a href="/v-projects">Back To Projects</a></p>' .
                '</div>';
        }

        return $return_val;  
    }    
}

endif;