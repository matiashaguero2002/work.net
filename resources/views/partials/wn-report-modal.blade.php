<div class="wn-modal-overlay" id="reportModal" onclick="closeReportModal(event)">
    <div class="wn-modal" onclick="event.stopPropagation()">
        <div class="wn-modal-header">
            <h2 id="reportModalTitle">Reportar oferta</h2>
            <button class="wn-modal-close" onclick="closeReportModal(event)" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="wn-modal-body">
            <p class="wn-modal-subtitle">Reportá la oferta <strong id="reportCompany">Nombre de la empresa</strong> por contenido sospechoso o inapropiado.</p>

            <form id="reportForm" novalidate>
                <input type="hidden" name="oferta_id" id="reportOfertaId">

                <div class="wn-form-group">
                    <label class="wn-label">Tipo de reporte</label>
                    <select class="wn-input" id="reportType" name="tipo" required>
                        <option value="">Seleccioná un tipo</option>
                    </select>
                </div>

                <div class="wn-form-group">
                    <label class="wn-label">Motivo</label>
                    <textarea class="wn-input" id="reportMotivo" name="motivo" rows="3" placeholder="Describir el motivo del reporte..." required></textarea>
                </div>

                <small class="wn-text-muted">Los reportes ayudan a mantener la calidad de la plataforma. Tu identidad es anónima para el empleador.</small>
            </form>
        </div>

        <div class="wn-modal-footer">
            <button type="button" class="btn-wn-secondary" onclick="closeReportModal(event)">
                <i class="bi bi-x-lg"></i> Cancelar
            </button>
            <button type="submit" form="reportForm" class="btn-wn-primary">
                <i class="bi bi-send"></i> Enviar reporte
            </button>
        </div>
    </div>
</div>