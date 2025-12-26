/**
 * FFC PDF Engine - Versão Centralizada e Robusta
 * Gerencia a conversão de HTML para Imagem e então para PDF.
 */
window.FFCPDFEngine = {
    generate: function(data) {
        const { template, bg_image, form_title } = data;

        // Verificação de Dependências
        if (typeof html2canvas === 'undefined' || typeof window.jspdf === 'undefined') {
            console.error("FFC Error: html2canvas ou jsPDF não carregados.");
            alert("Erro interno: Bibliotecas de PDF não encontradas.");
            return;
        }

        // 1. Criar Overlay de Progresso (Feedback visual para o usuário)
        const overlay = document.createElement('div');
        overlay.className = 'ffc-pdf-progress-overlay';
        overlay.innerHTML = `
            <div class="ffc-pdf-progress-content">
                <div class="ffc-progress-spinner"></div>
                <div id="ffc-prog-status">Iniciando geração...</div>
            </div>
        `;
        document.body.appendChild(overlay);
        const statusTxt = document.getElementById('ffc-prog-status');

        // 2. Preparar o Palco (Wrapper fora da área visível)
        // Usamos dimensões fixas de A4 Paisagem: 1123px x 794px
        const wrapper = document.createElement('div');
        wrapper.className = 'ffc-pdf-temp-wrapper';
        wrapper.innerHTML = `
            <div class="ffc-pdf-stage" id="ffc-capture-target" style="width:1123px; height:794px; position:relative; overflow:hidden; background:#fff;">
                ${bg_image ? `<img src="${bg_image}" class="ffc-pdf-bg-img" crossorigin="anonymous" style="width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0; z-index:1;">` : ''}
                <div class="ffc-pdf-user-content" style="position:relative; z-index:10; width:100%; height:100%;">
                    ${template}
                </div>
            </div>
        `;
        document.body.appendChild(wrapper);

        // Delay para garantir renderização de fontes e imagens antes da captura
        setTimeout(() => {
            if(statusTxt) statusTxt.innerText = "Renderizando certificado...";
            
            const target = document.querySelector('#ffc-capture-target');
            
            html2canvas(target, {
                scale: 2, // Aumenta a qualidade (DPI) para impressão
                useCORS: true, // Essencial para carregar a imagem de fundo de outros domínios/CDNs
                logging: false,
                backgroundColor: "#ffffff",
                width: 1123,
                height: 794
            }).then(canvas => {
                if(statusTxt) statusTxt.innerText = "Criando arquivo PDF...";
                
                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;
                
                // Configuração Landscape A4
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'px',
                    format: [1123, 794]
                });

                pdf.addImage(imgData, 'PNG', 0, 0, 1123, 794);
                
                if(statusTxt) statusTxt.innerText = "Sucesso! Baixando...";
                pdf.save(`${form_title || 'certificado'}.pdf`);
                
                // Limpeza de rastro no DOM
                setTimeout(() => {
                    if(document.body.contains(wrapper)) document.body.removeChild(wrapper);
                    if(document.body.contains(overlay)) document.body.removeChild(overlay);
                    jQuery(document).trigger('ffc_pdf_done');
                }, 1000);

            }).catch(err => {
                console.error("FFC Engine Error:", err);
                if(document.body.contains(overlay)) document.body.removeChild(overlay);
                if(document.body.contains(wrapper)) document.body.removeChild(wrapper);
                alert("Houve um erro técnico ao gerar o PDF. Verifique o console.");
                jQuery(document).trigger('ffc_pdf_done');
            });
        }, 800); 
    }
};

// Compatibilidade
window.generateCertificate = data => window.FFCPDFEngine.generate(data);