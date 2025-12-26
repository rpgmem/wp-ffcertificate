/**
 * Fast Form Certificates - Admin Script
 * Refactored for modularity, translation, and style centralization.
 * * Criteria 4: Removed inline styles, moved to classes.
 * Criteria 5: All strings localized via ffc_admin_ajax.strings.
 */
(function($) {
    'use strict';

    const FFC_Admin = {
        init: function() {
            this.cacheDOM();
            this.bindEvents();
            this.initSortable();
            this.initialSetup();
        },

        cacheDOM: function() {
            this.$body = $('body');
            this.$fieldsContainer = $('#ffc-fields-container');
            this.$rulesContainer = $('#ffc-rules-container');
            this.$layoutEditor = $('#ffc_pdf_layout');
            this.$previewModal = $('#ffc-preview-modal');
        },

        bindEvents: function() {
            const self = this;

            // 1. Template & File Import
            this.$body.on('click', '#ffc_btn_import_html', (e) => { e.preventDefault(); $('#ffc_import_html_file').trigger('click'); });
            this.$body.on('change', '#ffc_import_html_file', (e) => this.handleFileImport(e));
            this.$body.on('click', '#ffc_load_template_btn', (e) => this.loadServerTemplate(e));

            // 2. Form Builder Actions
            this.$body.on('click', '.ffc-add-field', (e) => this.addField(e));
            this.$body.on('click', '.ffc-remove-field', (e) => this.removeElement(e, '.ffc-field-row', ffc_admin_ajax.strings.confirmDeleteField));
            this.$body.on('change', '.ffc-field-type-selector', function() { self.toggleFieldOptions($(this)); });
            this.$body.on('change', '.ffc-logic-toggle', function() { self.toggleLogicConfig($(this)); });

            // 3. Conditional Layout Rules
            this.$body.on('click', '.ffc-add-rule', (e) => this.addLayoutRule(e));
            this.$body.on('click', '.ffc-remove-rule', (e) => this.removeElement(e, '.ffc-rule-row', ffc_admin_ajax.strings.confirmDeleteRule));

            // 4. Media & Code Generation
            this.$body.on('click', '#ffc_btn_media_lib', (e) => this.openMediaLibrary(e));
            this.$body.on('click', '#ffc_btn_generate_codes', (e) => this.generateCodes(e));

            // 5. PDF & Preview
            this.$body.on('click', '.ffc-admin-pdf-btn', (e) => this.generateAdminPDF(e));
            this.$body.on('click', '#ffc_btn_live_preview', (e) => this.showLivePreview(e));
            this.$body.on('click', '.ffc-modal-close, .ffc-modal-overlay', () => this.$previewModal.fadeOut(200));

            // 6. Tags & UI Updates
            this.$body.on('click', '.ffc-insert-tag', (e) => this.insertTag(e));
            this.$body.on('input', 'input[name*="[label]"], input[name*="[name]"]', () => this.refreshLogicDropdowns());
        },

        // --- Core Functions ---

        handleFileImport: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.$layoutEditor.val(event.target.result);
                $('#ffc_import_html_file').val('');
                alert(ffc_admin_ajax.strings.fileImported);
<<<<<<< Updated upstream
            }
        };
        reader.onerror = function() { 
            alert(ffc_admin_ajax.strings.errorReadingFile || 'Erro ao ler arquivo'); 
        };
        reader.readAsText(file);
    });
=======
            };
            reader.readAsText(file);
        },
