<?php
<<<<<<< Updated upstream
/*
Plugin Name: Free Form Certificate
Description: Allows creation of dynamic forms, saves submissions, generates a PDF certificate, and enables CSV export.
Version: 2.5.0
Author: Alex Meusburger
Text Domain: ffc
Domain Path: /languages
*/
=======
/**
 * Plugin Name:       Free Form Certificate
 * Description:       Allows creation of dynamic forms, saves submissions, generates PDF certificates, and enables verification and CSV export.
 * Version:           2.7.0
 * Author:            Alex Meusburger
 * Text Domain:       ffc
 * Domain Path:       /languages
 *
 * @package           FastFormCertificates
 */
>>>>>>> Stashed changes

// Segurança: Impede o acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

<<<<<<< Updated upstream
=======
/**
 * Definição de Constantes do Plugin
 */
define( 'FFC_VERSION', '2.7.0' );
>>>>>>> Stashed changes
define( 'FFC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FFC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FFC_BASENAME', plugin_basename( __FILE__ ) );

<<<<<<< Updated upstream
// 1. Inclui a classe de ativação
require_once FFC_PLUGIN_DIR . 'includes/class-ffc-activator.php';

// 2. Carrega a classe de utilitários (Essencial para as tags HTML permitidas)
// Importante: Carregar ANTES do loader para que as outras classes já a enxerguem
require_once FFC_PLUGIN_DIR . 'includes/class-ffc-utils.php';

// 3. Registra o Hook de Ativação
=======
/**
 * Carregamento de Internacionalização (i18n)
 */
function ffc_load_textdomain() {
    load_plugin_textdomain( 'ffc', false, dirname( FFC_BASENAME ) . '/languages' );
}
add_action( 'plugins_loaded', 'ffc_load_textdomain' );

/**
 * Inclusão dos Arquivos de Ciclo de Vida e Utilitários
 * Carregamos estes primeiro para que a ativação tenha acesso às funções de banco e segurança.
 */
require_once FFC_PLUGIN_DIR . 'includes/class-ffc-activator.php';
require_once FFC_PLUGIN_DIR . 'includes/class-ffc-deactivator.php';
require_once FFC_PLUGIN_DIR . 'includes/class-ffc-utils.php';

/**
 * Registro de Hooks de Ativação e Desativação
 */
>>>>>>> Stashed changes
register_activation_hook( __FILE__, array( 'FFC_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'FFC_Deactivator', 'deactivate' ) );

<<<<<<< Updated upstream
// 4. Carrega o núcleo do plugin
require_once FFC_PLUGIN_DIR . 'includes/class-ffc-loader.php';

function run_free_form_certificate() {
    $plugin = new Free_Form_Certificate_Loader();
    $plugin->run();
=======
/**
 * Inicialização do Core do Plugin
 * O Loader é o "maestro" que incluirá o Submission Handler, Admin, Front-end, etc.
 */
require_once FFC_PLUGIN_DIR . 'includes/class-ffc-loader.php';

/**
 * Função de inicialização segura
 */
function run_ffc_plugin() {
    // Verifica requisitos mínimos antes de rodar (Ex: Versão do PHP)
    if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>' . esc_html__( 'Free Form Certificate requires PHP 7.4 or higher.', 'ffc' ) . '</p></div>';
        });
        return;
    }

    if ( class_exists( 'FFC_Loader' ) ) {
        $plugin = new FFC_Loader();
        $plugin->run();
    }
>>>>>>> Stashed changes
}

run_ffc_plugin();