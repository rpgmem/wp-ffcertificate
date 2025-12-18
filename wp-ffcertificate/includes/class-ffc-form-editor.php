<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Form_Editor {

    public function __construct() {
        // Initialize Metaboxes
        add_action( 'add_meta_boxes', array( $this, 'add_custom_metaboxes' ), 20 );
        
        // Save Actions
        add_action( 'save_post', array( $this, 'save_form_data' ) );
        add_action( 'admin_notices', array( $this, 'display_save_errors' ) );

        // AJAX 1: Ticket Generator
        add_action( 'wp_ajax_ffc_generate_codes', array( $this, 'ajax_generate_random_codes' ) );

        // AJAX 2: Load HTML Template (ADDED)
        add_action( 'wp_ajax_ffc_load_template', array( $this, 'ajax_load_template' ) );
    }

    // =========================================================================
    // 1. REGISTER METABOXES
    // =========================================================================
    public function add_custom_metaboxes() {
        // Cleanup old boxes
        remove_meta_box( 'ffc_form_builder', 'ffc_form', 'normal' );
        remove_meta_box( 'ffc_form_config', 'ffc_form', 'normal' );
        remove_meta_box( 'ffc_builder_box', 'ffc_form', 'normal' ); 

        // 1. Layout
        add_meta_box( 'ffc_box_layout', __( '1. Certificate Layout', 'ffc' ), array( $this, 'render_box_layout' ), 'ffc_form', 'normal', 'high' );

        // 2. Form Builder
        add_meta_box( 'ffc_box_builder', __( '2. Form Builder (Fields)', 'ffc' ), array( $this, 'render_box_builder' ), 'ffc_form', 'normal', 'high' );

        // 3. Restriction
        add_meta_box( 'ffc_box_restriction', __( '3. Restriction & Security', 'ffc' ), array( $this, 'render_box_restriction' ), 'ffc_form', 'normal', 'high' );

        // 4. Email
        add_meta_box( 'ffc_box_email', __( '4. Email Configuration', 'ffc' ), array( $this, 'render_box_email' ), 'ffc_form', 'normal', 'high' );
    }

    // =========================================================================
    // 2. RENDERING (HTML BLOCKS)
    // =========================================================================

    // --- BOX 1: LAYOUT ---
    public function render_box_layout( $post ) {
        $config = get_post_meta( $post->ID, '_ffc_form_config', true );
        $layout = isset( $config['pdf_layout'] ) ? $config['pdf_layout'] : '';
        $bg_image = isset( $config['bg_image'] ) ? $config['bg_image'] : '';
        
        // List HTML files from plugin folder
        $templates_dir = FFC_PLUGIN_DIR . 'html/';
        $templates = glob( $templates_dir . '*.html' );
        
        wp_nonce_field( 'ffc_save_form_data', 'ffc_form_nonce' );
        ?>
        <table class="form-table">
            <tr>
                <th><label><?php esc_html_e( 'Actions', 'ffc' ); ?></label></th>
                <td>
                    <div style="display:flex; gap:10px; align-items: center; flex-wrap: wrap;">
                        
                        <div>
                            <input type="file" id="ffc_import_html_file" accept=".html,.txt" style="display:none;">
                            <button type="button" class="button" id="ffc_btn_import_html">
                                <span class="dashicons dashicons-upload" style="margin-top:3px;"></span> 
                                <?php esc_html_e( 'Import HTML File', 'ffc' ); ?>
                            </button>
                        </div>

                        <div>
                            <button type="button" class="button" id="ffc_btn_media_lib">
                                <span class="dashicons dashicons-format-image" style="margin-top:3px;"></span> 
                                <?php esc_html_e( 'Select Background', 'ffc' ); ?>
                            </button>
                        </div>

                        <?php if($templates): ?>
                        <div style="margin-left:auto; display:flex; gap:5px; border-left:1px solid #ccc; padding-left:10px;">
                            <select id="ffc_template_select" style="max-width:200px;">
                                <option value=""><?php esc_html_e( 'Select Server Template...', 'ffc' ); ?></option>
                                <?php foreach($templates as $tpl): $filename = basename($tpl); ?>
                                    <option value="<?php echo esc_attr($filename); ?>"><?php echo esc_html($filename); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="ffc_load_template_btn" class="button"><?php esc_html_e( 'Load Template', 'ffc' ); ?></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label><strong><?php esc_html_e( 'Certificate HTML Editor', 'ffc' ); ?></strong></label><br>
                    <textarea name="ffc_config[pdf_layout]" id="ffc_pdf_layout" style="width:100%; height:400px; font-family:monospace; background:#2c3338; color:#fff; padding:10px; margin-top:5px;"><?php echo esc_textarea( $layout ); ?></textarea>
                    <p class="description"><?php esc_html_e( 'Mandatory Tags:', 'ffc' ); ?> <code>{{auth_code}}</code>, <code>{{name}}</code>, <code>{{cpf_rf}}</code>.</p>
                    
                    <div style="margin-top:10px;">
                        <label><strong><?php esc_html_e( 'Background Image URL:', 'ffc' ); ?></strong></label><br>
                        <input type="text" name="ffc_config[bg_image]" id="ffc_bg_image_input" value="<?php echo esc_attr( $bg_image ); ?>" style="width:100%;">
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    // --- BOX 2: BUILDER ---
    public function render_box_builder( $post ) {
        $fields = get_post_meta( $post->ID, '_ffc_form_fields', true );

        if ( empty( $fields ) && $post->post_status === 'auto-draft' ) {
            $fields = array(
                array( 'type' => 'text', 'label' => 'Full Name', 'name' => 'name', 'required' => '1' ),
                array( 'type' => 'email', 'label' => 'Email', 'name' => 'email', 'required' => '1' ),
                array( 'type' => 'text', 'label' => 'CPF / ID', 'name' => 'cpf_rf', 'required' => '1' )
            );
        }
        ?>
        <div id="ffc-fields-container">
            <?php if ( ! empty( $fields ) ) : foreach ( $fields as $index => $field ) : ?>
                <?php $this->render_field_row( $index, $field ); ?>
            <?php endforeach; endif; ?>
        </div>
        
        <div style="margin-top:15px; border-top:1px solid #eee; padding-top:10px;">
            <button type="button" class="button button-primary ffc-add-field"><?php esc_html_e( '+ Add Field', 'ffc' ); ?></button>
        </div>

        <div class="ffc-field-row ffc-field-template" style="display:none;">
            <?php $this->render_field_row( 'TEMPLATE', array() ); ?>
        </div>
        <?php
    }

    // --- BOX 3: RESTRICTION ---
    public function render_box_restriction( $post ) {
        $config = get_post_meta( $post->ID, '_ffc_form_config', true );
        
        $enable    = isset($config['enable_restriction']) ? $config['enable_restriction'] : '0';
        $allow     = isset($config['allowed_users_list']) ? $config['allowed_users_list'] : '';
        $deny      = isset($config['denied_users_list']) ? $config['denied_users_list'] : ''; 
        $vcode     = isset($config['validation_code']) ? $config['validation_code'] : ''; 
        $gen_codes = isset($config['generated_codes_list']) ? $config['generated_codes_list'] : ''; 
        ?>
        <p class="description"><?php esc_html_e( 'Configure who can issue certificates. You can use CPFs (Allowlist) or generate Access Codes (Tickets).', 'ffc' ); ?></p>
        <table class="form-table">
            
            <tr>
                <th><label><?php esc_html_e( 'Single Password (Optional)', 'ffc' ); ?></label></th>
                <td>
                    <input type="text" name="ffc_config[validation_code]" value="<?php echo esc_attr($vcode); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e( 'If defined, all users must enter this password to unlock the form.', 'ffc' ); ?></p>
                </td>
            </tr>

            <tr>
                <th><label><?php esc_html_e( 'Restriction Mode', 'ffc' ); ?></label></th>
                <td>
                    <select name="ffc_config[enable_restriction]">
                        <option value="0" <?php selected($enable, '0'); ?>><?php esc_html_e( 'Disabled (Open to everyone)', 'ffc' ); ?></option>
                        <option value="1" <?php selected($enable, '1'); ?>><?php esc_html_e( 'Enabled (Requires Allowlist OR Valid Ticket)', 'ffc' ); ?></option>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label><?php esc_html_e( 'Allowlist (CPFs / IDs)', 'ffc' ); ?></label></th>
                <td>
                    <textarea name="ffc_config[allowed_users_list]" placeholder="12345678900&#10;98765432100" style="width:100%; height:120px; font-family:monospace;"><?php echo esc_textarea($allow); ?></textarea>
                    <p class="description"><?php esc_html_e( 'List of documents (CPFs/IDs). Requires a field with variable', 'ffc' ); ?> <code>cpf_rf</code>.</p>
                </td>
            </tr>

            <tr>
                <th><label><?php esc_html_e( 'Denylist (Blocked)', 'ffc' ); ?></label></th>
                <td>
                    <textarea name="ffc_config[denied_users_list]" placeholder="<?php esc_attr_e( 'CPF or Code per line', 'ffc' ); ?>" style="width:100%; height:80px;"><?php echo esc_textarea($deny); ?></textarea>
                    <p class="description"><?php esc_html_e( 'Users in this list will be blocked even if restriction mode is disabled.', 'ffc' ); ?></p>
                </td>
            </tr>
            
            <tr style="background:#f0f6fc; border-top:1px solid #ddd;">
                <th><label style="color:#2271b1; font-weight:bold;"><?php esc_html_e( 'Ticket Generator', 'ffc' ); ?></label></th>
                <td>
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom: 5px;">
                        <input type="number" id="ffc_qty_codes" value="10" min="1" max="500" style="width:70px;">
                        <button type="button" class="button button-secondary" id="ffc_btn_generate_codes"><?php esc_html_e( 'Generate Tickets', 'ffc' ); ?></button>
                        <span id="ffc_gen_status" style="font-style:italic; color:#666;"></span>
                    </div>
                    
                    <textarea name="ffc_config[generated_codes_list]" id="ffc_generated_list" placeholder="<?php esc_attr_e( 'Generated codes will appear here...', 'ffc' ); ?>" style="width:100%; height:120px; font-family:monospace; background:#fff;"><?php echo esc_textarea($gen_codes); ?></textarea>
                    
                    <p class="description">
                        <strong><?php esc_html_e( 'How to use:', 'ffc' ); ?></strong> <?php esc_html_e( 'Create a field in Form Builder with variable (Name)', 'ffc' ); ?> <code>ticket</code>.<br>
                        <?php esc_html_e( 'The user must enter one of these codes to issue. Once used, the code is removed (burned).', 'ffc' ); ?>
                    </p>
                </td>
            </tr>

        </table>
        <?php
    }

    // --- BOX 4: EMAIL ---
    public function render_box_email( $post ) {
        $config = get_post_meta( $post->ID, '_ffc_form_config', true );
        $send_email = isset($config['send_user_email']) ? $config['send_user_email'] : '0';
        $subject    = isset($config['email_subject']) ? $config['email_subject'] : 'Your Certificate';
        $body       = isset($config['email_body']) ? $config['email_body'] : "Hello,\n\nPlease find your certificate attached.\n\nRegards.";
        ?>
        <table class="form-table">
            <tr>
                <th><label><?php esc_html_e( 'Send Email?', 'ffc' ); ?></label></th>
                <td>
                    <select name="ffc_config[send_user_email]">
                        <option value="0" <?php selected($send_email, '0'); ?>><?php esc_html_e( 'No (Download only)', 'ffc' ); ?></option>
                        <option value="1" <?php selected($send_email, '1'); ?>><?php esc_html_e( 'Yes (Send with PDF attachment)', 'ffc' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Subject', 'ffc' ); ?></label></th>
                <td>
                    <input type="text" name="ffc_config[email_subject]" value="<?php echo esc_attr($subject); ?>" class="regular-text" style="width:100%;">
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Email Body', 'ffc' ); ?></label></th>
                <td>
                    <textarea name="ffc_config[email_body]" style="width:100%; height:120px;"><?php echo esc_textarea($body); ?></textarea>
                </td>
            </tr>
        </table>
        <?php
    }

    // --- HELPER: BUILDER ROW ---
    private function render_field_row( $index, $field ) {
        $type = isset( $field['type'] ) ? $field['type'] : 'text';
        $label = isset( $field['label'] ) ? $field['label'] : '';
        $name = isset( $field['name'] ) ? $field['name'] : '';
        $req = isset( $field['required'] ) ? $field['required'] : '';
        $opts = isset( $field['options'] ) ? $field['options'] : '';
        ?>
        <div class="ffc-field-row" style="background:#fff; border:1px solid #ccd0d4; padding:15px; margin-bottom:10px; border-left: 4px solid #2271b1; cursor:move;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <span class="ffc-sort-handle" style="cursor:move; color:#999; font-size:18px;">☰ <span style="font-size:12px; font-weight:bold; color:#333;"><?php esc_html_e( 'Field', 'ffc' ); ?></span></span>
                <button type="button" class="button button-link-delete ffc-remove-field" style="color:#b32d2e;"><?php esc_html_e( 'Delete', 'ffc' ); ?></button>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:10px; align-items:end;">
                <label>
                    <span style="display:block; font-size:11px; color:#666;"><?php esc_html_e( 'Label', 'ffc' ); ?></span>
                    <input type="text" name="ffc_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr( $label ); ?>" style="width:100%;">
                </label>
                
                <label>
                    <span style="display:block; font-size:11px; color:#666;"><?php esc_html_e( 'Variable (Name)', 'ffc' ); ?></span>
                    <input type="text" name="ffc_fields[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $name ); ?>" style="width:100%;">
                </label>

                <label>
                    <span style="display:block; font-size:11px; color:#666;"><?php esc_html_e( 'Type', 'ffc' ); ?></span>
                    <select name="ffc_fields[<?php echo $index; ?>][type]" class="ffc-field-type-select" style="width:100%;">
                        <option value="text" <?php selected($type, 'text'); ?>><?php esc_html_e( 'Text', 'ffc' ); ?></option>
                        <option value="email" <?php selected($type, 'email'); ?>><?php esc_html_e( 'Email', 'ffc' ); ?></option>
                        <option value="number" <?php selected($type, 'number'); ?>><?php esc_html_e( 'Number', 'ffc' ); ?></option>
                        <option value="select" <?php selected($type, 'select'); ?>><?php esc_html_e( 'Select (List)', 'ffc' ); ?></option>
                        <option value="radio" <?php selected($type, 'radio'); ?>><?php esc_html_e( 'Radio', 'ffc' ); ?></option>
                    </select>
                </label>
                
                <label style="padding-bottom:5px;">
                    <input type="checkbox" name="ffc_fields[<?php echo $index; ?>][required]" value="1" <?php checked($req, '1'); ?>> <?php esc_html_e( 'Required', 'ffc' ); ?>
                </label>
            </div>

            <div class="ffc-options-field" style="margin-top:10px; background:#f0f0f1; padding:10px; display:<?php echo ($type=='select'||$type=='radio')?'block':'none'; ?>;">
                <label><?php esc_html_e( 'Options (comma separated):', 'ffc' ); ?> <input type="text" name="ffc_fields[<?php echo $index; ?>][options]" value="<?php echo esc_attr( $opts ); ?>" style="width:100%;"></label>
            </div>
        </div>
        <?php
    }

    // =========================================================================
    // 3. AJAX (CODE GENERATOR & TEMPLATE LOADER)
    // =========================================================================
    
    // A. Generator (Tickets)
    public function ajax_generate_random_codes() {
        // Use 'ffc_admin_pdf_nonce' as standard for this editor
        check_ajax_referer( 'ffc_admin_pdf_nonce', 'nonce' );
        
        $qty = isset($_POST['qty']) ? absint($_POST['qty']) : 10;
        if($qty <= 0) $qty = 1;
        if($qty > 500) $qty = 500;

        $codes = array();
        for($i = 0; $i < $qty; $i++) {
            $rnd = strtoupper(bin2hex(random_bytes(4))); 
            $formatted = substr($rnd, 0, 4) . '-' . substr($rnd, 4, 4);
            $codes[] = $formatted;
        }
        
        wp_send_json_success( array( 'codes' => implode("\n", $codes) ) );
    }

    // B. Template Loader (Server Side) - ADICIONADO
    public function ajax_load_template() {
        // Use 'ffc_admin_pdf_nonce' to match JS
        check_ajax_referer( 'ffc_admin_pdf_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ffc' ) );
        }

        $filename = isset($_POST['filename']) ? sanitize_file_name($_POST['filename']) : '';
        
        // Correção: Adicionar whitelist de arquivos permitidos
        $allowed_templates = array( 'atestado_estagios.html', 'certificado_1.html', 'certificado_2.html', 'layout.html' ); // Define a safe list based on the actual files
        if ( ! in_array( $filename, $allowed_templates ) ) {
            wp_send_json_error( __( 'Template not allowed.', 'ffc' ) );
        }
        
        if ( empty($filename) ) {
            wp_send_json_error( __( 'No file selected.', 'ffc' ) );
        }

        $filepath = FFC_PLUGIN_DIR . 'html/' . $filename;

        if ( ! file_exists( $filepath ) ) {
            wp_send_json_error( __( 'File not found on server.', 'ffc' ) );
        }

        $content = file_get_contents( $filepath );
        
        if ( $content === false ) {
            wp_send_json_error( __( 'Could not read file.', 'ffc' ) );
        }

        wp_send_json_success( $content );
    }

    // =========================================================================
    // 4. SAVE WITH VALIDATION
    // =========================================================================
    public function save_form_data( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( ! isset( $_POST['ffc_form_nonce'] ) || ! wp_verify_nonce( $_POST['ffc_form_nonce'], 'ffc_save_form_data' ) ) return;

        // 1. Save Fields
        if ( isset( $_POST['ffc_fields'] ) && is_array( $_POST['ffc_fields'] ) ) {
            $raw_fields = $_POST['ffc_fields'];
            $clean_fields = array();

            foreach ( $raw_fields as $index => $field ) {
                if ( $index === 'TEMPLATE' ) continue;
                if ( empty( trim( $field['label'] ) ) && empty( trim( $field['name'] ) ) ) continue;

                $clean_fields[] = array(
                    'label'    => sanitize_text_field( $field['label'] ),
                    'name'     => sanitize_key( $field['name'] ),
                    'type'     => sanitize_key( $field['type'] ),
                    'required' => isset( $field['required'] ) ? '1' : '',
                    'options'  => sanitize_text_field( isset( $field['options'] ) ? $field['options'] : '' ),
                );
            }
            update_post_meta( $post_id, '_ffc_form_fields', $clean_fields );
        }

        // 2. Save Configs
        if ( isset( $_POST['ffc_config'] ) ) {
            $config = $_POST['ffc_config'];
            $html_layout = isset($config['pdf_layout']) ? $config['pdf_layout'] : '';

            // Restrict tags for security (only basic tags for PDF layouts)
            $allowed_tags = array(
            // Tags originais
            'p'      => array('style' => array()),
            'strong' => array('style' => array()),
            'em'     => array(),
            'id'     => array(),
            'img'     => array('style' => array(),'src' => array()),
            'br'     => array(),
            'div'    => array('style' => array()),
            'span'   => array('style' => array()),
    
            // --- ADICIONADO SUPORTE A TABELAS ---
            'table'  => array('style' => array(), 'border' => array(), 'width' => array(), 'cellspacing' => array(), 'cellpadding' => array()),
            'thead'  => array('style' => array()),
            'tbody'  => array('style' => array()),
            'tfoot'  => array('style' => array()),
            'tr'     => array('style' => array()),
            'th'     => array('style' => array(), 'scope' => array(), 'colspan' => array(), 'rowspan' => array()),
            'td'     => array('style' => array(), 'colspan' => array(), 'rowspan' => array())
        );
            $config['pdf_layout']       = wp_kses( $html_layout, $allowed_tags );
            $config['bg_image']         = esc_url_raw( isset($config['bg_image']) ? $config['bg_image'] : '' );
            $config['email_body']       = isset($config['email_body']) ? sanitize_textarea_field( $config['email_body'] ) : '';
            $config['allowed_users_list'] = isset($config['allowed_users_list']) ? sanitize_textarea_field( $config['allowed_users_list'] ) : '';
            $config['denied_users_list']  = isset($config['denied_users_list']) ? sanitize_textarea_field( $config['denied_users_list'] ) : '';
            $config['validation_code']    = isset($config['validation_code']) ? sanitize_text_field( $config['validation_code'] ) : '';
            $config['generated_codes_list'] = isset($config['generated_codes_list']) ? sanitize_textarea_field( $config['generated_codes_list'] ) : '';

            $missing_tags = array();
            if ( strpos( $html_layout, '{{auth_code}}' ) === false ) $missing_tags[] = '{{auth_code}}';
            if ( strpos( $html_layout, '{{name}}' ) === false && strpos( $html_layout, '{{nome}}' ) === false ) $missing_tags[] = '{{name}}';
            if ( strpos( $html_layout, '{{cpf_rf}}' ) === false ) $missing_tags[] = '{{cpf_rf}}';

            if ( ! empty( $missing_tags ) ) {
                set_transient( 'ffc_save_error_' . get_current_user_id(), $missing_tags, 45 );
            }

            $current_config = get_post_meta( $post_id, '_ffc_form_config', true );
            if(!is_array($current_config)) $current_config = array();
            $final_config = array_merge($current_config, $config);

            update_post_meta( $post_id, '_ffc_form_config', $final_config );
        }
    }

    public function display_save_errors() {
        $error_tags = get_transient( 'ffc_save_error_' . get_current_user_id() );
        if ( $error_tags ) {
            delete_transient( 'ffc_save_error_' . get_current_user_id() );
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong><?php esc_html_e( 'Warning! Missing required tags in PDF Layout:', 'ffc' ); ?></strong>
                    <code><?php echo implode( ', ', $error_tags ); ?></code>.
                    <?php esc_html_e( 'The certificate might not generate correctly.', 'ffc' ); ?>
                </p>
            </div>
            <?php
        }
    }
}