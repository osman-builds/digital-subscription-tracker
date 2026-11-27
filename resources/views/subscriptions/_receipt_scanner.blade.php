{{-- Ephemeral In-Memory Multimodal Vision AI Receipt & PDF Scanner (Zero Disk / Zero DB Blob) --}}
<div id="receiptScanner" class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #6366f1 !important;">
    <div class="card-body p-3 p-sm-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-file-earmark-medical text-primary me-2"></i>AI Receipt &amp; PDF Auto-Scanner
                </h5>
                <p class="text-muted small mb-0">
                    Upload a billing screenshot, photo, or PDF invoice to auto-fill your subscription details and check for price changes. Files are never stored.
                </p>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                <i class="bi bi-shield-check me-1"></i>Private · Not Stored
            </span>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <input type="file" id="receiptFileInput" class="form-control form-control-sm" style="max-width: 340px; width: 100%;"
                   accept="image/jpeg,image/png,image/webp,application/pdf"
                   aria-label="Upload subscription receipt image or PDF">
            <button type="button" id="btnScanReceipt" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-stars me-1"></i>Scan &amp; Pre-Fill
            </button>
            @if(auth()->user()?->isSandbox())
                <button type="button" id="btnSandboxDemoScan" class="btn btn-outline-primary btn-sm px-3">
                    <i class="bi bi-play-circle me-1"></i>Try Sample Receipt
                </button>
            @endif
            <span id="scannerSpinner" class="small text-primary d-none">
                <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Analyzing receipt...
            </span>
        </div>
        <div class="form-text mt-1">Allowed: JPG, PNG, WebP, or PDF up to 4 MB. Personal card numbers and addresses are automatically excluded.</div>

        {{-- Error Alert --}}
        <div id="scanErrorAlert" class="alert alert-danger py-2 px-3 mt-3 mb-0 small d-none" role="alert"></div>

        {{-- Duplicate Receipt Guard Alert --}}
        <div id="scanDuplicateAlert" class="alert alert-warning py-2 px-3 mt-3 mb-0 small d-none" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>Duplicate Receipt Detected:</strong>
            <span id="scanDuplicateText"></span>
        </div>

        {{-- Verified HITL Result Banner --}}
        <div id="scanSuccessBanner" class="alert alert-success py-3 px-3 mt-3 mb-0 small d-none" aria-live="polite">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <strong>Receipt Extracted — Review &amp; Accept to Save</strong>
                    <span id="scanModelBadge" class="badge bg-dark bg-opacity-25 ms-1"></span>
                </div>
                <span id="scanConfidenceBadge" class="badge bg-success">Confidence: --%</span>
            </div>
            <div id="scanExtractedSummary" class="mt-2 p-2 rounded bg-body-tertiary border d-flex flex-wrap gap-3 text-body"></div>
            <div id="scanFieldBadges" class="d-flex flex-wrap gap-2 mt-2"></div>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-2 border-top border-success border-opacity-25">
                <span class="text-muted">
                    <i class="bi bi-shield-check me-1"></i>Form auto-filled below. Nothing is saved until you accept.
                </span>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" id="btnAcceptAndSaveScan" class="btn btn-success btn-sm fw-semibold px-3">
                        <i class="bi bi-check2-circle me-1"></i>Accept &amp; Save Subscription
                    </button>
                    <button type="button" id="btnReviewScanForm" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil-square me-1"></i>Edit Details Below
                    </button>
                    <button type="button" id="btnDiscardScan" class="btn btn-link btn-sm text-danger text-decoration-none px-1">
                        Discard
                    </button>
                </div>
            </div>
        </div>

        {{-- Matched Existing Subscription Action Card (Price Hike / Renewal Update) --}}
        <div id="scanMatchedCard" class="card bg-body-tertiary border border-warning border-opacity-50 mt-3 d-none">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <span class="badge bg-warning text-dark mb-1">
                            <i class="bi bi-arrow-left-right me-1"></i>Existing Subscription Match
                        </span>
                        <h6 class="fw-bold mb-0" id="scanMatchedTitle">Matched Subscription</h6>
                        <small class="text-muted" id="scanMatchedSubtitle"></small>
                    </div>
                    <form id="scanApplyExistingForm" method="POST" action="#">
                        @csrf
                        <input type="hidden" name="amount" id="applyAmount">
                        <input type="hidden" name="currency" id="applyCurrency">
                        <input type="hidden" name="billing_cycle" id="applyCycle">
                        <input type="hidden" name="next_billing_date" id="applyNextDate">
                        <input type="hidden" name="ai_audit_id" id="applyAuditId">
                        <input type="hidden" name="input_source" id="applyInputSource" value="ai_pdf">
                        <button type="submit" class="btn btn-warning btn-sm fw-semibold text-dark">
                            <i class="bi bi-check2-all me-1"></i>Accept &amp; Update Existing Subscription
                        </button>
                    </form>
                </div>
                <small class="text-muted">Or click <strong>Accept &amp; Save Subscription</strong> above / edit below to save as a separate subscription.</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('receiptFileInput');
    const btnScan = document.getElementById('btnScanReceipt');
    const btnSandboxDemo = document.getElementById('btnSandboxDemoScan');
    const spinner = document.getElementById('scannerSpinner');
    const errorAlert = document.getElementById('scanErrorAlert');
    const duplicateAlert = document.getElementById('scanDuplicateAlert');
    const duplicateText = document.getElementById('scanDuplicateText');
    const successBanner = document.getElementById('scanSuccessBanner');
    const extractedSummary = document.getElementById('scanExtractedSummary');
    const modelBadge = document.getElementById('scanModelBadge');
    const confidenceBadge = document.getElementById('scanConfidenceBadge');
    const fieldBadges = document.getElementById('scanFieldBadges');
    const matchedCard = document.getElementById('scanMatchedCard');
    const matchedTitle = document.getElementById('scanMatchedTitle');
    const matchedSubtitle = document.getElementById('scanMatchedSubtitle');
    const applyForm = document.getElementById('scanApplyExistingForm');
    const btnAcceptAndSave = document.getElementById('btnAcceptAndSaveScan');
    const btnReviewForm = document.getElementById('btnReviewScanForm');
    const btnDiscardScan = document.getElementById('btnDiscardScan');
    const createForm = document.getElementById('subscriptionCreateForm');

    const scanEndpoint = @json(route('subscriptions.scan_receipt'));
    const csrfToken = @json(csrf_token());

    function resetAlerts() {
        errorAlert.classList.add('d-none');
        duplicateAlert.classList.add('d-none');
        successBanner.classList.add('d-none');
        matchedCard.classList.add('d-none');
        fieldBadges.innerHTML = '';
        if (extractedSummary) {
            extractedSummary.innerHTML = '';
        }
    }

    function setLoading(loading) {
        btnScan.disabled = loading;
        if (btnSandboxDemo) {
            btnSandboxDemo.disabled = loading;
        }
        spinner.classList.toggle('d-none', !loading);
    }

    function highlightField(el) {
        if (!el) return;
        el.classList.add('border-primary');
        setTimeout(() => el.classList.remove('border-primary'), 4000);
    }

    function appendSummaryItem(label, value) {
        if (!extractedSummary || !value) return;
        const item = document.createElement('div');
        const lbl = document.createElement('span');
        lbl.className = 'text-muted me-1';
        lbl.textContent = label + ':';
        const val = document.createElement('strong');
        val.textContent = value;
        item.appendChild(lbl);
        item.appendChild(val);
        extractedSummary.appendChild(item);
    }

    function applyExtractedToForm(data) {
        const ex = data.extracted;
        if (!ex) return;

        const nameEl = document.getElementById('sub_name');
        const amountEl = document.getElementById('sub_amount');
        const currencyEl = document.getElementById('sub_currency');
        const cycleEl = document.getElementById('sub_billing_cycle');
        const startEl = document.getElementById('sub_start_date');
        const nextEl = document.getElementById('sub_next_billing_date');
        const urlEl = document.getElementById('sub_website_url');
        const cancelEl = document.getElementById('sub_cancellation_url');
        const logoEl = document.getElementById('sub_logo_url');
        const previewEl = document.getElementById('liveLogoPreview');
        const catEl = document.getElementById('sub_category_id');
        const descEl = document.getElementById('sub_description');
        const trialEl = document.getElementById('sub_is_trial');
        const trialDateEl = document.getElementById('sub_trial_ends_at');
        const trialWrap = document.getElementById('trialEndDateWrapper');
        const sourceEl = document.getElementById('sub_input_source');
        const auditEl = document.getElementById('sub_ai_audit_id');

        if (nameEl) { nameEl.value = ex.name || ''; if (ex.name) highlightField(nameEl); }
        if (amountEl) { amountEl.value = ex.amount || ''; if (ex.amount) highlightField(amountEl); }
        if (currencyEl && ex.currency) {
            currencyEl.value = ex.currency;
            currencyEl.dispatchEvent(new Event('change'));
            highlightField(currencyEl);
        }
        if (cycleEl && ex.billing_cycle) { cycleEl.value = ex.billing_cycle; highlightField(cycleEl); }
        if (startEl && ex.start_date) { startEl.value = ex.start_date; highlightField(startEl); }
        if (nextEl && ex.next_billing_date) { nextEl.value = ex.next_billing_date; highlightField(nextEl); }
        if (urlEl) {
            urlEl.value = ex.website_url || '';
            urlEl.dataset.autoInferred = 'false';
            if (ex.website_url) highlightField(urlEl);
        }
        if (cancelEl) { cancelEl.value = ex.cancellation_url || ''; if (ex.cancellation_url) highlightField(cancelEl); }
        if (logoEl) { logoEl.value = ex.logo_url || ''; }
        if (previewEl && ex.logo_url) { previewEl.src = ex.logo_url; }
        if (catEl) {
            catEl.value = ex.category_id ? String(ex.category_id) : '';
            if (ex.category_id) highlightField(catEl);
        }
        if (descEl) { descEl.value = ex.description || ''; }
        if (trialEl) {
            trialEl.checked = Boolean(ex.is_trial);
            if (trialWrap) trialWrap.classList.toggle('d-none', !trialEl.checked);
        }
        if (trialDateEl) { trialDateEl.value = ex.trial_ends_at || ''; }
        if (sourceEl && data.input_source) { sourceEl.value = data.input_source; }
        if (auditEl) { auditEl.value = data.audit_id ? String(data.audit_id) : ''; }

        const cycleLabels = {
            weekly: 'Weekly',
            monthly: 'Monthly',
            quarterly: 'Quarterly',
            semi_annual: 'Semi-Annual',
            annual: 'Annual',
        };
        if (extractedSummary) {
            extractedSummary.innerHTML = '';
            appendSummaryItem('Service', ex.name || 'Needs review');
            appendSummaryItem('Amount', (ex.currency || '') + ' ' + (ex.amount || '0.00'));
            appendSummaryItem('Cycle', cycleLabels[ex.billing_cycle] || ex.billing_cycle || 'Monthly');
            appendSummaryItem('Next Renewal', ex.next_billing_date || '—');
            if (ex.category_name) {
                appendSummaryItem('Category', ex.category_name);
            }
            if (ex.is_trial) {
                appendSummaryItem('Trial Ends', ex.trial_ends_at || 'Yes');
            }
        }

        modelBadge.textContent = data.sandbox_simulated ? 'Demo Sample' : (data.model_used || 'SubShield AI');
        confidenceBadge.textContent = 'Overall Confidence: ' + Number(ex.confidence_score || 0).toFixed(0) + '%';

        const fieldConf = ex.field_confidence || {};
        Object.keys(fieldConf).forEach(function (fieldKey) {
            const pill = document.createElement('span');
            pill.className = 'badge bg-light text-dark border';
            pill.textContent = fieldKey.replace(/_/g, ' ') + ': ' + fieldConf[fieldKey] + '%';
            fieldBadges.appendChild(pill);
        });
        successBanner.classList.remove('d-none');

        if (data.matched_subscription) {
            const m = data.matched_subscription;
            matchedTitle.textContent = m.name + ' (Current: ' + m.current_currency + ' ' + m.current_amount + ' -> Scanned: ' + m.new_currency + ' ' + m.new_amount + ')';
            let subText = 'Next renewal will update automatically to ' + ex.next_billing_date + ' and record a billing ledger entry.';
            if (m.price_delta !== null && m.price_delta > 0) {
                subText = 'Price hike of +' + m.symbol + Number(m.price_delta).toFixed(2) + ' (+' + m.price_change_percent + '%) detected! Click to accept, update & log price creep.';
            } else if (m.price_delta !== null && m.price_delta < 0) {
                subText = 'Price reduction of ' + m.symbol + Number(m.price_delta).toFixed(2) + ' (' + m.price_change_percent + '%) detected.';
            }
            matchedSubtitle.textContent = subText;

            applyForm.action = m.apply_url;
            document.getElementById('applyAmount').value = ex.amount;
            document.getElementById('applyCurrency').value = ex.currency;
            document.getElementById('applyCycle').value = ex.billing_cycle;
            document.getElementById('applyNextDate').value = ex.next_billing_date;
            document.getElementById('applyAuditId').value = data.audit_id ? String(data.audit_id) : '';
            document.getElementById('applyInputSource').value = data.input_source || 'ai_pdf';
            matchedCard.classList.remove('d-none');
        }
    }

    if (btnAcceptAndSave && createForm) {
        btnAcceptAndSave.addEventListener('click', function () {
            if (!createForm.reportValidity()) {
                createForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            btnAcceptAndSave.disabled = true;
            btnAcceptAndSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Saving...';
            createForm.submit();
        });
    }

    if (btnReviewForm && createForm) {
        btnReviewForm.addEventListener('click', function () {
            createForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            const nameEl = document.getElementById('sub_name');
            if (nameEl) {
                setTimeout(() => nameEl.focus(), 300);
            }
        });
    }

    if (btnDiscardScan) {
        btnDiscardScan.addEventListener('click', function () {
            resetAlerts();
            const sourceEl = document.getElementById('sub_input_source');
            const auditEl = document.getElementById('sub_ai_audit_id');
            if (sourceEl) sourceEl.value = 'manual';
            if (auditEl) auditEl.value = '';
            if (fileInput) fileInput.value = '';
        });
    }

    async function rasterizePdfFirstPage(pdfFile) {
        try {
            if (!window.pdfjsLib) {
                await new Promise((resolve, reject) => {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js';
                    s.crossOrigin = 'anonymous';
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }
            if (!window.pdfjsLib) return pdfFile;
            const buffer = await pdfFile.arrayBuffer();
            const pdf = await window.pdfjsLib.getDocument({ data: buffer, disableWorker: true }).promise;
            const page = await pdf.getPage(1);
            const viewport = page.getViewport({ scale: 2.0 });
            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
            if (!blob) return pdfFile;
            return new File([blob], pdfFile.name.replace(/\.pdf$/i, '.png'), { type: 'image/png' });
        } catch (e) {
            return pdfFile;
        }
    }

    async function runScan(allowEmptyForSandbox) {
        resetAlerts();
        let file = fileInput.files && fileInput.files[0];

        if (!file && !allowEmptyForSandbox) {
            errorAlert.textContent = 'Please select a receipt image (JPG/PNG/WebP) or PDF invoice first.';
            errorAlert.classList.remove('d-none');
            return;
        }

        setLoading(true);
        try {
            const formData = new FormData();
            if (file) {
                if (file.type === 'application/pdf' || /\.pdf$/i.test(file.name)) {
                    file = await rasterizePdfFirstPage(file);
                }
                formData.append('receipt', file);
            }

            const response = await fetch(scanEndpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const data = await response.json();
            if (!response.ok) {
                const msg = (data.errors && data.errors.receipt && data.errors.receipt[0])
                    || data.message
                    || 'Receipt scan failed.';
                errorAlert.textContent = msg;
                errorAlert.classList.remove('d-none');
                return;
            }

            if (data.duplicate_receipt) {
                const target = data.existing_subscription_name
                    ? ' This exact receipt was already verified for "' + data.existing_subscription_name + '".'
                    : ' This exact receipt was already verified earlier.';
                duplicateText.textContent = target + ' AI scan was skipped to protect your quota.';
                duplicateAlert.classList.remove('d-none');
                return;
            }

            applyExtractedToForm(data);
        } catch (err) {
            errorAlert.textContent = 'Network or server error while analyzing receipt. Please enter fields manually.';
            errorAlert.classList.remove('d-none');
        } finally {
            setLoading(false);
        }
    }

    if (btnScan) {
        btnScan.addEventListener('click', () => runScan(false));
    }
    if (btnSandboxDemo) {
        btnSandboxDemo.addEventListener('click', () => runScan(true));
    }
});
</script>
@endpush
