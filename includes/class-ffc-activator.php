<?php
/**
 * FFC_Activator
<<<<<<< Updated upstream
<<<<<<< Updated upstream
 * Gerencia a instalação do plugin: Tabelas, Páginas, Formulários Iniciais e Configurações.
=======
 * Handles plugin installation: Tables, Pages, Initial Forms, and Settings.
 *
 * @package FastFormCertificates
>>>>>>> Stashed changes
=======
 * Handles plugin installation: Tables, Pages, Initial Forms, and Settings.
 *
 * @package FastFormCertificates
>>>>>>> Stashed changes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Activator {

<<<<<<< Updated upstream
<<<<<<< Updated upstream
    public static function activate() {
        // 1. Criação das Tabelas
        self::create_tables();

        // 2. Criação da Página de Validação
        self::create_validation_page();

        // 3. Criação do Formulário Padrão
=======
    /**
     * POINT 1 & 2: Run activation logic.
     * Orchestrates the setup of the environment.
     */
    public static function activate() {
        // 1. Create/Update Tables
        self::create_tables();

        // 2. Create Validation Page (Shortcode: [ffc_verification])
        self::create_validation_page();

        // 3. Create Default Form Example
>>>>>>> Stashed changes
=======
    /**
     * POINT 1 & 2: Run activation logic.
     * Orchestrates the setup of the environment.
     */
    public static function activate() {
        // 1. Create/Update Tables
        self::create_tables();

        // 2. Create Validation Page (Shortcode: [ffc_verification])
        self::create_validation_page();

        // 3. Create Default Form Example
>>>>>>> Stashed changes
        self::create_default_form();

        // 4. Configurações Iniciais
        self::set_default_options();

<<<<<<< Updated upstream
<<<<<<< Updated upstream
        // 5. Atualização de Permalinks (Garante que o /valid funcione na hora)
=======
=======
>>>>>>> Stashed changes
        // 5. Run Retroactive Migration for legacy data
        self::run_retroactive_migration();

        // 6. Refresh Permalinks
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        flush_rewrite_rules();
    }

    /**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * Gerencia a criação da tabela de submissões
=======
     * POINT 3: Manages the submissions table.
     * Uses dbDelta for safe schema updates.
>>>>>>> Stashed changes
=======
     * POINT 3: Manages the submissions table.
     * Uses dbDelta for safe schema updates.
>>>>>>> Stashed changes
     */
    private static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $table_name = $wpdb->prefix . 'ffc_submissions';

        /**
         * SQL for dbDelta. 
         * Note: Primary Key must be followed by two spaces for WP compatibility.
         */
        $sql_submissions = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            auth_code varchar(20) NOT NULL,
            auth_hash varchar(64) DEFAULT '' NOT NULL,
            submission_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            data longtext NOT NULL,
            user_ip varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            status varchar(20) DEFAULT 'publish' NOT NULL,
            PRIMARY KEY  (id),
            KEY form_id (form_id),
            KEY auth_code (auth_code),
            KEY auth_hash (auth_hash),
            KEY email (email),
            KEY status (status)
        ) {$wpdb->get_charset_collate()};";

        dbDelta( $sql_submissions );
        
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        // Verificação manual da coluna status (segurança extra)
        $column = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'status'", 
            DB_NAME, $table_name
        ));
=======
        // POINT 3: Fallback for column updates that dbDelta might miss
        self::ensure_columns_exist( $table_name );
>>>>>>> Stashed changes
=======
        // POINT 3: Fallback for column updates that dbDelta might miss
        self::ensure_columns_exist( $table_name );