>>>>>>> Stashed changes

        loadServerTemplate: function(e) {
            e.preventDefault();
            const filename = $('#ffc_template_select').val();
            const $btn = $(e.currentTarget);
            if (!filename) return alert(ffc_admin_ajax.strings.selectTemplate);
            if (!confirm(ffc_admin_ajax.strings.confirmReplaceContent)) return;

<<<<<<< Updated upstream
        if (!filename) {
            alert(ffc_admin_ajax.strings.selectTemplate || 'Selecione um template');
            return;
        }

        if (!confirm(ffc_admin_ajax.strings.confirmReplaceContent || 'Isso substituirá o conteúdo atual. Continuar?')) {
            return;
        }

        $btn.prop('disabled', true).text(ffc_admin_ajax.strings.loading || 'Carregando...');

        $.ajax({
            url: ffc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
=======
            $btn.prop('disabled', true).text(ffc_admin_ajax.strings.loading);
            $.post(ffc_admin_ajax.ajax_url, {
>>>>>>> Stashed changes
                action: 'ffc_load_template',
                filename: filename,
                nonce: ffc_admin_ajax.nonce
            }, (response) => {
                if (response.success) {
<<<<<<< Updated upstream
                    $('#ffc_pdf_layout').val(response.data);
                    alert(ffc_admin_ajax.strings.templateLoaded || 'Template carregado!');
                } else {
                    alert((ffc_admin_ajax.strings.error || 'Erro: ') + response.data);
                }
            },
            error: function() {
                alert(ffc_admin_ajax.strings.connectionError || 'Erro de conexão');
            },
            complete: function() {
                $btn.prop('disabled', false).text(ffc_admin_ajax.strings.loadTemplate || 'Carregar Template');
=======
                    this.$layoutEditor.val(response.data);
                    alert(ffc_admin_ajax.strings.templateLoaded);
                }
            }).always(() => $btn.prop('disabled', false).text(ffc_admin_ajax.strings.loadTemplate));
        },

        addField: function(e) {
            e.preventDefault();
            const $template = $('.ffc-field-template');
            const $newRow = $($template.html());
            $newRow.removeClass('ffc-field-template ffc-hidden').hide();
            this.$fieldsContainer.append($newRow);
            $newRow.slideDown(200);
            this.reindexFields();
        },

        addLayoutRule: function(e) {
            e.preventDefault();
            const index = Date.now();
            // Criteria 4: All styles moved to classes in admin.css (ffc-rule-row)
            const ruleHtml = `
                <div class="ffc-rule-row">
                    <div class="ffc-rule-header">
                        <strong>${ffc_admin_ajax.strings.if_field}</strong> 
                        <select name="ffc_extra_templates[${index}][target]" class="ffc-rule-target-select">
                            <option value="">${ffc_admin_ajax.strings.select_field}</option>
                        </select>
                        <strong>${ffc_admin_ajax.strings.equals_to}</strong>
                        <input type="text" name="ffc_extra_templates[${index}][value]" placeholder="${ffc_admin_ajax.strings.value}">
                        <button type="button" class="button-link-delete ffc-remove-rule">${ffc_admin_ajax.strings.remove_rule}</button>
                    </div>
                    <textarea name="ffc_extra_templates[${index}][layout]" rows="5" placeholder="${ffc_admin_ajax.strings.html_placeholder}"></textarea>
                    <input type="text" name="ffc_extra_templates[${index}][bg]" placeholder="${ffc_admin_ajax.strings.bg_url_placeholder}">
                </div>`;
            this.$rulesContainer.append(ruleHtml);
            this.reindexRules();
        },

        removeElement: function(e, selector, confirmMsg) {
            e.preventDefault();
            if (confirm(confirmMsg)) {
                $(e.currentTarget).closest(selector).remove();
                this.reindexFields();
                this.reindexRules();
>>>>>>> Stashed changes
            }
        },

<<<<<<< Updated upstream
    // =========================================================================
    // 3. MEDIA LIBRARY (BACKGROUND IMAGE)
    // =========================================================================
    var mediaUploader;
    $('#ffc_btn_media_lib').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) { mediaUploader.open(); return; }
        
        mediaUploader = wp.media({
            title: ffc_admin_ajax.strings.selectBackgroundImage || 'Selecionar Imagem de Fundo',
            button: { text: ffc_admin_ajax.strings.useImage || 'Usar esta imagem' },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#ffc_bg_image_input').val(attachment.url);
        });
        
        mediaUploader.open();
    });

    // =========================================================================
    // 4. GENERATE RANDOM CODES (TICKETS)
    // =========================================================================
    $('#ffc_btn_generate_codes').on('click', function(e) {
        e.preventDefault();
        var qty = $('#ffc_qty_codes').val();
        var $btn = $(this);
        var $textarea = $('#ffc_generated_list');
        var $status = $('#ffc_gen_status');
        
        if(qty < 1) return;

        $btn.prop('disabled', true);
        $status.text(ffc_admin_ajax.strings.generating || 'Gerando...');
        
        $.ajax({
            url: ffc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ffc_generate_codes',
                qty: qty,
                nonce: ffc_admin_ajax.nonce
            },
            success: function(response) {
                if(response.success) {
                    var currentVal = $textarea.val();
                    var sep = (currentVal.length > 0 && !currentVal.endsWith('\n')) ? "\n" : "";
                    $textarea.val(currentVal + sep + response.data.codes);
                    $status.text(qty + ' ' + (ffc_admin_ajax.strings.codesGenerated || 'códigos gerados'));
                } else {
                    $status.text(ffc_admin_ajax.strings.errorGeneratingCodes || 'Erro ao gerar códigos');
                }
            },
            complete: function() {
                $btn.prop('disabled', false);
            },
            error: function() {
                $status.text(ffc_admin_ajax.strings.connectionError || 'Erro de conexão');
                $btn.prop('disabled', false);
            }
        });
    });

    // =========================================================================
    // 5. FORM BUILDER (O CORAÇÃO DO PROBLEMA)
    // =========================================================================
    
    // Função para reindexar nomes dos campos
    function ffc_reindex_fields() {
        $('#ffc-fields-container').children('.ffc-field-row').each(function(index) {
            $(this).find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    // Regex robusto para trocar o índice: ffc_fields[X][label] -> ffc_fields[index][label]
                    const newName = name.replace(/ffc_fields\[[^\]]*\]/, 'ffc_fields[' + index + ']');
                    $(this).attr('name', newName);
                }
=======
        // --- Helpers & Utilities ---

        reindexFields: function() {
            this.$fieldsContainer.children('.ffc-field-row').each(function(index) {
                $(this).find('input, select, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (name) $(this).attr('name', name.replace(/ffc_fields\[[^\]]*\]/, `ffc_fields[${index}]`));
                });
                $(this).attr('data-index', index);
>>>>>>> Stashed changes
            });
            this.refreshLogicDropdowns();
        },

