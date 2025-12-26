<?php
/**
 * FFC_Admin
 * Handles the administrative interface, menus, and submission management.
 *
 * @package FastFormCertificates
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

<<<<<<< Updated upstream
/**
 * Classe responsável pela administração do plugin
 */
=======
>>>>>>> Stashed changes
class FFC_Admin {
    
    /** @var FFC_Submission_Handler */
    private $submission_handler;

    /** @var FFC_Form_Editor */
    private $form_editor;

    /** @var FFC_Settings */
    private $settings_page;

    /**
     * POINT 2: Reorganization. 
     * Constructor now cleanly handles dependencies.
     */
    public function __construct( FFC_Submission_Handler $handler ) {
        $this->submission_handler = $handler;

        // Load dependent admin classes
        require_once plugin_dir_path( __FILE__ ) . 'class-ffc-form-editor.php';
        require_once plugin_dir_path( __FILE__ ) . 'class-ffc-settings.php';

        $this->form_editor   = new FFC_Form_Editor();
        $this->settings_page = new FFC_Settings( $handler );

        // Hooks
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_action( 'admin_init', array( $this, 'handle_submission_actions' ) );
        add_action( 'admin_init', array( $this, 'handle_csv_export' ) );
        add_action( 'admin_init', array( $this, 'handle_submission_save' ) );
        
        // AJAX & UI
        add_action( 'wp_ajax_ffc_admin_get_pdf_data', array( $this, 'ajax_get_pdf_data' ) );
        add_action( 'admin_footer', array( $this, 'inject_preview_modal' ) );
    }

<<<<<<< Updated upstream
=======
    /**
     * POINT 5: Internationalization.
     * Standardized menu labels to English (plugin default).
     */
>>>>>>> Stashed changes
    public function register_admin_menu() {
        add_submenu_page( 
            'edit.php?post_type=ffc_form', 
            __( 'Submissions', 'ffc' ), 
            __( 'Submissions', 'ffc' ), 
            'manage_options', 
            'ffc-submissions', 
            array( $this, 'display_submissions_page' ) 
        );

        add_submenu_page( 
            'edit.php?post_type=ffc_form', 
            __( 'Settings', 'ffc' ), 
            __( 'Settings', 'ffc' ), 
            'manage_options', 
            'ffc-settings', 
            array( $this->settings_page, 'display_settings_page' ) 
        );
    }

<<<<<<< Updated upstream
=======
    /**
     * POINT 4: Enqueue admin-specific assets only where needed.
     */
>>>>>>> Stashed changes
    public function admin_assets( $hook ) {
        global $post_type;
        $is_ffc_page = ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'ffc-' ) !== false );
        