>>>>>>> Stashed changes

        update_option( 'ffc_db_version', FFC_VERSION );
    }

    /**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * Cria a página /valid com o shortcode necessário
=======
     * Secondary check to ensure specific columns exist in older installations.
>>>>>>> Stashed changes
=======
     * Secondary check to ensure specific columns exist in older installations.
>>>>>>> Stashed changes
     */
    private static function ensure_columns_exist( $table_name ) {
        global $wpdb;
        $columns_to_check = array(
            'auth_code' => "varchar(20) NOT NULL AFTER form_id",
            'auth_hash' => "varchar(64) DEFAULT '' NOT NULL AFTER auth_code",
            'status'    => "varchar(20) DEFAULT 'publish' NOT NULL"
        );

<<<<<<< Updated upstream
<<<<<<< Updated upstream
        if ( ! isset( $page_check->ID ) ) {
            // Se a página não existe, cria do zero
            wp_insert_post( array(
                'post_title'    => 'Validação de Certificado',
                'post_content'  => $shortcode,
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => $slug
            ) );
        } else {
            // Se a página existe, garante que o shortcode esteja lá
            if ( strpos( $page_check->post_content, $shortcode ) === false ) {
                $page_check->post_content .= "\n" . $shortcode;
                wp_update_post( $page_check );
=======
=======
>>>>>>> Stashed changes
        foreach ( $columns_to_check as $column => $definition ) {
            $check = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `$table_name` LIKE %s", $column ) );
            if ( empty( $check ) ) {
                $wpdb->query( "ALTER TABLE `$table_name` ADD `$column` $definition" );
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
            }
        }
    }

    /**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * Cria um formulário inicial para o usuário não começar do zero
=======
=======
>>>>>>> Stashed changes
     * POINT 3: Retroactive Migration.
     * Recovers auth codes from JSON 'data' if the physical column is empty.
     */
    private static function run_retroactive_migration() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ffc_submissions';

        $results = $wpdb->get_results( "SELECT id, data, auth_code FROM $table_name WHERE auth_code = '' LIMIT 500" );

        if ( empty( $results ) ) {
            return;
        }

        foreach ( $results as $row ) {
            $decoded = json_decode( $row->data, true );
            if ( is_null( $decoded ) ) {
                $decoded = maybe_unserialize( $row->data );
            }

            if ( is_array( $decoded ) && ! empty( $decoded['auth_code'] ) ) {
                $code = sanitize_text_field( $decoded['auth_code'] );
                $hash = class_exists( 'FFC_Utils' ) ? FFC_Utils::generate_auth_hash( $code ) : wp_hash( $code );
                
                $wpdb->update( 
                    $table_name, 
                    array( 'auth_code' => $code, 'auth_hash' => $hash ), 
                    array( 'id' => $row->id ) 
                );
            }
        }
    }

    /**
     * POINT 5: Set initial global options.
     */
    private static function set_default_options() {
        $defaults = array( 
            'smtp_mode'    => 'wp',
            'cleanup_days' => 30,
            'blocked_cpfs' => '' 
        );

        $current_settings = get_option( 'ffc_settings', array() );
        $new_settings     = wp_parse_args( $current_settings, $defaults );
        update_option( 'ffc_settings', $new_settings );
    }

    /**
     * POINT 5: Creates the validation page with the correct shortcode.
     */
    private static function create_validation_page() {
        $slug = 'verify-certificate';
        $page_check = get_page_by_path( $slug );

        if ( ! isset( $page_check->ID ) ) {
            wp_insert_post( array(
                'post_title'   => __( 'Verify Certificate', 'ffc' ),
                'post_content' => '[ffc_verification]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_name'    => $slug
            ) );
        }
    }

    /**
     * POINT 4 & 5: Creates an initial form example.
     * Uses minimal inline styles as this is a user-editable template.
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
     */
    private static function create_default_form() {
        $forms_query = new WP_Query( array( 
            'post_type'      => 'ffc_form', 
            'posts_per_page' => 1,
            'post_status'    => 'any' 
        ) );

        if ( ! $forms_query->have_posts() ) {
            $form_id = wp_insert_post( array(
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                'post_title'   => 'Certificado de Exemplo',
                'post_status'  => 'publish',
                'post_type'    => 'ffc_form',
                'post_content' => 'Este é um formulário criado automaticamente pelo plugin.'
            ) );

            if ( $form_id ) {
                // Layout padrão com as variáveis dinâmicas
                $layout = '
                <div style="border:10px solid #2c3e50; padding:40px; text-align:center; font-family: Arial, sans-serif;">
                    <h1 style="color:#2c3e50; font-size:42px;">CERTIFICADO</h1>
                    <p style="font-size:20px;">Este certificado confirma que</p>
                    <h2 style="font-size:32px; color:#e67e22;">{{nome}}</h2>
                    <p style="font-size:18px;">concluiu o processo com sucesso em {{submission_date}}.</p>
                    <div style="margin-top:60px; padding-top:20px; border-top:1px solid #eee;">
                        <p style="margin:0;">Código de Autenticidade: <strong>{{auth_code}}</strong></p>
                        <p style="font-size:12px; color:#7f8c8d;">Valide este documento em: {{validation_url}}</p>
=======
                'post_title'   => __( 'Default Certificate Form', 'ffc' ),
                'post_status'  => 'publish',
                'post_type'    => 'ffc_form',
                'post_content' => __( 'This is a sample form created automatically upon activation.', 'ffc' )
            ) );

            if ( $form_id ) {
=======
                'post_title'   => __( 'Default Certificate Form', 'ffc' ),
                'post_status'  => 'publish',
                'post_type'    => 'ffc_form',
                'post_content' => __( 'This is a sample form created automatically upon activation.', 'ffc' )
            ) );

            if ( $form_id ) {
>>>>>>> Stashed changes
                $layout = '<div style="border: 10px solid #333; padding: 50px; text-align: center; font-family: Arial, sans-serif;">
                    <h1>' . __( 'CERTIFICATE OF ACHIEVEMENT', 'ffc' ) . '</h1>
                    <p style="font-size: 20px;">' . __( 'This certifies that', 'ffc' ) . '</p>
                    <h2 style="font-size: 30px; color: #0073aa;">{{name}}</h2>
                    <p>' . __( 'has successfully completed the requirements for this program.', 'ffc' ) . '</p>
                    <div style="margin-top: 50px; font-size: 12px; color: #666;">
                        ' . __( 'Verification Code', 'ffc' ) . ': {{auth_code}}<br>
                        ' . __( 'Date', 'ffc' ) . ': {{submission_date}}
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                    </div>
                </div>';

                update_post_meta( $form_id, 'ffc_form_config', array(
                    'pdf_layout'      => $layout,
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                    'email_subject'   => 'Seu Certificado de Conclusão',
                    'send_user_email' => 1
=======
=======
>>>>>>> Stashed changes
                    'email_subject'   => __( 'Your Certificate is Ready', 'ffc' ),
                    'send_user_email' => 1,
                    'success_message' => __( 'Congratulations! Your certificate has been generated.', 'ffc' )
                ) );

                // Add sample field
                update_post_meta( $form_id, '_ffc_form_fields', array(
                    array( 'label' => 'Full Name', 'name' => 'name', 'type' => 'text', 'required' => 1 )
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                ) );
            }
        }
    }
<<<<<<< Updated upstream
<<<<<<< Updated upstream

    /**
     * Define as configurações globais iniciais se não existirem
     */
    private static function set_default_options() {
        $settings = get_option( 'ffc_settings' );
        
        if ( ! $settings ) {
            update_option( 'ffc_settings', array( 
                'smtp_mode'    => 'wp', // WP Default por padrão
                'cleanup_days' => 30 
            ) );
        }
    }
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
}