<div class="wn-modal-overlay" id="offerModal" onclick="closeModal(event)">
    <div class="wn-modal" onclick="event.stopPropagation()">
        <div class="wn-modal-image-wrapper">
            <img src="" alt="" class="wn-modal-image" id="modalImage">
            <button class="wn-modal-close" onclick="closeModal(event)" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="wn-modal-logo-badge" id="modalLogo">SM</div>
        </div>

        <div class="wn-modal-body">
            <h2 class="wn-modal-title" id="modalTitle">Título del puesto</h2>
            <p class="wn-modal-company" id="modalCompany">
                <i class="bi bi-building"></i>
                <span>Nombre de la empresa</span>
            </p>

            <div class="wn-modal-badges" id="modalBadges"></div>

            <div class="wn-modal-section">
                <div class="wn-modal-section-title">
                    <i class="bi bi-file-text"></i>
                    <span>Descripción del puesto</span>
                </div>
                <div class="wn-modal-section-content" id="modalDescription"></div>
            </div>

            <div class="wn-modal-section">
                <div class="wn-modal-section-title">
                    <i class="bi bi-list-check"></i>
                    <span>Requisitos</span>
                </div>
                <div class="wn-modal-tags" id="modalRequirements"></div>
            </div>

            <div class="wn-modal-section">
                <div class="wn-modal-section-title">
                    <i class="bi bi-info-circle"></i>
                    <span>Información de la oferta</span>
                </div>
                <div class="wn-modal-detail-grid">
                    <div class="wn-modal-detail-item">
                        <i class="bi bi-cash-coin"></i>
                        <div>
                            <div class="wn-modal-detail-label">Remuneración</div>
                            <div class="wn-modal-detail-value" id="modalSalary">A convenir</div>
                        </div>
                    </div>
                    <div class="wn-modal-detail-item">
                        <i class="bi bi-clock-history"></i>
                        <div>
                            <div class="wn-modal-detail-label">Publicado</div>
                            <div class="wn-modal-detail-value" id="modalPublished">Hace 2 días</div>
                        </div>
                    </div>
                    <div class="wn-modal-detail-item">
                        <i class="bi bi-laptop"></i>
                        <div>
                            <div class="wn-modal-detail-label">Modalidad</div>
                            <div class="wn-modal-detail-value" id="modalModality">Presencial</div>
                        </div>
                    </div>
                    <div class="wn-modal-detail-item">
                        <i class="bi bi-briefcase"></i>
                        <div>
                            <div class="wn-modal-detail-label">Contrato</div>
                            <div class="wn-modal-detail-value" id="modalContract">Tiempo completo</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wn-modal-section">
                <div class="wn-modal-section-title">
                    <i class="bi bi-star"></i>
                    <span>Beneficios</span>
                </div>
                <div class="wn-modal-section-content" id="modalBenefits"></div>
            </div>

            <div class="wn-modal-section">
                <div class="wn-modal-section-title">
                    <i class="bi bi-telephone"></i>
                    <span>Contacto</span>
                </div>
                <div class="wn-modal-section-content" id="modalContact"></div>
            </div>
        </div>

        <div class="wn-modal-footer">
            <button class="btn-wn-secondary" onclick="closeModal(event)">
                <i class="bi bi-x-lg"></i> Cerrar
            </button>
            <button class="btn-wn-primary">
                <i class="bi bi-send"></i> Postularme a esta oferta
            </button>
        </div>
    </div>
</div>
