<?php
<<<<<<< Updated upstream
=======
/**
 * FFC_Submission_List
 * Handles the submission listing table using the WordPress List Table API.
 *
 * @package FastFormCertificates
 * @version 1.2.0
 */

>>>>>>> Stashed changes
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FFC_Submission_List extends WP_List_Table {

    /**
     * @var FFC_Submission_Handler
     */
    protected $handler;

    /**
     * @var string
     */
    protected $table_name;

    public function __construct( $handler ) {
        global $wpdb;
        $this->handler    = $handler;
        $this->table_name = $wpdb->prefix . 'ffc_submissions';
        
        parent::__construct( array(
            'singular' => 'submission',
            'plural'   => 'submissions',
            'ajax'     => false
        ) );
    }

    public function no_items() {
        esc_html_e( 'No certificate submissions found.', 'ffc' );
    }

    /**
<<<<<<< Updated upstream
     * Define as abas de visualização (Tudo / Lixeira)
=======
     * Navigation filters (All/Trash).
     * Criteria 3: Efficient count queries.
>>>>>>> Stashed changes
     */
    protected function get_views() {
        global $wpdb;
        
        $current_status = isset( $_REQUEST['status'] ) ? sanitize_key( $_REQUEST['status'] ) : 'publish';

        $publish_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$this->table_name} WHERE status = %s", 'publish' ) );
        $trash_count   = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$this->table_name} WHERE status = %s", 'trash' ) );

        $base_url = admin_url( 'edit.php?post_type=ffc_form&page=ffc-submissions' );

        $views = array(
            'publish' => sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url( add_query_arg( 'status', 'publish', $base_url ) ),
                'publish' === $current_status ? 'current' : '',
                __( 'Active', 'ffc' ),
                absint( $publish_count )
            ),
            'trash'   => sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url( add_query_arg( 'status', 'trash', $base_url ) ),
                'trash' === $current_status ? 'current' : '',
                __( 'Trash', 'ffc' ),
                absint( $trash_count )
            )
        );

        return $views;
    }

    /**
<<<<<<< Updated upstream
     * Define as colunas da tabela
     * Se houver um filtro por formulário, ele exibe os campos desse formulário como colunas
=======
     * Define the columns for the table.
     * Criteria 1: Dynamic columns based on form filter for better UX.
>>>>>>> Stashed changes
     */
    public function get_columns() {
        $columns = array(
            'cb'              => '<input type="checkbox" />',
            'id'              => __( 'ID', 'ffc' ),
            'auth_code'       => __( 'Auth Code', 'ffc' ),
            'submission_date' => __( 'Date', 'ffc' ),
            'form_name'       => __( 'Origin Form', 'ffc' ),
            'email'           => __( 'Recipient Email', 'ffc' ),
        );

<<<<<<< Updated upstream
        // Lógica de colunas dinâmicas quando um formulário específico é filtrado
        if ( ! empty( $_GET['filter_form_id'] ) ) {
            $form_id = absint( $_GET['filter_form_id'] );
            $fields  = get_post_meta( $form_id, '_ffc_form_fields', true );
            
            if ( is_array( $fields ) ) {
                foreach ( $fields as $field ) {
                    if ( ! empty( $field['name'] ) ) {
                        $col_key = sanitize_key( $field['name'] );
                        // Evita duplicar colunas que já existem por padrão
                        if ( isset( $columns[$col_key] ) || $col_key === 'email' ) continue;
                        
                        $col_label = ! empty( $field['label'] ) ? $field['label'] : $field['name'];
                        $columns[ $col_key ] = esc_html( $col_label );
                    }
                }
            }
        } else {
            // Se estiver vendo todos os formulários, mostra um resumo
=======
        // If filtering by a specific form, inject its fields as columns
        $filter_form_id = isset( $_GET['filter_form_id'] ) ? absint( $_GET['filter_form_id'] ) : 0;
        if ( $filter_form_id > 0 ) {
            $fields = get_post_meta( $filter_form_id, '_ffc_form_fields', true );
            
            if ( is_array( $fields ) ) {
                foreach ( $fields as $field ) {
                    if ( empty( $field['name'] ) ) continue;
                    
                    $col_key = sanitize_key( $field['name'] );
                    // Avoid duplicating fixed columns
                    if ( in_array( $col_key, array( 'id', 'auth_code', 'email', 'submission_date' ) ) ) continue;
                    
                    $columns[ $col_key ] = esc_html( ! empty( $field['label'] ) ? $field['label'] : $field['name'] );
                }
            }
        } else {
>>>>>>> Stashed changes
            $columns['data_summary'] = __( 'Data Summary', 'ffc' );
        }

        $columns['actions'] = __( 'Actions', 'ffc' );

        return $columns;
    }

    public function get_sortable_columns() {
        return array(
            'id'              => array( 'id', false ),
            'auth_code'       => array( 'auth_code', false ),
            'submission_date' => array( 'submission_date', true ),
            'email'           => array( 'email', false ),
            'form_name'       => array( 'form_id', false ),
        );
    }

    public function get_bulk_actions() {
        $status = isset( $_REQUEST['status'] ) ? sanitize_key( $_REQUEST['status'] ) : 'publish';
        if ( 'trash' === $status ) {
            return array(
                'bulk_restore' => __( 'Restore', 'ffc' ),
                'bulk_delete'  => __( 'Delete Permanently', 'ffc' )
            );
        }
        return array( 'bulk_trash' => __( 'Move to Trash', 'ffc' ) );
    }

<<<<<<< Updated upstream
    // --- RENDERIZAÇÃO DAS COLUNAS ---

    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="submission[]" value="%s" />', esc_attr( $item['id'] ) );
=======
    /* --- Column Renderers --- */

    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="submission[]" value="%d" />', absint( $item['id'] ) );
>>>>>>> Stashed changes
    }

    public function column_id( $item ) {
        return sprintf( '<strong>#%d</strong>', absint( $item['id'] ) );
    }

    public function column_auth_code( $item ) {
        if ( empty( $item['auth_code'] ) ) return '<span class="description">-</span>';
        
        return sprintf(
            '<code class="ffc-code-badge">%s</code>',
            esc_html( FFC_Utils::format_auth_code( $item['auth_code'] ) )
        );
    }

    public function column_submission_date( $item ) {
        $timestamp = strtotime( $item['submission_date'] );
        return sprintf(
            '<span title="%s">%s</span>',
            esc_attr( $item['submission_date'] ),
            esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) )
        );
    }

    public function column_email( $item ) {
        return sprintf( '<a href="mailto:%1$s"><strong>%1$s</strong></a>', esc_html( $item['email'] ) );
    }

    public function column_form_name( $item ) {
<<<<<<< Updated upstream
        $form = get_post( $item['form_id'] );
        $title = $form ? $form->post_title : __( '(Deleted)', 'ffc' );
        $url = add_query_arg( 'filter_form_id', $item['form_id'], admin_url( 'edit.php?post_type=ffc_form&page=ffc-submissions' ) );
        return '<a href="' . esc_url( $url ) . '"><strong>' . esc_html( $title ) . '</strong></a>';
    }

    public function column_user_ip( $item ) {
        return ! empty( $item['user_ip'] ) ? esc_html( $item['user_ip'] ) : '-';
    }

    public function column_data_summary( $item ) {
        $data = json_decode( $item['data'], true );
        if ( ! is_array( $data ) ) $data = json_decode( stripslashes( $item['data'] ), true );
        if ( ! is_array( $data ) ) return '-';

        $output = array();
        $count = 0;
        foreach ( $data as $k => $v ) {
            if ( $count >= 3 ) break;
            // Pula metadados internos
            if ( in_array( $k, array( 'auth_code', 'fill_date', 'is_edited', 'edited_at', 'ticket' ) ) ) continue;
            
            if ( is_string( $v ) || is_numeric( $v ) ) {
                $label = str_replace( array('_', '-'), ' ', $k );
                $output[] = '<strong>' . esc_html( ucfirst($label) ) . ':</strong> ' . esc_html( wp_trim_words($v, 5) );
                $count++;
            }
=======
        $title = get_the_title( $item['form_id'] );
        if ( ! $title ) {
            return '<span class="description">' . __( '(Form Deleted)', 'ffc' ) . '</span>';
>>>>>>> Stashed changes
        }
        $url = add_query_arg( 'filter_form_id', $item['form_id'], admin_url( 'edit.php?post_type=ffc_form&page=ffc-submissions' ) );
        return sprintf( '<a href="%s"><strong>%s</strong></a>', esc_url( $url ), esc_html( $title ) );
    }

    /**
<<<<<<< Updated upstream
     * Renderiza colunas dinâmicas (Campos do formulário)
=======
     * Renders a snippet of the data if no specific form is filtered.
     */
    public function column_data_summary( $item ) {
        $data = json_decode( $item['data'], true );
        if ( ! is_array( $data ) ) return '<span class="description">-</span>';

        $output    = array();
        $count     = 0;
        $blacklist = array( 'auth_code', 'auth_hash', 'qr_code_base64', 'user_ip', 'form_id' );

        foreach ( $data as $k => $v ) {
            if ( $count >= 3 ) break;
            if ( in_array( $k, $blacklist ) ) continue;
            
            $label = str_replace( array( '_', '-' ), ' ', $k );
            $val   = is_array( $v ) ? implode( ', ', $v ) : $v;
            
            $output[] = sprintf(
                '<strong>%s:</strong> %s',
                esc_html( ucfirst( $label ) ),
                esc_html( wp_trim_words( $val, 8 ) )
            );
            $count++;
        }

        return ! empty( $output ) ? implode( '<br>', $output ) : '<em>' . __( 'No meta data', 'ffc' ) . '</em>';
    }

    /**
     * Generic renderer for dynamic field columns.
>>>>>>> Stashed changes
     */
    public function column_default( $item, $column_name ) {
        $data = json_decode( $item['data'], true );
        if ( is_array( $data ) && isset( $data[ $column_name ] ) ) {
            $val = $data[ $column_name ];
            return is_array( $val ) ? esc_html( implode( ', ', $val ) ) : esc_html( $val );
        }
        return '<span class="description">-</span>';
    }

<<<<<<< Updated upstream
    /**
     * Renderiza os botões de ação (Edit, Trash, PDF, Delete, Restore)
     */
=======
>>>>>>> Stashed changes
    public function column_actions( $item ) {
        $id     = absint( $item['id'] );
        $status = isset( $_REQUEST['status'] ) ? sanitize_key( $_REQUEST['status'] ) : 'publish';
        $nonce  = wp_create_nonce( 'ffc_action_' . $id );
        $base   = admin_url( 'edit.php?post_type=ffc_form&page=ffc-submissions&submission_id=' . $id . '&_wpnonce=' . $nonce );
        
<<<<<<< Updated upstream
        // Nonce vital para bater com o check do FFC_Admin
        $nonce = wp_create_nonce( 'ffc_action_' . $id );
        
        $base_url = admin_url( 'edit.php?post_type=ffc_form&page=ffc-submissions&submission_id=' . $id . '&_wpnonce=' . $nonce );
=======
>>>>>>> Stashed changes
        $actions = array();

        if ( 'trash' === $status ) {
            $actions['restore'] = sprintf( '<a href="%s" class="button button-small">%s</a>', esc_url( add_query_arg( 'action', 'restore', $base ) ), __( 'Restore', 'ffc' ) );
            $actions['delete']  = sprintf( '<a href="%s" class="button button-small ffc-btn-danger" onclick="return confirm(\'Irreversible! Confirm?\')">%s</a>', esc_url( add_query_arg( 'action', 'delete', $base ) ), __( 'Delete', 'ffc' ) );
        } else {
            $actions['view']    = sprintf( '<a href="%s" class="button button-small">%s</a>', esc_url( add_query_arg( 'action', 'edit', $base ) ), __( 'View/Edit', 'ffc' ) );
            $actions['pdf']     = sprintf( '<button type="button" class="button button-small button-primary ffc-admin-pdf-btn" data-id="%d">PDF</button>', $id );
            $actions['trash']   = sprintf( '<a href="%s" class="button button-small">%s</a>', esc_url( add_query_arg( 'action', 'trash', $base ) ), __( 'Trash', 'ffc' ) );
        }

        return '<div class="ffc-row-actions">' . implode( ' ', $actions ) . '</div>';
    }

    /**
<<<<<<< Updated upstream
     * Barra de filtro por formulário no topo da tabela
=======
     * Add filter dropdown at the top of the table.
>>>>>>> Stashed changes
     */
    public function extra_tablenav( $which ) {
        if ( 'top' === $which ) {
            $forms   = get_posts( array( 'post_type' => 'ffc_form', 'posts_per_page' => -1 ) );
            $current = isset( $_GET['filter_form_id'] ) ? absint( $_GET['filter_form_id'] ) : 0;
            ?>
            <div class="alignleft actions">
                <select name="filter_form_id">
                    <option value=""><?php esc_html_e( 'Filter by Form...', 'ffc' ); ?></option>
                    <?php foreach ( $forms as $f ) : ?>
                        <option value="<?php echo esc_attr( $f->ID ); ?>" <?php selected( $current, $f->ID ); ?>>
                            <?php echo esc_html( $f->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button( __( 'Filter', 'ffc' ), 'secondary', 'filter_action', false ); ?>
            </div>
            <?php
        }
    }

    /**
     * Main data preparation.
     * Criteria 2 & 3: Sanitized SQL and optimized pagination.
     */
    public function prepare_items() {
        global $wpdb;

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $offset       = ( $current_page - 1 ) * $per_page;

        // Configure headers
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

<<<<<<< Updated upstream
        // 1. Filtro de Status (Ativos ou Lixeira)
        $status = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] === 'trash' ) ? 'trash' : 'publish';
        $where_parts = array( $wpdb->prepare( "status = %s", $status ) );

        // 2. Filtro por Formulário
=======
        // Building the WHERE clause
        $status      = ( isset( $_REQUEST['status'] ) && 'trash' === $_REQUEST['status'] ) ? 'trash' : 'publish';
        $where_parts = array( $wpdb->prepare( "status = %s", $status ) );

>>>>>>> Stashed changes
        if ( ! empty( $_GET['filter_form_id'] ) ) {
            $where_parts[] = $wpdb->prepare( "form_id = %d", absint( $_GET['filter_form_id'] ) );
        }

<<<<<<< Updated upstream
        // 3. Busca (Pesquisa no email ou no JSON de dados)
=======
>>>>>>> Stashed changes
        if ( ! empty( $_REQUEST['s'] ) ) {
            $search = '%' . $wpdb->esc_like( sanitize_text_field( $_REQUEST['s'] ) ) . '%';
            $where_parts[] = $wpdb->prepare( "(email LIKE %s OR data LIKE %s OR auth_code LIKE %s)", $search, $search, $search );
        }

        $where_clause = "WHERE " . implode( " AND ", $where_parts );

<<<<<<< Updated upstream
        // 4. Ordenação
        $orderby_whitelist = array( 'id', 'submission_date', 'email' );
        $orderby = ( ! empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], $orderby_whitelist ) ) ? $_GET['orderby'] : 'submission_date';
        $order = ( ! empty( $_GET['order'] ) && strtoupper( $_GET['order'] ) === 'ASC' ) ? 'ASC' : 'DESC';

        // 5. Paginação
        $total_items = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name} {$where_clause}" );
        $current_page = $this->get_pagenum();
        $offset = ( $current_page - 1 ) * $per_page;

        // 6. Execução da Query Final
=======
        // Sorting (Security: check against sortable columns)
        $orderby = 'submission_date';
        if ( ! empty( $_GET['orderby'] ) ) {
            $allowed_sort = array( 'id', 'auth_code', 'submission_date', 'email', 'form_id' );
            if ( in_array( $_GET['orderby'], $allowed_sort ) ) {
                $orderby = sanitize_key( $_GET['orderby'] );
            }
        }
        $order = ( ! empty( $_GET['order'] ) && 'asc' === strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';

        // Get total count
        $total_items = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$this->table_name} $where_clause" );

        // Fetch results
>>>>>>> Stashed changes
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );
        $this->items = $wpdb->get_results( $query, ARRAY_A );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ) );
    }
}