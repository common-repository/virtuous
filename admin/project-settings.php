<?php 

$display_message = false;

$virtuous_api_include_support_button = '';
$virtuous_api_support_button_text = '';
$virtuous_api_support_page_url = '';
$virtuous_api_details_page = '';
$alert = '';
$api_message = '';
$redirect_to_api_settings_page = false;

global $VirtuousApi;
global $VirtuousProject;

if ( $VirtuousApi->vdrm_virtuous_credentials_loaded ) {
    $return_unique_only = true;
    $available_projects = $VirtuousProject->vdrm_get_top_projects( $return_unique_only );
} else {
    $redirect_to_api_settings_page = true;
    $api_settings_page_url = admin_url( 'admin.php?page=virtuous/admin/api-settings.php' );
    $api_message = '<p>Before we get started configuring your site to display your Virtuous projects, we need to get some account information from you.</p>
        <p><a href="' . $api_settings_page_url . '">Head over to the API Settings Page to begin</a></p>';
}

$active_project_types = array();

$args = array(
    'sort_order' => 'asc',
    'sort_column' => 'post_title',
    'hierarchical' => 1,
    'exclude' => '',
    'include' => '',
    'meta_key' => '',
    'meta_value' => '',
    'authors' => '',
    'child_of' => 0,
    'parent' => -1,
    'exclude_tree' => '',
    'number' => '',
    'offset' => 0,
    'post_type' => 'page',
    'post_status' => 'publish'
); 
$post_pages = get_pages($args);
$pages = array();
foreach ( $post_pages as $page ) {
    $page_info = new stdClass();
    $page_info->ID = $page->ID;    
    $page_info->title = $page->post_title;
    $pages[] = $page_info;
}

if ( $_POST ) {

    // build array of options
    $active_project_types = array();

    if ( isset( $_POST['virtuous_api_active_project_types'] ) && is_array( $_POST['virtuous_api_active_project_types'] ) ) {
        for ( $i = 0; $i < count( $_POST['hdn_virtuous_api_active_project_types'] ); $i++ ) {            
            if ( key_exists( $i, $_POST['virtuous_api_active_project_types'] ) ) {
                $project_name = $_POST['virtuous_api_active_project_types'][$i];

                // get the slug for use on the project listing page
                $project_detail_page = $_POST['virtuous_api_project_detail_page'][$i];
                $post = get_post($project_detail_page); 
                $slug = $post->post_name;

                $project = array( 'project_detail_page' => $project_detail_page,
                    'slug' => $slug );

                if (isset( $_POST['virtuous_api_include_support_button'] ) ) {
                    if ( key_exists( $i, $_POST['virtuous_api_include_support_button'] ) ) {
                        $project['include_support_button'] = true;
                        if ( $_POST['virtuous_api_support_button_text'][$i] != '' ) {
                            $project['support_button_text'] = $_POST['virtuous_api_support_button_text'][$i];
                        } else {
                            $project['support_button_text'] = 'Donate Now';
                        }
                        $project['support_page_url'] = $_POST['virtuous_api_support_page_url'][$i];
                    } else {
                        $project['include_support_button'] = false;
                    }
                } else {
                    $project['include_support_button'] = false;
                }
                $active_project_types[$project_name] = $project;
            }
        }
        update_option( 'virtuous_api_project_types', serialize( $active_project_types ) );

    } else {
        delete_option( 'virtuous_api_project_types' );
    }

    $display_message = true;
    $alert = 'Your information has been saved';

} else {

    if ( $VirtuousApi->vdrm_virtuous_credentials_loaded ) { // premiere fois
        $virtuous_api_project_types = get_option( 'virtuous_api_project_types' );

        if ( ! empty( $virtuous_api_project_types ) ) {
            $active_project_types = unserialize( $virtuous_api_project_types );
        }
    }
} 

