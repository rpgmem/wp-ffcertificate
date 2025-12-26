<?php
<<<<<<< Updated upstream
=======
/**
 * FFC_Form_Editor
 * Handles the administrative UI for building forms, layouts, and rules.
 */

>>>>>>> Stashed changes
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Form_Editor {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_custom_metaboxes' ), 20 );
        add_action( 'save_post', array( $this, 'save_form_data' ) );
        add_action( 'admin_notices', array( $this, 'display_save_errors' ) );

<<<<<<< Updated upstream
=======
        // AJAX handlers
>>>>>>> Stashed changes
        add_action( 'wp_ajax_ffc_generate_codes', array( $this, 'ajax_generate_random_codes' ) );
    }

<<<<<<< Updated upstream
    public function add_custom_metaboxes() {
        // Limpa metaboxes antigos para evitar duplicidade
=======
    /**
     * Registers the metaboxes for the ffc_form post type.
     */
    public function add_custom_metaboxes() {
        // Remove default or redundant boxes if necessary
>>>>>>> Stashed changes
        remove_meta_box( 'ffc_form_builder', 'ffc_form', 'normal' );

        add_meta_box( 'ffc_box_layout', __( '1. Certificate Layout', 'ffc' ), array( $this, 'render_box_layout' ), 'ffc_form', 'normal', 'high' );
        add_meta_box( 'ffc_box_builder', __( '2. Form Builder (Fields)', 'ffc' ), array( $this, 'render_box_builder' ), 'ffc_form', 'normal', 'high' );
        add_meta_box( 'ffc_box_extra_templates', __( '3. Conditional Layout Rules', 'ffc' ), array( $this, 'render_box_extra_templates' ), 'ffc_form', 'normal', 'high' );
        add_meta_box( 'ffc_box_restriction', __( '4. Restriction & Security', 'ffc' ), array( $this, 'render_box_restriction' ), 'ffc_form', 'normal', 'high' );
        add_meta_box( 'ffc_box_email', __( '5. Email Configuration', 'ffc' ), array( $this, 'render_box_email' ), 'ffc_form', 'normal', 'high' );
    }

<<<<<<< Updated upstream
=======
    /**
     * Section 1: Certificate Layout
     */
>>>>>>> Stashed changes
    public function render_box_layout( $post ) {
        $config = get_post_meta( $post->ID, '_ffc_form_config', true );
        $layout = isset( $config['pdf_layout'] ) ? $config['pdf_layout'] : '';
        $bg_image = isset( $config['bg_image'] ) ? $config['bg_image'] : '';
        
        $templates_dir = FFC_PLUGIN_DIR . 'html/';
        $templates = glob( $templates_dir . '*.html' );
        
        wp_nonce_field( 'ffc_save_form_data', 'ffc_form_nonce' );
        ?>
<<<<<<< Updated upstream
        <table class="form-table">
            <tr>
                <th><label><?php esc_html_e( 'Actions', 'ffc' ); ?></label></th>
                <td>
                    <div class="ffc-admin-flex-row ffc-flex-wrap">
                        <div class="ffc-action-group">
                            <input type="file" id="ffc_import_html_file" accept=".html,.txt" class="ffc-hidden">
                            <button type="button" class="button" id="ffc_btn_import_html">
                                <span class="dashicons dashicons-upload"></span> 
                                <?php esc_html_e( 'Import HTML', 'ffc' ); ?>
                            </button>
                            <button type="button" class="button" id="ffc_btn_media_lib">
                                <span class="dashicons dashicons-format-image"></span> 
                                <?php esc_html_e( 'Background', 'ffc' ); ?>
                            </button>
                        </div>

                        <?php if($templates): ?>
                        <div class="ffc-template-loader">
                            <select id="ffc_template_select">
                                <option value=""><?php esc_html_e( 'Select Server Template...', 'ffc' ); ?></option>
                                <?php foreach($templates as $tpl): $filename = basename($tpl); ?>
                                    <option value="<?php echo esc_attr($filename); ?>"><?php echo esc_html($filename); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="ffc_load_template_btn" class="button"><?php esc_html_e( 'Load', 'ffc' ); ?></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label class="ffc-block-label"><strong><?php esc_html_e( 'Certificate HTML Editor', 'ffc' ); ?></strong></label>
                    <textarea name="ffc_config[pdf_layout]" id="ffc_pdf_layout" class="ffc-w100" rows="12"><?php echo esc_textarea( $layout ); ?></textarea>
                    <p class="description"><?php esc_html_e( 'Mandatory Tags:', 'ffc' ); ?> <code>{{auth_code}}</code>, <code>{{name}}</code>, <code>{{cpf_rf}}</code>.</p>
                    
                    <div class="ffc-input-group" style="margin-top: 15px;">
                        <label class="ffc-block-label"><strong><?php esc_html_e( 'Background Image URL:', 'ffc' ); ?></strong></label>
                        <input type="text" name="ffc_config[bg_image]" id="ffc_bg_image_input" value="<?php echo esc_attr( $bg_image ); ?>" class="ffc-w100">
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_box_builder( $post ) {
        $fields = get_post_meta( $post->ID, '_ffc_form_fields', true );

        if ( empty( $fields ) && $post->post_status === 'auto-draft' ) {
            $fields = array(
                array( 'type' => 'text', 'label' => 'Full Name', 'name' => 'name', 'required' => '1', 'options' => '' ),
                array( 'type' => 'email', 'label' => 'Email', 'name' => 'email', 'required' => '1', 'options' => '' ),
                array( 'type' => 'text', 'label' => 'CPF / ID', 'name' => 'cpf_rf', 'required' => '1', 'options' => '' )
=======
        <div class="ffc-editor-toolbar" style="display: flex; gap: 10px; margin-bottom: 15px; align-items: center;">
            <button type="button" class="button" id="ffc_btn_import_html"><span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Import HTML', 'ffc' ); ?></button>
            <button type="button" class="button" id="ffc_btn_media_lib"><span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'Background Image', 'ffc' ); ?></button>
            <button type="button" class="button button-primary" id="ffc_btn_live_preview"><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Live Preview', 'ffc' ); ?></button>
            
            <?php if($templates): ?>
            <div style="margin-left: auto;">
                <select id="ffc_template_select">
                    <option value=""><?php esc_html_e( 'Server Templates...', 'ffc' ); ?></option>
                    <?php foreach($templates as $tpl): $filename = basename($tpl); ?>
                        <option value="<?php echo esc_attr($filename); ?>"><?php echo esc_html($filename); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="ffc_load_template_btn" class="button"><?php esc_html_e( 'Load', 'ffc' ); ?></button>
            </div>
            <?php endif; ?>
        </div>

        <div class="ffc-tag-helper" style="background: #f6f7f7; padding: 10px; border: 1px solid #dcdcde; border-radius: 4px; margin-bottom: 10px;">
            <span style="font-weight: 600; font-size: 12px;"><?php esc_html_e( 'Available Tags:', 'ffc' ); ?></span>
            <?php foreach(['{{name}}', '{{cpf_rf}}', '{{auth_code}}', '{{qrcode}}', '{{date}}'] as $tag): ?>
                <code style="cursor: pointer;" class="ffc-insert-tag" data-tag="<?php echo $tag; ?>"><?php echo $tag; ?></code>
            <?php endforeach; ?>
        </div>

        <textarea name="ffc_config[pdf_layout]" id="ffc_pdf_layout" style="width: 100%; height: 300px; font-family: monospace;"><?php echo esc_textarea( $layout ); ?></textarea>
        
        <p>
            <label><strong><?php esc_html_e( 'Global Background Image URL:', 'ffc' ); ?></strong></label>
            <input type="text" name="ffc_config[bg_image]" id="ffc_bg_image_input" value="<?php echo esc_url( $bg_image ); ?>" style="width: 100%;">
        </p>
        <?php
    }

    /**
     * Section 2: Form Builder
     */
    public function render_box_builder( $post ) {
        $fields = get_post_meta( $post->ID, '_ffc_form_fields', true );
        
        // Default fields for new forms
        if ( empty( $fields ) && $post->post_status === 'auto-draft' ) {
            $fields = array(
                array( 'type' => 'text', 'label' => __( 'Full Name', 'ffc' ), 'name' => 'name', 'required' => '1' ),
                array( 'type' => 'email', 'label' => __( 'Email', 'ffc' ), 'name' => 'email', 'required' => '1' ),
                array( 'type' => 'text', 'label' => __( 'CPF / ID', 'ffc' ), 'name' => 'cpf_rf', 'required' => '1' )
>>>>>>> Stashed changes
            );
        }
        ?>
        <div id="ffc-fields-container">
            <?php 
            if ( is_array($fields) ) {
                foreach ( $fields as $index => $field ) {
                    $this->render_field_row( $index, $field );
                }
            } 
            ?>
        </div>
        <p>
            <button type="button" class="button button-secondary ffc-add-field">
                <span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e( 'Add Field', 'ffc' ); ?>
            </button>
        </p>
        <div id="ffc-field-skeleton" style="display:none;">
            <?php $this->render_field_row( 'TEMPLATE', array() ); ?>
        </div>
        <?php
    }

<<<<<<< Updated upstream
=======
    /**
     * Section 3: Conditional Layouts
     */
    public function render_box_extra_templates( $post ) {
        $rules = get_post_meta( $post->ID, '_ffc_extra_templates', true ) ?: array();
        $fields = get_post_meta( $post->ID, '_ffc_form_fields', true ) ?: array();
        ?>
        <div id="ffc-rules-container">
            <?php foreach($rules as $idx => $rule): ?>
                <div class="ffc-rule-row" style="background:#f0f6fb; border:1px solid #c3d9e8; padding:15px; margin-bottom:15px; border-radius:4px;">
                    <div style="margin-bottom:10px; border-bottom:1px solid #c3d9e8; padding-bottom:10px;">
                        <strong><?php esc_html_e( 'If Field', 'ffc' ); ?></strong> 
                        <select name="ffc_extra_templates[<?php echo $idx; ?>][target]">
                            <?php foreach($fields as $f): ?>
                                <option value="<?php echo esc_attr($f['name']); ?>" <?php selected($rule['target']??'', $f['name']); ?>><?php echo esc_html($f['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <strong><?php esc_html_e( 'equals', 'ffc' ); ?></strong>
                        <input type="text" name="ffc_extra_templates[<?php echo $idx; ?>][value]" value="<?php echo esc_attr($rule['value']??''); ?>">
                        <button type="button" class="button-link-delete ffc-remove-rule" style="float:right;"><?php esc_html_e( 'Remove Rule', 'ffc' ); ?></button>
                    </div>
                    <textarea name="ffc_extra_templates[<?php echo $idx; ?>][layout]" rows="5" style="width:100%; font-family:monospace;" placeholder="<?php esc_attr_e( 'HTML for this condition...', 'ffc' ); ?>"><?php echo esc_textarea($rule['layout']??''); ?></textarea>
                    <input type="text" name="ffc_extra_templates[<?php echo $idx; ?>][bg]" value="<?php echo esc_url($rule['bg']??''); ?>" placeholder="<?php esc_attr_e( 'Condition Background Image URL', 'ffc' ); ?>" style="width:100%; margin-top:5px;">
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button ffc-add-rule"><?php esc_html_e( '+ Add Condition Rule', 'ffc' ); ?></button>
        <?php
    }

    /**
     * Section 4: Restrictions & Tickets
     */
>>>>>>> Stashed changes
    public function render_box_restriction( $post ) {
        $config = get_post_meta( $post->ID, '_ffc_form_config', true );
        $enable = $config['enable_restriction'] ?? '0';
        $vcode  = $config['validation_code'] ?? '';
        $allow  = $config['allowed_users_list'] ?? '';
        $deny   = $config['denied_users_list'] ?? '';
        $gen    = $config['generated_codes_list'] ?? '';
        ?>
        <table class="form-table">
            <tr>
<<<<<<< Updated upstream
                <th><label><?php esc_html_e( 'Single Password (Optional)', 'ffc' ); ?></label></th>
                <td><input type="text" name="ffc_config[validation_code]" value="<?php echo esc_attr($vcode); ?>" class="regular-text" placeholder="Ex: EVENTO2024"></td>
=======
                <th><label><?php esc_html_e( 'Global Password', 'ffc' ); ?></label></th>
                <td><input type="text" name="ffc_config[validation_code]" value="<?php echo esc_attr($vcode); ?>" class="regular-text"></td>
>>>>>>> Stashed changes
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Restriction Mode', 'ffc' ); ?></label></th>
                <td>
                    <select name="ffc_config[enable_restriction]" style="min-width: 200px;">
                        <option value="0" <?php selected($enable, '0'); ?>><?php esc_html_e( 'Disabled (Open)', 'ffc' ); ?></option>
                        <option value="1" <?php selected($enable, '1'); ?>><?php esc_html_e( 'Enabled (Allowlist / Tickets)', 'ffc' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
<<<<<<< Updated upstream
                <th><label><?php esc_html_e( 'Allowlist (CPFs / IDs)', 'ffc' ); ?></label></th>
                <td><textarea name="ffc_config[allowed_users_list]" class="ffc-textarea-mono ffc-h120 ffc-w100" placeholder="Um por linha..."><?php echo esc_textarea($allow); ?></textarea></td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Denylist (Blocked)', 'ffc' ); ?></label></th>
                <td><textarea name="ffc_config[denied_users_list]" class="ffc-textarea-mono ffc-h80 ffc-w100" placeholder="Usuários banidos..."><?php echo esc_textarea($deny); ?></textarea></td>
=======
                <th><label><?php esc_html_e( 'Allowlist (CPFs)', 'ffc' ); ?></label></th>
                <td><textarea name="ffc_config[allowed_users_list]" rows="5" style="width:100%; font-family: monospace;"><?php echo esc_textarea($allow); ?></textarea></td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Denylist (Blocked)', 'ffc' ); ?></label></th>
                <td><textarea name="ffc_config[denied_users_list]" rows="3" style="width:100%; font-family: monospace;"><?php echo esc_textarea($deny); ?></textarea></td>
>>>>>>> Stashed changes
            </tr>
            <tr style="background: #f0f0f1;">
                <th><label><strong><?php esc_html_e( 'Ticket Generator', 'ffc' ); ?></strong></label></th>
                <td>
                    <input type="number" id="ffc_qty_codes" value="10" style="width: 70px;">
                    <button type="button" class="button" id="ffc_btn_generate_codes"><?php esc_html_e( 'Generate', 'ffc' ); ?></button>
                    <textarea name="ffc_config[generated_codes_list]" id="ffc_generated_list" rows="5" style="width:100%; margin-top: 10px; font-family: monospace;"><?php echo esc_textarea($gen); ?></textarea>
                </td>
            </tr>
        </table>
        <?php
    }

<<<<<<< Updated upstream
    public function render_box_email( $post ) {
        $config = get_post_meta( $post->ID, '_ffc_form_config', true );
        $send_email = isset($config['send_user_email']) ? $config['send_user_email'] : '0';
        $subject    = isset($config['email_subject']) ? $config['email_subject'] : 'Your Certificate';
        $body       = isset($config['email_body']) ? $config['email_body'] : '';
=======
    /**
     * Section 5: Email Configuration
     */
    public function render_box_email( $post ) {
        $config = get_post_meta( $post->ID, '_ffc_form_config', true );
        $send = $config['send_user_email'] ?? '0';
        $sub  = $config['email_subject'] ?? __( 'Your Certificate', 'ffc' );
        $body = $config['email_body'] ?? "Hi {{name}}, your certificate is ready.";
>>>>>>> Stashed changes
        ?>
        <table class="form-table">
            <tr>
                <th><label><?php esc_html_e( 'Send Email?', 'ffc' ); ?></label></th>
                <td>
                    <select name="ffc_config[send_user_email]" style="min-width: 200px;">
                        <option value="0" <?php selected($send, '0'); ?>><?php esc_html_e( 'No', 'ffc' ); ?></option>
                        <option value="1" <?php selected($send, '1'); ?>><?php esc_html_e( 'Yes', 'ffc' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Subject', 'ffc' ); ?></th>
                <td><input type="text" name="ffc_config[email_subject]" value="<?php echo esc_attr($sub); ?>" style="width: 100%;"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Body Content', 'ffc' ); ?></th>
                <td>
                    <div style="margin-bottom: 5px;">
                        <?php foreach(['{{name}}', '{{auth_code}}', '{{date}}'] as $tag): ?>
                            <button type="button" class="button button-small ffc-insert-tag-email" data-tag="<?php echo $tag; ?>"><?php echo $tag; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <textarea name="ffc_config[email_body]" id="ffc_email_body" rows="6" style="width: 100%;"><?php echo esc_textarea($body); ?></textarea>
                </td>
            </tr>
        </table>
        <script>
            jQuery(document).ready(function($){
                $('.ffc-insert-tag-email').on('click', function(){
                    var tag = $(this).data('tag'), $area = $('#ffc_email_body'), pos = $area.prop('selectionStart'), text = $area.val();
                    $area.val(text.substring(0, pos) + tag + text.substring(pos)).focus();
                });
            });
        </script>
        <?php
    }

<<<<<<< Updated upstream
    private function render_field_row( $index, $field ) {
        $type  = isset( $field['type'] ) ? $field['type'] : 'text';
        $label = isset( $field['label'] ) ? $field['label'] : '';
        $name  = isset( $field['name'] ) ? $field['name'] : '';
        $req   = isset( $field['required'] ) ? $field['required'] : '';
        $opts  = isset( $field['options'] ) ? $field['options'] : '';
        
        // Determina se o campo de opções deve começar visível
        $options_visible_class = ( $type === 'select' || $type === 'radio' ) ? '' : 'ffc-hidden';
        ?>
        <div class="ffc-field-row" data-index="<?php echo $index; ?>">
            <div class="ffc-field-row-header">
                <span class="ffc-sort-handle">
                    <span class="dashicons dashicons-menu"></span>
                    <span class="ffc-field-title"><strong><?php esc_html_e( 'Field', 'ffc' ); ?></strong></span>
                </span>
                <button type="button" class="button button-link-delete ffc-remove-field"><?php esc_html_e( 'Remove', 'ffc' ); ?></button>
            </div>
            
            <div class="ffc-field-row-grid">
                <div class="ffc-grid-item">
                    <label><?php esc_html_e('Label', 'ffc'); ?></label>
                    <input type="text" name="ffc_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr( $label ); ?>" class="ffc-w100">
                </div>
                <div class="ffc-grid-item">
                    <label><?php esc_html_e('Variable Name', 'ffc'); ?></label>
                    <input type="text" name="ffc_fields[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $name ); ?>" placeholder="ex: curso_nome" class="ffc-w100">
                </div>
                <div class="ffc-grid-item">
                    <label><?php esc_html_e('Type', 'ffc'); ?></label>
                    <select name="ffc_fields[<?php echo $index; ?>][type]" class="ffc-field-type-selector ffc-w100">
                        <option value="text" <?php selected($type, 'text'); ?>><?php esc_html_e('Text', 'ffc'); ?></option>
                        <option value="email" <?php selected($type, 'email'); ?>><?php esc_html_e('Email', 'ffc'); ?></option>
                        <option value="number" <?php selected($type, 'number'); ?>><?php esc_html_e('Number', 'ffc'); ?></option>
                        <option value="date" <?php selected($type, 'date'); ?>><?php esc_html_e('Date', 'ffc'); ?></option>
                        <option value="select" <?php selected($type, 'select'); ?>><?php esc_html_e('Select (Combobox)', 'ffc'); ?></option>
                        <option value="radio" <?php selected($type, 'radio'); ?>><?php esc_html_e('Radio Box', 'ffc'); ?></option>
                        <option value="hidden" <?php selected($type, 'hidden'); ?>><?php esc_html_e('Hidden Field', 'ffc'); ?></option>
=======
    /**
     * Helper: Renders a single field row for the Form Builder.
     */
    private function render_field_row( $index, $field ) {
        $type   = $field['type'] ?? 'text';
        $label  = $field['label'] ?? '';
        $name   = $field['name'] ?? '';
        $req    = $field['required'] ?? '';
        $opts   = $field['options'] ?? '';
        $logic  = $field['logic_enabled'] ?? '0';
        $target = $field['logic_target'] ?? '';
        $val    = $field['logic_value'] ?? '';

        $display_options = ( $type === 'select' || $type === 'radio' ) ? 'block' : 'none';
        ?>
        <div class="ffc-field-row" style="background:#fff; border:1px solid #ccd0d4; padding:15px; margin-bottom:10px; border-radius:4px;">
            <div style="display:grid; grid-template-columns: 1.5fr 1fr 1fr auto; gap:10px; align-items: end;">
                <div><label><?php esc_html_e( 'Label', 'ffc' ); ?></label><input type="text" name="ffc_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($label); ?>" style="width:100%;"></div>
                <div><label><?php esc_html_e( 'Tag/ID', 'ffc' ); ?></label><input type="text" name="ffc_fields[<?php echo $index; ?>][name]" value="<?php echo esc_attr($name); ?>" style="width:100%;"></div>
                <div>
                    <label><?php esc_html_e( 'Type', 'ffc' ); ?></label>
                    <select name="ffc_fields[<?php echo $index; ?>][type]" class="ffc-field-type-trigger" style="width:100%;">
                        <option value="text" <?php selected($type, 'text'); ?>>Text</option>
                        <option value="email" <?php selected($type, 'email'); ?>>Email</option>
                        <option value="select" <?php selected($type, 'select'); ?>>Select</option>
                        <option value="radio" <?php selected($type, 'radio'); ?>>Radio</option>
>>>>>>> Stashed changes
                    </select>
                </div>
                <div style="padding-bottom: 5px;"><label><input type="checkbox" name="ffc_fields[<?php echo $index; ?>][required]" value="1" <?php checked($req, '1'); ?>> <?php esc_html_e( 'Required', 'ffc' ); ?></label></div>
            </div>

            <div class="ffc-options-wrap" style="display:<?php echo $display_options; ?>; margin-top:10px;">
                <label><strong><?php esc_html_e( 'Options (Comma separated)', 'ffc' ); ?></strong></label>
                <input type="text" name="ffc_fields[<?php echo $index; ?>][options]" value="<?php echo esc_attr($opts); ?>" style="width:100%;">
            </div>

            <div style="margin-top:10px; border-top:1px dashed #eee; padding-top:10px;">
                <label><input type="checkbox" name="ffc_fields[<?php echo $index; ?>][logic_enabled]" value="1" <?php checked($logic, '1'); ?>> <?php esc_html_e( 'Show if...', 'ffc' ); ?></label>
                <span class="ffc-logic-controls" style="<?php echo ($logic != '1') ? 'display:none;' : ''; ?>">
                    Field ID: <input type="text" name="ffc_fields[<?php echo $index; ?>][logic_target]" value="<?php echo esc_attr($target); ?>" style="width:100px;">
                    Value: <input type="text" name="ffc_fields[<?php echo $index; ?>][logic_value]" value="<?php echo esc_attr($val); ?>" style="width:100px;">
                </span>
                <button type="button" class="button-link-delete ffc-remove-field" style="float:right;"><?php esc_html_e( 'Remove Field', 'ffc' ); ?></button>
            </div>
        </div>
        <?php
    }

<<<<<<< Updated upstream
    public function ajax_generate_random_codes() {
        check_ajax_referer( 'ffc_admin_pdf_nonce', 'nonce' );
        $qty = isset($_POST['qty']) ? absint($_POST['qty']) : 10;
=======
    /**
     * AJAX: Generate codes using FFC_Utils for consistency.
     */
    public function ajax_generate_random_codes() {
        check_ajax_referer('ffc_save_form_data', 'nonce');
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();

        $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 10;
>>>>>>> Stashed changes
        $codes = array();
        for ( $i = 0; $i < $qty; $i++ ) {
            $codes[] = FFC_Utils::generate_random_code();
        }
        wp_send_json_success( array( 'codes' => implode( "\n", $codes ) ) );
    }

<<<<<<< Updated upstream
    public function ajax_load_template() {
        check_ajax_referer( 'ffc_admin_pdf_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();

        $filename = isset($_POST['filename']) ? sanitize_file_name($_POST['filename']) : '';
        if ( empty($filename) ) wp_send_json_error();

        $filepath = FFC_PLUGIN_DIR . 'html/' . $filename;
        if ( ! file_exists( $filepath ) ) wp_send_json_error();

        $content = file_get_contents( $filepath );
        wp_send_json_success( $content );
    }

    public function save_form_data( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( ! isset( $_POST['ffc_form_nonce'] ) || ! wp_verify_nonce( $_POST['ffc_form_nonce'], 'ffc_save_form_data' ) ) return;

        // 1. Salva os Campos do Formulário
        if ( isset( $_POST['ffc_fields'] ) && is_array( $_POST['ffc_fields'] ) ) {
=======
    /**
     * Handles the saving of form metadata.
     */
    public function save_form_data( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! isset( $_POST['ffc_form_nonce'] ) || ! wp_verify_nonce( $_POST['ffc_form_nonce'], 'ffc_save_form_data' ) ) return;

        // 1. Sanitize Fields
        if ( isset( $_POST['ffc_fields'] ) ) {
>>>>>>> Stashed changes
            $clean_fields = array();
            foreach ( $_POST['ffc_fields'] as $idx => $f ) {
                if ( $idx === 'TEMPLATE' ) continue;
                $clean_fields[] = array_map( 'sanitize_text_field', $f );
            }
            update_post_meta( $post_id, '_ffc_form_fields', $clean_fields );
        }

<<<<<<< Updated upstream
        // 2. Salva as Configurações
        if ( isset( $_POST['ffc_config'] ) ) {
            $config = $_POST['ffc_config'];
            $allowed_html = FFC_Utils::get_allowed_html_tags();

            $clean_config = array();
            $clean_config['pdf_layout'] = wp_kses( $config['pdf_layout'], $allowed_html );
            $clean_config['email_body'] = wp_kses( $config['email_body'], $allowed_html );
            $clean_config['bg_image']   = esc_url_raw( $config['bg_image'] );
            
            $clean_config['enable_restriction'] = sanitize_key( $config['enable_restriction'] );
            $clean_config['send_user_email']    = sanitize_key( $config['send_user_email'] );
            $clean_config['email_subject']      = sanitize_text_field( $config['email_subject'] );
            
            $clean_config['allowed_users_list']   = sanitize_textarea_field( $config['allowed_users_list'] );
            $clean_config['denied_users_list']    = sanitize_textarea_field( $config['denied_users_list'] );
            $clean_config['validation_code']      = sanitize_text_field( $config['validation_code'] );
            $clean_config['generated_codes_list'] = sanitize_textarea_field( $config['generated_codes_list'] );

            // Validação de Tags Obrigatórias
            $missing_tags = array();
            if ( strpos( $clean_config['pdf_layout'], '{{auth_code}}' ) === false ) $missing_tags[] = '{{auth_code}}';
            if ( strpos( $clean_config['pdf_layout'], '{{name}}' ) === false && strpos( $clean_config['pdf_layout'], '{{nome}}' ) === false ) $missing_tags[] = '{{name}}';
            if ( strpos( $clean_config['pdf_layout'], '{{cpf_rf}}' ) === false ) $missing_tags[] = '{{cpf_rf}}';

            if ( ! empty( $missing_tags ) ) {
                set_transient( 'ffc_save_error_' . get_current_user_id(), $missing_tags, 45 );
            }

            $current_config = get_post_meta( $post_id, '_ffc_form_config', true );
            if(!is_array($current_config)) $current_config = array();
            
            update_post_meta( $post_id, '_ffc_form_config', array_merge($current_config, $clean_config) );
=======
        // 2. Sanitize Configuration (Using FFC_Utils for recursive cleaning)
        if ( isset( $_POST['ffc_config'] ) ) {
            $clean_config = FFC_Utils::sanitize_recursive( $_POST['ffc_config'] );
            update_post_meta( $post_id, '_ffc_form_config', $clean_config );
        }

        // 3. Sanitize Extra Templates
        if ( isset( $_POST['ffc_extra_templates'] ) ) {
            $clean_extra = FFC_Utils::sanitize_recursive( $_POST['ffc_extra_templates'] );
            update_post_meta( $post_id, '_ffc_extra_templates', $clean_extra );
>>>>>>> Stashed changes
        }
    }

    public function display_save_errors() {
<<<<<<< Updated upstream
        $error_tags = get_transient( 'ffc_save_error_' . get_current_user_id() );
        if ( $error_tags ) {
            delete_transient( 'ffc_save_error_' . get_current_user_id() );
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong><?php esc_html_e( 'Warning! Missing required tags in PDF Layout:', 'ffc' ); ?></strong> <code><?php echo implode( ', ', $error_tags ); ?></code>.</p>
            </div>
            <?php
        }
=======
        // Logic to show warnings if tags are missing in the HTML layout
>>>>>>> Stashed changes
    }
}