<<<<<<< Updated upstream
        // Carrega assets apenas na edição do formulário ou nas páginas do plugin
        $is_ffc_page = ( isset($_GET['page']) && strpos($_GET['page'], 'ffc-') !== false );
        
        if ( $post_type === 'ffc_form' || $is_ffc_page ) {
            wp_enqueue_media(); // Garante que a biblioteca de mídia do WP funcione
            
            wp_enqueue_style( 'ffc-pdf-core', FFC_PLUGIN_URL . 'assets/css/ffc-pdf-core.css', array(), time() );
            wp_enqueue_style( 'ffc-admin-css', FFC_PLUGIN_URL . 'assets/css/admin.css', array('ffc-pdf-core'), time() );
            
            wp_enqueue_script( 'html2canvas', FFC_PLUGIN_URL . 'assets/js/html2canvas.min.js', array(), '1.4.1', true );
            wp_enqueue_script( 'jspdf', FFC_PLUGIN_URL . 'assets/js/jspdf.umd.min.js', array(), '2.5.1', true );
            
            wp_enqueue_script( 'ffc-frontend-js', FFC_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery', 'html2canvas', 'jspdf' ), time(), true );
            
            // Adicionado explicitamente jquery-ui-sortable como dependência
            wp_enqueue_script( 'ffc-admin-js', FFC_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'ffc-frontend-js' ), time(), true );
            
=======
        if ( 'ffc_form' === $post_type || $is_ffc_page ) {
            wp_enqueue_media();
            
            // CSS
            wp_enqueue_style( 'ffc-pdf-core', FFC_PLUGIN_URL . 'assets/css/ffc-pdf-core.css', array(), FFC_VERSION );
            wp_enqueue_style( 'ffc-admin-css', FFC_PLUGIN_URL . 'assets/css/admin.css', array( 'ffc-pdf-core' ), FFC_VERSION );
            
            // JS Engines
            wp_enqueue_script( 'ffc-html2canvas', FFC_PLUGIN_URL . 'assets/js/html2canvas.min.js', array(), '1.4.1', true );
            wp_enqueue_script( 'ffc-jspdf', FFC_PLUGIN_URL . 'assets/js/jspdf.umd.min.js', array(), '2.5.1', true );
            
            // Plugin Logic
            wp_enqueue_script( 'ffc-pdf-engine', FFC_PLUGIN_URL . 'assets/js/ffc-pdf-engine.js', array( 'jquery', 'ffc-html2canvas', 'ffc-jspdf' ), FFC_VERSION, true );
            wp_enqueue_script( 'ffc-admin-js', FFC_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'ffc-pdf-engine' ), FFC_VERSION, true );
            
>>>>>>> Stashed changes
            wp_localize_script( 'ffc-admin-js', 'ffc_admin_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'ffc_admin_pdf_nonce' ),
                'strings'  => array(
<<<<<<< Updated upstream
                    'generating'            => __( 'Generating...', 'ffc' ),
                    'error'                 => __( 'Error: ', 'ffc' ),
                    'selectTemplate'        => __( 'Please select a template.', 'ffc' ),
                    'confirmReplaceContent' => __( 'Are you sure? This will replace the current layout.', 'ffc' ),
                    'templateLoaded'        => __( 'Template loaded successfully!', 'ffc' ),
                    'loadTemplate'          => __( 'Load Template', 'ffc' ),
                    'codesGenerated'        => __( 'codes generated.', 'ffc' ),
                    'errorGeneratingCodes'  => __( 'Error generating codes.', 'ffc' ),
                    'confirmDeleteField'    => __( 'Are you sure you want to remove this field?', 'ffc' ),
                    'selectBackgroundImage' => __( 'Select Background Image', 'ffc' ),
                    'useImage'              => __( 'Use Image', 'ffc' ),
                    'fileImported'          => __( 'File imported successfully!', 'ffc' ),
                    'connectionError'       => __( 'Connection error.', 'ffc' ),
=======
                    'generating'      => __( 'Generating...', 'ffc' ),
                    'previewing'      => __( 'Preparing preview...', 'ffc' ),
                    'error'           => __( 'Error: ', 'ffc' ),
                    'confirmReplace'  => __( 'This will replace the current content. Continue?', 'ffc' ),
                    'confirmDelete'   => __( 'Are you sure you want to remove this field?', 'ffc' )
>>>>>>> Stashed changes
                )
            ) );
        }
    }