<<<<<<< Updated upstream
    // Inicializa Sortable (Arrastar e Soltar)
    if ($.fn.sortable) {
        $('#ffc-fields-container').sortable({
            handle: '.ffc-sort-handle',
            placeholder: 'ui-state-highlight',
            update: function() { ffc_reindex_fields(); }
        });
    }

    // ADICIONAR NOVO CAMPO (Corrigido para usar o conteúdo do Template)
    $('.ffc-add-field').on('click', function(e) {
        e.preventDefault();
        
        // Pega o HTML de dentro da div de template
        var templateHtml = $('.ffc-field-template').html();
        var $container = $('#ffc-fields-container');
        
        // Cria o elemento jQuery
        var $newRow = $(templateHtml);
        
        // Remove classes de controle e garante que apareça
        $newRow.removeClass('ffc-field-template ffc-hidden').show();
        
        // Reseta campos internos
        $newRow.find('input, select, textarea').val('');
        $newRow.find('.ffc-field-type-selector').val('text');
        
        // Adiciona ao container
        $container.append($newRow);
        
        // Reindexa para o PHP salvar certo
        ffc_reindex_fields();
    });

    // REMOVER CAMPO (Usando delegação para funcionar em novos campos)
    $(document).on('click', '.ffc-remove-field', function(e) { 
        e.preventDefault();
        if (confirm(ffc_admin_ajax.strings.confirmDeleteField || 'Remover este campo?')) {
            $(this).closest('.ffc-field-row').remove();
            ffc_reindex_fields(); 
        }
    });
    
    // LÓGICA MOSTRAR/ESCONDER OPÇÕES (Delegação + Seletor Correto)
    $(document).on('change', '.ffc-field-type-selector', function() {
        const selectedType = $(this).val();
        const $row = $(this).closest('.ffc-field-row');
        const $optionsContainer = $row.find('.ffc-options-field'); 
        
        if (selectedType === 'select' || selectedType === 'radio') {
            $optionsContainer.stop(true, true).fadeIn(200).removeClass('ffc-hidden');
        } else {
            $optionsContainer.hide().addClass('ffc-hidden');
        }
    });

    // Inicialização: Aplica a visibilidade nos campos já carregados do banco
    $('.ffc-field-type-selector').each(function() {
        $(this).trigger('change');
    });
