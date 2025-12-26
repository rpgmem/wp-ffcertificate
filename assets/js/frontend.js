<<<<<<< Updated upstream
// =========================================================================
// 1. FUNÇÃO GLOBAL DE GERAÇÃO DE PDF
// =========================================================================
window.generateCertificate = function(data) {
    const { template, bg_image, form_title } = data;
=======
/**
 * Free Form Certificates - Public Frontend Script
 * Handles conditional logic, input masking, and AJAX submissions.
 * * Criteria 4: Removed inline styles, using CSS classes instead.
 * Criteria 5: All strings and comments in English (localized via ffc_ajax).
 */
(function($) {
    'use strict';
>>>>>>> Stashed changes

    const FFC_Public = {
        lastVerifiedPdfData: null,

<<<<<<< Updated upstream
    // 1. Criar Overlay (Adicionamos pointer-events para bloquear cliques acidentais)
    const overlay = document.createElement('div');
    overlay.className = 'ffc-pdf-progress-overlay';
    overlay.style.pointerEvents = 'all'; 
    overlay.innerHTML = `
        <div class="ffc-progress-spinner"></div>
        <div id="ffc-prog-status" style="font-weight:bold;">Starting...</div>
    `;
    document.body.appendChild(overlay);
=======
        init: function() {
            this.cacheDOM();
            this.bindEvents();
            this.applyConditionalLogic(); // Initial check
        },
>>>>>>> Stashed changes

        cacheDOM: function() {
            this.$body = $('body');
            this.$form = $('.ffc-frontend-form');
            this.$verifyForm = $('.ffc-verification-form');
        },

<<<<<<< Updated upstream
    // 2. Preparar o palco (Adicionado atributo crossorigin para evitar erro de 'Tainted Canvas')
    const wrapper = document.createElement('div');
    wrapper.className = 'ffc-pdf-temp-wrapper';
    wrapper.innerHTML = `
        <div class="ffc-pdf-stage" id="ffc-capture-target">
            ${bg_image ? `<img src="${bg_image}" class="ffc-pdf-bg-img" crossorigin="anonymous">` : ''}
            <div class="ffc-pdf-user-content">${template}</div>
        </div>
    `;
    document.body.appendChild(wrapper);

    // O SEGREDO PARA MOBILE: 
    // Usamos um delay menor (500ms) para o overlay aparecer, 
    // mas garantimos que o navegador "respire" antes do html2canvas.
    setTimeout(() => {
        if(statusTxt) statusTxt.innerText = "Processing image...";
        
        const target = document.querySelector('#ffc-capture-target');
        
        html2canvas(target, {
            scale: 2, 
            useCORS: true,
            allowTaint: true, // Backup para imagens de domínios diferentes
            backgroundColor: "#ffffff",
            width: 1123,
            height: 794,
            logging: false // Desativa logs para ganhar performance
        }).then(canvas => {
            if(statusTxt) statusTxt.innerText = "Generating PDF...";
            
            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'px',
                format: [1123, 794]
=======
        bindEvents: function() {
            const self = this;

            // 1. Conditional Logic Trigger
            $(document).on('change input', '.ffc-frontend-form input, .ffc-frontend-form select', () => {
                this.applyConditionalLogic();
>>>>>>> Stashed changes
            });

            // 2. Input Masking (Document/Tax ID & Auth Code)
            $(document).on('input', 'input[name="ffc_auth_code"], .ffc-verify-input', (e) => this.maskAuthCode(e));
            $(document).on('input', 'input[name="cpf_rf"]', (e) => this.maskTaxID(e));

            // 3. AJAX Verification
            $(document).on('submit', '.ffc-verification-form', (e) => this.handleVerification(e));
            $(document).on('click', '.ffc-btn-download-verify', (e) => this.downloadVerifiedPDF(e));

            // 4. AJAX Submission
            $(document).on('submit', '.ffc-frontend-form', (e) => this.handleFormSubmit(e));
        },

        // --- Core Functions ---

        applyConditionalLogic: function() {
            $('.ffc-conditional-field').each(function() {
                const $group = $(this);
                const targetName = $group.data('logic-target');
                const requiredVal = String($group.data('logic-val')).trim().toLowerCase();
                const $target = $(`[name="${targetName}"]`);

                if ($target.length > 0) {
                    let currentVal = '';
                    if ($target.is(':radio')) {
                        currentVal = $(`[name="${targetName}"]:checked`).val() || '';
                    } else {
                        currentVal = $target.val() || '';
                    }

                    currentVal = String(currentVal).trim().toLowerCase();

                    if (currentVal === requiredVal && currentVal !== '') {
                        $group.stop().slideDown(300);
                        $group.find('input, select, textarea').prop('disabled', false);
                    } else {
                        $group.stop().slideUp(200);
                        // Disable fields to avoid HTML5 validation on hidden elements
                        $group.find('input, select, textarea').prop('disabled', true);
                    }
                }
            });
        },

        maskAuthCode: function(e) {
            let v = $(e.target).val().toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (v.length > 12) v = v.substring(0, 12);
            let parts = v.match(/.{1,4}/g);
            $(e.target).val(parts ? parts.join('-') : v);
        },

        maskTaxID: function(e) {
            let v = $(e.target).val().replace(/\D/g, '');
            if (v.length > 11) v = v.slice(0, 11);
            
            if (v.length <= 7) {
                v = v.replace(/(\d{3})(\d{3})(\d{1})/, '$1.$2-$3');
            } else {
                v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            }
            $(e.target).val(v);
        },

        handleVerification: function(e) {
            e.preventDefault();
            const $form = $(e.target);
            const $btn = $form.find('button[type="submit"]');
            const $result = $form.closest('.ffc-verification-container').find('.ffc-verify-result');
            const rawCode = $form.find('input[name="ffc_auth_code"]').val();

<<<<<<< Updated upstream
// =========================================================================
// 2. LÓGICA DO FORMULÁRIO (JQUERY)
// =========================================================================
jQuery(function($) {

    // Helper para atualizar o Captcha
    function refreshCaptcha($form, data) {
        if (data.refresh_captcha) {
            $form.find('label[for="ffc_captcha_ans"]').html(data.new_label);
            $form.find('input[name="ffc_captcha_hash"]').val(data.new_hash);
            $form.find('input[name="ffc_captcha_ans"]').val('');
        }
    }

    // --- A. MÁSCARAS DE INPUT ---
    $(document).on('input', 'input[name="ffc_auth_code"], .ffc-verify-input', function() {
        let v = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, ''); 
        if (v.length > 12) v = v.substring(0, 12);
        let parts = v.match(/.{1,4}/g);
        $(this).val(parts ? parts.join('-') : v);
    });

    $(document).on('input', 'input[name="cpf_rf"]', function() {
        let v = $(this).val().replace(/\D/g, ''); 
        if (v.length > 11) v = v.slice(0, 11);
        if (v.length <= 7) {
            v = v.replace(/(\d{3})(\d{3})(\d{1})/, '$1.$2-$3');
        } else {
            v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }
        $(this).val(v);
    });

    // --- B. VERIFICAÇÃO DE CERTIFICADO (AJAX) ---
    $(document).on('submit', '.ffc-verification-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const $resultContainer = $form.closest('.ffc-verification-container').find('.ffc-verify-result');
        const rawCode = $form.find('input[name="ffc_auth_code"]').val();
        
        if(!rawCode) return;
        const cleanCode = rawCode.replace(/[^a-zA-Z0-9]/g, ''); 

        $btn.prop('disabled', true).text(ffc_ajax.strings.verifying);
        $resultContainer.fadeOut();
        
        $.ajax({
            url: ffc_ajax.ajax_url,
            type: 'POST',
            data: {
=======
            if (!rawCode) return;
            const cleanCode = rawCode.replace(/[^a-zA-Z0-9]/g, '');

            $btn.prop('disabled', true).text(ffc_ajax.strings.verifying);
            $result.fadeOut();

            $.post(ffc_ajax.ajax_url, {
>>>>>>> Stashed changes
                action: 'ffc_verify_certificate',
                ffc_auth_code: cleanCode,
                nonce: ffc_ajax.nonce
            }, (response) => {
                if (response.success) {
                    $result.html(response.data.html).fadeIn();
                    if (response.data.pdf_data) this.lastVerifiedPdfData = response.data.pdf_data;
                } else {
                    // Criteria 4: Use ffc-error class instead of inline red color
                    $result.html(`<div class="ffc-error">${response.data.message}</div>`).fadeIn();
                }
            }).fail(() => alert(ffc_ajax.strings.connectionError))
              .always(() => $btn.prop('disabled', false).text(ffc_ajax.strings.verify));
        },

        handleFormSubmit: function(e) {
            e.preventDefault();
            const $form = $(e.target);
            const $btn = $form.find('.ffc-submit-btn');
            const $msg = $form.find('.ffc-form-response');
            
            // Validations
            const $taxInput = $form.find('input[name="cpf_rf"]');
            if ($taxInput.length) {
                const rawVal = $taxInput.val().replace(/\D/g, '');
                if (rawVal && rawVal.length !== 7 && rawVal.length !== 11) {
                    alert(ffc_ajax.strings.idMustHaveDigits);
                    return false;
                }
            }

<<<<<<< Updated upstream
    // --- C. SUBMISSÃO E GERAÇÃO (AJAX) ---
    $(document).on('submit', '.ffc-submission-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const $msg = $form.find('.ffc-message');
        
        const rawCPF = $form.find('input[name="cpf_rf"]').val() ? $form.find('input[name="cpf_rf"]').val().replace(/\D/g, '') : '';
        
        if (rawCPF && rawCPF.length !== 7 && rawCPF.length !== 11) {
            alert(ffc_ajax.strings.idMustHaveDigits);
            return false;
        }
=======
            $btn.prop('disabled', true).addClass('ffc-loading');
            $msg.removeClass('ffc-success ffc-error').hide().text(ffc_ajax.strings.processing);
>>>>>>> Stashed changes

            $.ajax({
                url: ffc_ajax.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=ffc_submit_form&nonce=' + ffc_ajax.nonce + '&form_id=' + $form.data('form-id'),
                success: (response) => {
                    if (response.success) {
                        $msg.addClass('ffc-success').html(response.data.message).fadeIn();
                        
                        if (response.data.pdf_data && window.FFCPDFEngine) {
                            // Criteria 4: Avoided inline font-size, handled by container or common classes
                            $msg.append(`<div class="ffc-generating-notice">${ffc_ajax.strings.generatingCertificate}</div>`);
                            window.FFCPDFEngine.generate(response.data.pdf_data);
                        }
                    } else {
                        $msg.addClass('ffc-error').html(response.data.message).fadeIn();
                    }

                    if (response.data && response.data.new_hash) this.refreshCaptcha($form, response.data);
                },
                error: () => $msg.addClass('ffc-error').text(ffc_ajax.strings.connectionError).fadeIn()
            }).always(() => $btn.prop('disabled', false).removeClass('ffc-loading'));
        },

        downloadVerifiedPDF: function(e) {
            e.preventDefault();
            const pdfData = this.lastVerifiedPdfData || (window.lastVerifiedPdfData || null);
            if (pdfData && window.FFCPDFEngine) {
                window.FFCPDFEngine.generate(pdfData);
            } else {
                alert(ffc_ajax.strings.pdfDataNotFound);
            }
<<<<<<< Updated upstream
        });
    });

    // --- D. LÓGICA DO BOTÃO DOWNLOAD ADMIN ---
    // (Agora fora do evento de submissão, funcionando de forma independente)
    $(document).on('click', '.ffc-admin-download-btn', function(e) {
        const $btn = $(this);
        
        // Ativa o spinner no botão do admin
        $btn.addClass('ffc-btn-loading');
        
        // Escuta o evento que dispararemos na função generateCertificate ao terminar
        $(document).one('ffc_pdf_done', function() {
            $btn.removeClass('ffc-btn-loading');
        });
    });

});
=======
        },

        refreshCaptcha: function($form, data) {
            if (data.refresh_captcha) {
                $form.find('.ffc-math-captcha label').html(data.new_label);
                $form.find('input[name="ffc_captcha_hash"]').val(data.new_hash);
                $form.find('input[name="ffc_captcha_ans"]').val('');
            }
        }
    };

    $(document).ready(() => FFC_Public.init());

})(jQuery);
>>>>>>> Stashed changes