?>
<div class="wrap">
<h1>Virtuous Project Settings</h1><br/>
<?php 
    if ( $redirect_to_api_settings_page ) {
?>
        <table>
            <tr>
                <td><?php echo $api_message; ?></td>
            </tr>
        </table>
<?php        
    } else {    
?>

<form method="post">
<?php 
    settings_fields( 'virtuous-settings-group' );
    do_settings_sections( 'virtuous-settings-group' ); 
?>
    <table class="wp-list-table widefat  striped">
        <thead>
<?php

        if ( $display_message ) {
?>
        <tr>
            <td colspan="6"><p><?php echo $alert; ?></p></td>
        </tr>
<?php           
        }   
?>      
        <tr>
            <th class="span-cb top"><strong>Active</strong></th>
            <td class="span-15 top"><strong>Project</strong></td>
            <th class="span-15 top"><strong>Detail Page</strong></th>
            <td class="span-cb top" style="text-align: center;"><strong>Action Button</strong></td>
            <th class="span-15 top"><strong>Button Text</strong></th>
            <th class="span-25 top"><strong>Donate URL</strong></th>
        <tr/>
        </thead>

<?php 
            $cnt = 0;

            $virtuous_api_active_project = false;
            $virtuous_api_include_support_button = false;
            $virtuous_api_support_button_text = '';
            $virtuous_api_support_page_url = '';
            $virtuous_api_project_detail_page = '';

            foreach ( $available_projects as $project ) {
                if ( ! empty($active_project_types) && key_exists( $project, $active_project_types ) ) {
                    if ( $active_project_types[$project]['include_support_button'] ) {
                        $virtuous_api_include_support_button = true;
                        $virtuous_api_support_button_text = $active_project_types[$project]['support_button_text'];
                        $virtuous_api_support_page_url = $active_project_types[$project]['support_page_url'];
                    } else {
                        $virtuous_api_include_support_button = false;
                        $virtuous_api_support_button_text = '';
                        $virtuous_api_support_page_url = '';
                    }
                    $virtuous_api_active_project = true;
                    $virtuous_api_project_detail_page = $active_project_types[$project]['project_detail_page'];
                } else {
                    $virtuous_api_active_project = false;
                    $virtuous_api_include_support_button = false;
                    $virtuous_api_support_button_text = '';
                    $virtuous_api_support_page_url = '';
                }
            //}
?>              
            <tr>
                <th class="span-cb">
                    <input type="checkbox" name="virtuous_api_active_project_types[<?php echo $cnt; ?>]" value="<?php echo $project; ?>"<?php if ( $virtuous_api_active_project ) { echo ' checked'; } ?> />
                    <input type="hidden" name="hdn_virtuous_api_active_project_types[<?php echo $cnt; ?>]" value="<?php echo $project; ?>" />
                </th>
                <td class="span-15">
                    <?php echo $project; ?>
                </td>
                <td class="span-15">
                    <select name="virtuous_api_project_detail_page[<?php echo $cnt; ?>]" id="virtuous_api_details_page" style="width: auto;">
<?php
                    foreach ($pages as $page) {
?>                      
                        <option value="<?php echo $page->ID; ?>"<?php if ( $page->ID == $virtuous_api_project_detail_page ) { echo ' selected'; } ?>><?php echo $page->title; ?></option>
<?php
                    }
?>                                      
                    </select>
                </td>
                <td class="center span-cb">
                    <input type="checkbox" name="virtuous_api_include_support_button[<?php echo $cnt; ?>]" value="1"<?php if ( $virtuous_api_include_support_button ) { echo ' checked'; } ?> />
                </td>
                <td class="span-15">
                    <input type="text" class="full-text" name="virtuous_api_support_button_text[<?php echo $cnt; ?>]" value="<?php echo $virtuous_api_support_button_text; ?>" placeholder="Donate Now, etc." />
                </td>
                <td class="span-25">
                    <input type="text" class="full-text" name="virtuous_api_support_page_url[<?php echo $cnt; ?>]" value="<?php echo $virtuous_api_support_page_url; ?>" />
                </td>
            </tr>
<?php
            $cnt++;
            }
?>          
        </tr>
    </table>
    <table>
        <tr>
            <td>&nbsp;</td>
            <td>    
                <?php submit_button(); ?>
            </td>
        </tr>
    </table>
</form>        

<table>
    <tr>
        <td>
            <p>Projects are displayed on your page by typing a shortcode in the content.</p>
            <p><h3>Project List</h3></p>
            <p><strong>Shortcode name:</strong> virtuous_projects</p>
            <p><strong>Optional attributes:</strong></p>
            <p>
                <strong>project_types (separate by commas, if more than one)</strong> - if omitted, projects you mark as Active on this page will be used<br/>
                <strong>inventory_statuses</strong> - if omitted, the default is <em>Available</em><br/>
                <strong>items_per_page</strong> - if omitted, the default is <em>12</em>
            </p>
            <p><strong>Samples:</strong></p>
            <p>
                [virtuous_projects]<br/>
                [virtuous_projects items_per_page="8"]<br/>
                [virtuous_projects inventory_statuses="Available, Available - Previously Sponsored" project_types="Child" items_per_page="4", update_status="true", is_unique="true"]
            </p>

            <p><h3>Project Details</h3></p>
            <p><strong>Shortcode name:</strong> virtuous_project_details</p>
            <p><strong>Optional attribute:</strong></p>
            <p>
                <strong>update_status</strong> temporarily sets inventory status to Allocated when a donor clicks your Action button - if omitted, the default is <em>false</em>
                <strong>check_status</strong> only allows support from one donor - if omitted, the default is false
            </p>
            <p><strong>Sample:</strong></p>
            <p>
                [virtuous_project_details]<br/>
                [virtuous_project_details update_status="true" check_status="true"]<br/>
            </p>

        </td>
    </tr>
</table>
<?php } ?>
