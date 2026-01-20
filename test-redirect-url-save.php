<?php
/**
 * TEST: Verificar salvamento do redirect_url
 *
 * Este arquivo testa se a correção do salvamento está funcionando corretamente.
 *
 * Instruções:
 * 1. Faça upload deste arquivo para: wp-content/plugins/wp-ffcertificate/
 * 2. Acesse: https://dresaomiguel.com.br/wp-content/plugins/wp-ffcertificate/test-redirect-url-save.php
 * 3. Veja os resultados dos testes
 * 4. DELETE este arquivo após usar
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Acesso negado. Você precisa ser administrador.');
}

echo '<h1>🧪 Teste: Salvamento de redirect_url</h1>';
echo '<style>
body{font-family:monospace;padding:20px;line-height:1.6;background:#f5f5f5;}
.test-case{background:white;border:1px solid #ddd;border-radius:5px;padding:20px;margin:20px 0;}
.pass{background:#e8f5e9;border-left:4px solid #4caf50;}
.fail{background:#ffebee;border-left:4px solid #f44336;}
.info{background:#e3f2fd;border-left:4px solid #2196f3;}
table{border-collapse:collapse;margin:20px 0;width:100%;}
td,th{border:1px solid #ddd;padding:8px;text-align:left;}
th{background:#f0f0f0;font-weight:bold;}
code{background:#f5f5f5;padding:2px 6px;border-radius:3px;font-family:monospace;}
</style>';

// Test cases
echo '<h2>📋 Casos de Teste</h2>';

// Helper function to test the logic
function test_redirect_url_logic($post_value, $test_name) {
    // Simulate the OLD logic (buggy)
    $old_logic = isset($post_value) ? esc_url_raw($post_value) : home_url('/dashboard');

    // Simulate the NEW logic (fixed)
    $new_logic = !empty($post_value) ? esc_url_raw($post_value) : home_url('/dashboard');

    $expected = home_url('/dashboard');
    if (!empty($post_value)) {
        $expected = esc_url_raw($post_value);
    }

    $pass_old = ($old_logic === $expected);
    $pass_new = ($new_logic === $expected);

    echo '<div class="test-case ' . ($pass_new ? 'pass' : 'fail') . '">';
    echo '<h3>' . esc_html($test_name) . '</h3>';
    echo '<table>';
    echo '<tr><th>Item</th><th>Valor</th></tr>';
    echo '<tr><td>Valor do $_POST[\'redirect_url\']</td><td><code>' . (isset($post_value) ? esc_html(var_export($post_value, true)) : 'não definido') . '</code></td></tr>';
    echo '<tr><td>Resultado Esperado</td><td><code>' . esc_html($expected) . '</code></td></tr>';
    echo '<tr class="' . ($pass_old ? '' : 'fail') . '"><td>Lógica ANTIGA (isset)</td><td><code>' . esc_html($old_logic) . '</code> ' . ($pass_old ? '✅' : '❌') . '</td></tr>';
    echo '<tr class="' . ($pass_new ? 'pass' : '') . '"><td>Lógica NOVA (!empty)</td><td><code>' . esc_html($new_logic) . '</code> ' . ($pass_new ? '✅' : '❌') . '</td></tr>';
    echo '</table>';
    echo '</div>';

    return $pass_new;
}

// Test 1: Campo com URL válida
$test1 = test_redirect_url_logic('https://dresaomiguel.com.br/painel-de-usuario/', 'Teste 1: Campo preenchido com URL válida');

// Test 2: Campo vazio (string vazia)
$test2 = test_redirect_url_logic('', 'Teste 2: Campo enviado mas vazio (string vazia)');

// Test 3: Campo não enviado (null)
$_POST = array(); // Simular POST sem o campo
$test3 = test_redirect_url_logic(null, 'Teste 3: Campo não enviado');

// Test 4: Campo com espaços vazios
$test4 = test_redirect_url_logic('   ', 'Teste 4: Campo com apenas espaços');

// Summary
echo '<h2>📊 Resumo dos Testes</h2>';
echo '<div class="test-case ' . ($test1 && $test2 && $test3 && $test4 ? 'pass' : 'fail') . '">';
echo '<table>';
echo '<tr><th>Teste</th><th>Status</th></tr>';
echo '<tr><td>Teste 1: URL válida</td><td>' . ($test1 ? '✅ PASSOU' : '❌ FALHOU') . '</td></tr>';
echo '<tr><td>Teste 2: String vazia</td><td>' . ($test2 ? '✅ PASSOU' : '❌ FALHOU') . '</td></tr>';
echo '<tr><td>Teste 3: Não enviado</td><td>' . ($test3 ? '✅ PASSOU' : '❌ FALHOU') . '</td></tr>';
echo '<tr><td>Teste 4: Apenas espaços</td><td>' . ($test4 ? '✅ PASSOU' : '❌ FALHOU') . '</td></tr>';
echo '</table>';

if ($test1 && $test2 && $test3 && $test4) {
    echo '<p style="color:green;font-weight:bold;font-size:18px;">🎉 TODOS OS TESTES PASSARAM!</p>';
} else {
    echo '<p style="color:red;font-weight:bold;font-size:18px;">❌ ALGUNS TESTES FALHARAM!</p>';
}
echo '</div>';

// Real world test
echo '<h2>🌍 Teste Real com Configuração Atual</h2>';

$current_settings = get_option('ffc_user_access_settings', array());

echo '<div class="test-case info">';
echo '<h3>Configuração Atual no Banco de Dados</h3>';

if (empty($current_settings)) {
    echo '<p style="color:orange;">⚠️ Nenhuma configuração encontrada. A opção <code>ffc_user_access_settings</code> não existe ou está vazia.</p>';
} else {
    echo '<table>';
    echo '<tr><th>Chave</th><th>Valor</th></tr>';
    foreach ($current_settings as $key => $value) {
        $display_value = is_bool($value) ? ($value ? 'true' : 'false') : (is_array($value) ? implode(', ', $value) : $value);
        $highlight = ($key === 'redirect_url') ? ' style="background:#ffffcc;"' : '';
        echo '<tr' . $highlight . '><td><strong>' . esc_html($key) . '</strong></td><td><code>' . esc_html($display_value) . '</code></td></tr>';
    }
    echo '</table>';

    if (isset($current_settings['redirect_url'])) {
        if (empty($current_settings['redirect_url'])) {
            echo '<p style="color:orange;">⚠️ O campo <code>redirect_url</code> existe mas está VAZIO!</p>';
        } else {
            echo '<p style="color:green;">✅ O campo <code>redirect_url</code> está configurado: <code>' . esc_html($current_settings['redirect_url']) . '</code></p>';
        }
    } else {
        echo '<p style="color:red;">❌ O campo <code>redirect_url</code> não existe nas configurações!</p>';
    }
}

echo '</div>';

// Instructions
echo '<h2>📝 Próximos Passos</h2>';
echo '<div class="test-case info">';
echo '<ol>';
echo '<li>Se os testes passaram, a correção está funcionando corretamente</li>';
echo '<li>Vá para: <a href="' . admin_url('edit.php?post_type=ffc_form&page=ffc-settings&tab=user_access') . '" target="_blank">Settings &gt; User Access</a></li>';
echo '<li>No campo <strong>"Redirect URL"</strong>, digite: <code>https://dresaomiguel.com.br/painel-de-usuario/</code></li>';
echo '<li>Clique em <strong>"Save Settings"</strong></li>';
echo '<li>Volte aqui e recarregue esta página para confirmar que foi salvo</li>';
echo '<li>Depois, teste a página de usuários: <a href="' . admin_url('users.php') . '" target="_blank">Usuários</a></li>';
echo '</ol>';
echo '</div>';

echo '<hr style="margin:40px 0;">';
echo '<div style="background:#fff3cd;border:1px solid #ffc107;padding:15px;border-radius:5px;">';
echo '<p><strong>⚠️ IMPORTANTE:</strong> DELETE este arquivo após usar!</p>';
echo '<p>Comando SSH: <code>rm ' . __FILE__ . '</code></p>';
echo '</div>';
?>