=======
        reindexRules: function() {
            this.$rulesContainer.children('.ffc-rule-row').each(function(index) {
                $(this).find('input, select, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (name) $(this).attr('name', name.replace(/ffc_extra_templates\[[^\]]*\]/, `ffc_extra_templates[${index}]`));
                });
            });
            this.refreshLogicDropdowns();
        },

        refreshLogicDropdowns: function() {
            const fields = [];
            this.$fieldsContainer.children('.ffc-field-row:not(.ffc-field-template)').each(function() {
                const name = $(this).find('input[name*="[name]"]').val();
                const label = $(this).find('input[name*="[label]"]').val() || name;
                if (name) fields.push({ name: name, label: label });
            });

            $('.ffc-logic-target-select, .ffc-rule-target-select').each(function() {
                const $select = $(this);
                const current = $select.val();
                $select.find('option:not([value=""])').remove();
                fields.forEach(f => $select.append(new Option(f.label, f.name, false, f.name === current)));
            });
        },

        toggleFieldOptions: function($el) {
            const $options = $el.closest('.ffc-field-row').find('.ffc-options-field');
            (['select', 'radio'].includes($el.val())) ? $options.fadeIn(200) : $options.hide();
        },

        toggleLogicConfig: function($el) {
            const $config = $el.closest('.ffc-logic-settings').find('.ffc-logic-config-container');
            $el.is(':checked') ? ($config.slideDown(200), this.refreshLogicDropdowns()) : $config.slideUp(200);
        },

        insertTag: function(e) {
            e.preventDefault();
            const tag = $(e.currentTarget).data('tag');
            const editor = this.$layoutEditor[0];
            const start = editor.selectionStart;
            const text = this.$layoutEditor.val();
            this.$layoutEditor.val(text.substring(0, start) + tag + text.substring(editor.selectionEnd));
            editor.focus();
            editor.setSelectionRange(start + tag.length, start + tag.length);
        },

        showLivePreview: function(e) {
            e.preventDefault();
            if (!window.FFCPDFEngine) return alert(ffc_admin_ajax.strings.engine_not_loaded);
            
            const dummyData = {
                template: this.$layoutEditor.val()
                    .replace(/{{name}}|{{nome}}/g, 'John Doe')
                    .replace(/{{auth_code}}/g, 'ABCD-1234-EFGH-5678'),
                bg_image: $('#ffc_bg_image_input').val(),
                form_title: ($('#title').val() || 'Preview') + ' Preview'
            };

            this.$previewModal.fadeIn(200);
            $('#ffc-preview-render-container').html(`<div class="ffc-preview-loader"><span class="spinner is-active"></span> ${ffc_admin_ajax.strings.rendering}</div>`);
            window.FFCPDFEngine.generate(dummyData, true);
        },

        initSortable: function() {
            if ($.fn.sortable) {
                this.$fieldsContainer.sortable({
                    handle: '.ffc-sort-handle',
                    placeholder: 'ffc-sortable-placeholder',
                    update: () => this.reindexFields()
                });
            }
        },

        initialSetup: function() {
            $('.ffc-field-type-selector').trigger('change');
            this.reindexFields();
            this.reindexRules();
        }
    };

    $(document).ready(() => FFC_Admin.init());
>>>>>>> Stashed changes

})(jQuery);