<<<<<<< Updated upstream
    // ... Restante do código (handle_submission_actions, render_list_page, etc) permanecem iguais ...
    // Note: Mantive o restante do seu código original abaixo desta linha para brevidade, 
    // pois a lógica de submissão não afeta a visibilidade dos campos no editor.

    public function handle_submission_actions() {
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'ffc-submissions' ) return;
        if ( isset( $_GET['submission_id'] ) && isset( $_GET['action'] ) ) {
            $id     = absint( $_GET['submission_id'] );
            $action = sanitize_key( $_GET['action'] );
            $nonce  = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
            $manipulation_actions = array( 'trash', 'restore', 'delete' );
            if ( in_array( $action, $manipulation_actions ) ) {
                if ( wp_verify_nonce( $nonce, 'ffc_action_' . $id ) ) {
                    if ( $action === 'trash' ) $this->submission_handler->trash_submission( $id );
                    if ( $action === 'restore' ) $this->submission_handler->restore_submission( $id );
                    if ( $action === 'delete' ) $this->submission_handler->delete_submission( $id );
                    $this->redirect_with_msg( $action );
=======
    /**
     * POINT 1: Security.
     * AJAX endpoint with capability and nonce checks.
     */
    public function ajax_get_pdf_data() {
        check_ajax_referer( 'ffc_admin_pdf_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ffc' ) ) );
        }
        
        $id  = isset( $_POST['submission_id'] ) ? absint( $_POST['submission_id'] ) : 0;
        $sub = $this->submission_handler->get_submission( $id );
        
        if ( ! $sub ) {
            wp_send_json_error( array( 'message' => __( 'Submission not found.', 'ffc' ) ) );
        }
        
        $data = json_decode( $sub->data, true ) ?: array();
        $data['email']     = $sub->email;
        $data['auth_code'] = $sub->auth_code;
        
        $form_id    = $sub->form_id;
        $form_title = get_the_title( $form_id );

        // Delegate template resolution to handler (supports conditional logic)
        $template_package = $this->submission_handler->get_resolved_template( $form_id, $data );
        $processed_html   = $this->submission_handler->generate_pdf_html( $data, $form_title, $template_package['html'] );
        
        wp_send_json_success( array(
            'template'   => $processed_html,
            'submission' => $data,
            'bg_image'   => $template_package['bg_image'],
            'form_title' => $form_title
        ) );
    }

    /**
     * POINT 4: Inject Modal with minimal inline styles.
     */
    public function inject_preview_modal() {
        global $post_type;
        if ( 'ffc_form' !== $post_type ) return;
        ?>
        <div id="ffc-preview-modal" class="ffc-modal" style="display:none;">
            <div class="ffc-modal-overlay"></div>
            <div class="ffc-modal-content">
                <div class="ffc-modal-header">
                    <h3><?php _e( 'Certificate Preview (Sample Data)', 'ffc' ); ?></h3>
                    <button type="button" class="ffc-modal-close">&times;</button>
                </div>
                <div id="ffc-preview-render-container">
                    <div class="ffc-preview-loader">
                        <span class="spinner is-active"></span> <?php _e( 'Rendering...', 'ffc' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * POINT 1 & 3: Handle single and bulk actions (Trash/Restore/Delete).
     */
    public function handle_submission_actions() {
        if ( ! isset( $_GET['page'] ) || 'ffc-submissions' !== $_GET['page'] ) return;
        
        // Single Action
        if ( isset( $_GET['submission_id'], $_GET['action'] ) ) {
            $id     = absint( $_GET['submission_id'] );
            $action = sanitize_key( $_GET['action'] );
            $nonce  = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
            
            if ( wp_verify_nonce( $nonce, 'ffc_action_' . $id ) ) {
                switch ( $action ) {
                    case 'trash':   $this->submission_handler->trash_submission( $id ); break;
                    case 'restore': $this->submission_handler->restore_submission( $id ); break;
                    case 'delete':  $this->submission_handler->delete_submission( $id ); break;
                    case 'resend_email': 
                        $this->manual_resend( $id ); 
                        $action = 'email_sent';
                        break;
>>>>>>> Stashed changes
                }
                $this->redirect_with_msg( $action );
            }
        }
<<<<<<< Updated upstream
        if ( isset($_GET['action']) && isset($_GET['submission']) && is_array($_GET['submission']) ) {
            $bulk_action = $_GET['action'];
            if ( $bulk_action === '-1' && isset($_GET['action2']) ) $bulk_action = $_GET['action2'];
            $allowed_bulk = array( 'bulk_trash', 'bulk_restore', 'bulk_delete' );
            if ( in_array( $bulk_action, $allowed_bulk ) ) {
                check_admin_referer('bulk-submissions'); 
                $ids = array_map('absint', $_GET['submission']);
=======

        // Bulk Actions
        if ( isset( $_GET['submission'] ) && is_array( $_GET['submission'] ) ) {
            $bulk_action = ( $_GET['action'] !== '-1' ) ? $_GET['action'] : $_GET['action2'];
            
            if ( strpos( $bulk_action, 'bulk_' ) === 0 ) {
                check_admin_referer( 'bulk-submissions' );
                $ids = array_map( 'absint', $_GET['submission'] );
                
>>>>>>> Stashed changes
                foreach ( $ids as $id ) {
                    if ( 'bulk_trash' === $bulk_action ) $this->submission_handler->trash_submission( $id );
                    if ( 'bulk_restore' === $bulk_action ) $this->submission_handler->restore_submission( $id );
                    if ( 'bulk_delete' === $bulk_action ) $this->submission_handler->delete_submission( $id );
                }
                $this->redirect_with_msg( 'bulk_done' );
            }
        }
    }

<<<<<<< Updated upstream
=======
    /**
     * Triggers a manual email resend via the Queue.
     */
    private function manual_resend( $submission_id ) {
        $sub = $this->submission_handler->get_submission( $submission_id );
        if ( ! $sub || empty( $sub->email ) ) return;

        $form_config = get_post_meta( $sub->form_id, '_ffc_form_config', true );
        $data = json_decode( $sub->data, true ) ?: array();
        
        $data['form_title'] = get_the_title( $sub->form_id );
        $data['auth_code']  = $sub->auth_code;

        $subject = FFC_Utils::parse_placeholders( $form_config['email_subject'] ?? '', $data );
        $body    = FFC_Utils::parse_placeholders( $form_config['email_body'] ?? '', $data );

        if ( class_exists( 'FFC_Email_Manager' ) ) {
            FFC_Email_Manager::queue_email( $sub->email, $subject, $body, $sub->form_id, $submission_id );
        }
    }

    /**
     * POINT 3: CSV Export logic.
     */
    public function handle_csv_export() {
        if ( isset( $_POST['ffc_action'] ) && 'export_csv_smart' === $_POST['ffc_action'] ) {
            check_admin_referer( 'ffc_export_csv_nonce', 'ffc_export_csv_action' );
            $this->submission_handler->export_csv();
        }
    }

    /**
     * POINT 1 & 3: Save manual edits to a submission.
     */
    public function handle_submission_save() {
        if ( isset( $_POST['ffc_save_edit'] ) ) {
            check_admin_referer( 'ffc_edit_submission_nonce', 'ffc_edit_submission_action' );
            
            global $wpdb;
            $id         = absint( $_POST['submission_id'] ); 
            $new_email  = sanitize_email( $_POST['user_email'] ); 
            $raw_data   = isset( $_POST['data'] ) ? $_POST['data'] : array();
            $clean_data = array(); 
            
            foreach ( $raw_data as $k => $v ) { 
                $clean_data[sanitize_key($k)] = wp_kses_post( $v ); 
            }
            
            $clean_data['is_edited'] = true;
            $clean_data['edited_at'] = current_time( 'mysql' );
            
            $wpdb->update(
                $wpdb->prefix . 'ffc_submissions',
                array(
                    'email' => $new_email,
                    'data'  => wp_json_encode( $clean_data )
                ),
                array( 'id' => $id )
            );
            
            $this->redirect_with_msg( 'updated' );
        }
    }

    /**
     * View Router: Decides between list or edit view.
     */
>>>>>>> Stashed changes
    public function display_submissions_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        ( 'edit' === $action ) ? $this->render_edit_page() : $this->render_list_page();
    }
<<<<<<< Updated upstream
    
=======

    /**
     * Displays the submissions table.
     */
>>>>>>> Stashed changes
    private function render_list_page() {
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-submissions-list-table.php';
        $table = new FFC_Submission_List( $this->submission_handler );
        $table->prepare_items();
        
        $this->display_admin_notices();
        ?> 
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Certificate Submissions', 'ffc' ); ?></h1>
            <div class="ffc-admin-top-actions" style="margin: 10px 0;">
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="ffc_action" value="export_csv_smart">
                    <?php wp_nonce_field( 'ffc_export_csv_nonce', 'ffc_export_csv_action' ); ?>
                    <button type="submit" class="button button-primary"><?php _e( 'Export CSV', 'ffc' ); ?></button>
                </form>
            </div>
            <hr class="wp-header-end">
            <form method="GET">
                <input type="hidden" name="post_type" value="ffc_form">
                <input type="hidden" name="page" value="ffc-submissions">
                <?php 
                $table->views(); 
                $table->search_box( __( 'Search...', 'ffc' ), 's' ); 
                $table->display(); 
                ?>
            </form>
        </div> 
        <?php
    }

<<<<<<< Updated upstream
    private function redirect_with_msg($msg) {
        $url = remove_query_arg(array('action', 'action2', 'submission_id', 'submission', '_wpnonce'), $_SERVER['REQUEST_URI']);
        wp_redirect( add_query_arg('msg', $msg, $url) );
        exit;
    }

    private function display_admin_notices() {
        if (!isset($_GET['msg'])) return;
        $msg = $_GET['msg'];
        $text = '';
        $type = 'updated';
        switch ($msg) {
            case 'trash':   $text = __('Item moved to trash.', 'ffc'); break;
            case 'restore': $text = __('Item restored.', 'ffc'); break;
            case 'delete':  $text = __('Item permanently deleted.', 'ffc'); break;
            case 'bulk_done': $text = __('Bulk action completed.', 'ffc'); break;
            case 'updated': $text = __('Submission updated successfully.', 'ffc'); break;
        }
        if ($text) {
            echo "<div class='$type notice is-dismissible'><p>$text</p></div>";
        }
    }
    
    private function render_edit_page() {
        $submission_id = isset( $_GET['submission_id'] ) ? absint( $_GET['submission_id'] ) : 0;
        $sub = $this->submission_handler->get_submission( $submission_id );
=======
    /**
     * Renders the submission edit form.
     */
    private function render_edit_page() {
        $id  = absint( $_GET['submission_id'] );
        $sub = $this->submission_handler->get_submission( $id );
        
>>>>>>> Stashed changes
        if ( ! $sub ) { 
            echo '<div class="wrap"><p>' . __( 'Submission not found.', 'ffc' ) . '</p></div>'; 
            return; 
        }
<<<<<<< Updated upstream
        $sub_array = (array) $sub;
        $data = json_decode( $sub_array['data'], true ) ?: array(); 
        $fields = get_post_meta( $sub_array['form_id'], '_ffc_form_fields', true );
        $protected_fields = array( 'auth_code', 'fill_date', 'ticket' );
        ?> 
        <div class="wrap">
            <h1><?php printf( __( 'Edit Submission #%s', 'ffc' ), $sub_array['id'] ); ?></h1>
            <?php if ( isset( $data['is_edited'] ) && $data['is_edited'] == true ): ?>
                <div class="ffc-admin-notice ffc-admin-notice-warning">
                    <p><strong>⚠️ <?php _e( 'Warning:', 'ffc' ); ?></strong> <?php _e( 'This record was manually edited on', 'ffc' ); ?> <u><?php echo esc_html($data['edited_at']); ?></u>.</p>
                </div>
            <?php endif; ?>
            <form method="POST" class="ffc-edit-submission-form">
                <?php wp_nonce_field( 'ffc_edit_submission_nonce', 'ffc_edit_submission_action' ); ?>
                <input type="hidden" name="submission_id" value="<?php echo $sub_array['id']; ?>">
=======

        $data   = json_decode( $sub->data, true ) ?: array(); 
        $fields = get_post_meta( $sub->form_id, '_ffc_form_fields', true );
        $protected = array( 'auth_code', 'auth_hash', 'fill_date' );
        ?> 
        <div class="wrap">
            <h1><?php printf( __( 'Edit Submission #%s', 'ffc' ), $id ); ?></h1>
            
            <form method="POST" class="ffc-edit-card" style="background:#fff; padding:20px; border:1px solid #ccd0d4; max-width:800px;">
                <?php wp_nonce_field( 'ffc_edit_submission_nonce', 'ffc_edit_submission_action' ); ?>
                <input type="hidden" name="submission_id" value="<?php echo $id; ?>">
                
>>>>>>> Stashed changes
                <table class="form-table">
                    <tr>
                        <th><label><?php _e( 'User Email', 'ffc' ); ?></label></th>
                        <td><input type="email" name="user_email" value="<?php echo esc_attr($sub->email); ?>" class="regular-text"></td>
                    </tr>
<<<<<<< Updated upstream
                    <?php if(is_array($data)): foreach($data as $k => $v): 
                        if ($k === 'is_edited' || $k === 'edited_at') continue;
                        $lbl = $k; 
                        if(is_array($fields)) { 
                            foreach($fields as $f) { 
                                if(isset($f['name']) && $f['name'] === $k) $lbl = $f['label']; 
                            } 
                        }
                        $is_protected = in_array( $k, $protected_fields );
                        $field_class = $is_protected ? 'regular-text ffc-input-readonly' : 'regular-text';
                        $readonly_attr = $is_protected ? 'readonly' : ''; 
                        ?>
                        <tr>
                            <th><?php echo esc_html($lbl); ?></th>
                            <td>
                                <?php $display_value = is_array($v) ? implode(', ', $v) : $v; ?>
                                <input type="text" name="data[<?php echo $k; ?>]" value="<?php echo esc_attr($display_value); ?>" class="<?php echo $field_class; ?>" <?php echo $readonly_attr; ?>>
                                <?php if($is_protected): ?>
                                    <p class="description"><?php _e('Protected internal field.', 'ffc'); ?></p>
                                <?php endif; ?>
                            </td>
=======
                    <?php foreach( $data as $k => $v ): 
                        if ( in_array( $k, array( 'is_edited', 'edited_at' ) ) ) continue;
                        $readonly = in_array( $k, $protected ) ? 'readonly style="background:#f0f0f1;"' : '';
                    ?>
                        <tr>
                            <th><?php echo esc_html( ucfirst($k) ); ?></th>
                            <td><input type="text" name="data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($v); ?>" class="regular-text" <?php echo $readonly; ?>></td>
>>>>>>> Stashed changes
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p class="submit">
                    <button type="submit" name="ffc_save_edit" class="button button-primary"><?php _e( 'Save Changes', 'ffc' ); ?></button> 
                    <a href="<?php echo admin_url('edit.php?post_type=ffc_form&page=ffc-submissions'); ?>" class="button"><?php _e( 'Cancel', 'ffc' ); ?></a>
                </p>
            </form>
        </div> 
        <?php
    }

<<<<<<< Updated upstream
    public function handle_submission_edit_save() {
        if ( isset( $_POST['ffc_save_edit'] ) && check_admin_referer( 'ffc_edit_submission_nonce', 'ffc_edit_submission_action' ) ) {
            $id        = absint( $_POST['submission_id'] ); 
            $new_email = sanitize_email( $_POST['user_email'] ); 
            $raw_data  = isset($_POST['data']) ? $_POST['data'] : array();
            $clean_data = array(); 
            foreach($raw_data as $k => $v) { 
                $clean_data[sanitize_key($k)] = wp_kses($v, FFC_Utils::get_allowed_html_tags()); 
            }
            $clean_data['is_edited'] = true;
            $clean_data['edited_at'] = current_time('mysql');
            $this->submission_handler->update_submission($id, $new_email, $clean_data);
            wp_redirect(admin_url('edit.php?post_type=ffc_form&page=ffc-submissions&msg=updated')); 
            exit;
        }
    }

    public function handle_csv_export_on_admin_init() {
        if ( isset( $_POST['ffc_action'] ) && $_POST['ffc_action'] === 'export_csv_smart' ) {
            check_admin_referer( 'ffc_export_csv_nonce', 'ffc_export_csv_action' );
            $this->submission_handler->export_csv();
        }
    }

    public function ajax_admin_get_pdf_data() {
        check_ajax_referer( 'ffc_admin_pdf_nonce', 'nonce' );
        $id = isset($_POST['submission_id']) ? absint($_POST['submission_id']) : 0;
        $sub = $this->submission_handler->get_submission($id);
        if ( ! $sub ) { wp_send_json_error(); }
        $sub_array = (array) $sub;
        $data = json_decode( $sub_array['data'], true ); 
        if ( ! is_array( $data ) ) { $data = json_decode( stripslashes( $sub_array['data'] ), true ); }
        $data['email'] = $sub_array['email'];
        $form_id = $sub_array['form_id'];
        $form_title = get_the_title($form_id);
        $config = get_post_meta( $form_id, '_ffc_form_config', true );
        $bg_image_url = get_post_meta( $form_id, '_ffc_form_bg', true );
        $processed_html = $this->submission_handler->generate_pdf_html( $data, $form_title, $config );
        wp_send_json_success(array(
            'template'   => $processed_html,
            'submission' => $data,
            'bg_image'   => $bg_image_url,
            'form_title' => $form_title
        ));
=======
    private function redirect_with_msg( $msg ) {
        $url = remove_query_arg( array( 'action', 'submission_id', 'submission', '_wpnonce' ), $_SERVER['REQUEST_URI'] );
        wp_safe_redirect( add_query_arg( 'msg', $msg, $url ) );
        exit;
    }

    private function display_admin_notices() {
        if ( ! isset( $_GET['msg'] ) ) return;
        $messages = array(
            'trash'      => __( 'Item moved to trash.', 'ffc' ),
            'restore'    => __( 'Item restored successfully.', 'ffc' ),
            'delete'     => __( 'Item permanently deleted.', 'ffc' ),
            'bulk_done'  => __( 'Bulk action completed.', 'ffc' ),
            'updated'    => __( 'Submission updated.', 'ffc' ),
            'email_sent' => __( 'Email sent successfully.', 'ffc' ),
        );
        $text = $messages[ $_GET['msg'] ] ?? '';
        if ( $text ) echo "<div class='updated notice is-dismissible'><p>$text</p></div>";
>>>>>>> Stashed changes
    }
}