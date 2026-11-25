{{-- Ephemeral In-Memory Multimodal Vision AI Receipt & PDF Scanner (Zero Disk / Zero DB Blob) --}}
<div id="receiptScanner" class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #6366f1 !important;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-file-earmark-medical text-primary me-2"></i>AI Receipt &amp; PDF Auto-Scanner
                </h5>
                <p class="text-muted small mb-0">
                    Upload a billing screenshot, photo, or PDF invoice. SubShield reads fields in RAM, checks for existing price hikes, lets you verify everything, and discards the file immediately.
                </p>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                <i class="bi bi-shield-lock me-1"></i>Zero-Disk Policy (0 bytes saved)
            </span>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <input type="file" id="receiptFileInput" class="form-control form-control-sm" style="max-width: 340px;"
                   accept="image/jpeg,image/png,image/webp,application/pdf"
                   aria-label="Upload subscription receipt image or PDF">
            <button type="button" id="btnScanReceipt" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-stars me-1"></i>Scan &amp; Pre-Fill
            </button>
            @if(auth()->user()?->isSandbox())
                <button type="button" id="btnSandboxDemoScan" class="btn btn-outline-primary btn-sm px-3">
                    <i class="bi bi-play-circle me-1"></i>Try Simulated Demo Scan
                </button>
            @endif
            <span id="scannerSpinner" class="small text-primary d-none">
                <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Analyzing receipt in RAM...
            </span>
        </div>
        <div class="form-text mt-1">Allowed: JPG, PNG, WebP, or PDF up to 4 MB. Personal card numbers and addresses are automatically excluded.</div>

        {{-- Error Alert --}}
        <div id="scanErrorAlert" class="alert alert-danger py-2 px-3 mt-3 mb-0 small d-none" role="alert"></div>

        {{-- Duplicate Receipt Guard Alert --}}
        <div id="scanDuplicateAlert" class="alert alert-warning py-2 px-3 mt-3 mb-0 small d-none" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>Duplicate Receipt Detected (SHA-256 Match):</strong>
            <span id="scanDuplicateText"></span>
        </div>

        {{-- Verified HITL Result Banner --}}
        <div id="scanSuccessBanner" class="alert alert-success py-2 px-3 mt-3 mb-0 small d-none" role="status">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <strong>Receipt Extracted — Verify Before Saving</strong>
                    <span id="scanModelBadge" class="badge bg-dark bg-opacity-25 ms-1"></span>
                </div>
                <span id="scanConfidenceBadge" class="badge bg-success">Confidence: --%</span>
            </div>
            <div id="scanFieldBadges" class="d-flex flex-wrap gap-2 mt-2"></div>
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
                            <i class="bi bi-check2-all me-1"></i>Update Existing &amp; Log Price History
                        </button>
                    </form>
                </div>
                <small class="text-muted">Or scroll down to review &amp; save as a separate subscription instead.</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('receiptFileInput');
    const btnScan = document.getElementById('btnScanReceipt');
    const btnSandboxDemo = document.getElementById('btnSandboxDemoScan');
    const spinner = document.getElementById('scannerSpinner');
    const errorAlert = document.getElementById('scanErrorAlert');
    const duplicateAlert = document.getElementById('scanDuplicateAlert');
    const duplicateText = document.getElementById('scanDuplicateText');
    const successBanner = document.getElementById('scanSuccessBanner');
    const modelBadge = document.getElementById('scanModelBadge');
    const confidenceBadge = document.getElementById('scanConfidenceBadge');
    const fieldBadges = document.getElementById('scanFieldBadges');
    const matchedCard = document.getElementById('scanMatchedCard');
    const matchedTitle = document.getElementById('scanMatchedTitle');
    const matchedSubtitle = document.getElementById('scanMatchedSubtitle');
    const applyForm = document.getElementById('scanApplyExistingForm');

    const scanEndpoint = @json(route('subscriptions.scan_receipt'));
    const csrfToken = @json(csrf_token());

    function resetAlerts() {
        errorAlert.classList.add('d-none');
        duplicateAlert.classList.add('d-none');
        successBanner.classList.add('d-none');
        matchedCard.classList.add('d-none');
        fieldBadges.innerHTML = '';
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

        if (nameEl && ex.name) { nameEl.value = ex.name; highlightField(nameEl); }
        if (amountEl && ex.amount) { amountEl.value = ex.amount; highlightField(amountEl); }
        if (currencyEl && ex.currency) { currencyEl.value = ex.currency; highlightField(currencyEl); }
        if (cycleEl && ex.billing_cycle) { cycleEl.value = ex.billing_cycle; highlightField(cycleEl); }
        if (startEl && ex.start_date) { startEl.value = ex.start_date; highlightField(startEl); }
        if (nextEl && ex.next_billing_date) { nextEl.value = ex.next_billing_date; highlightField(nextEl); }
        if (urlEl && ex.website_url) { urlEl.value = ex.website_url; highlightField(urlEl); }
        if (cancelEl && ex.cancellation_url) { cancelEl.value = ex.cancellation_url; highlightField(cancelEl); }
        if (logoEl && ex.logo_url) { logoEl.value = ex.logo_url; }
        if (previewEl && ex.logo_url) { previewEl.src = ex.logo_url; }
        if (catEl && ex.category_id) { catEl.value = String(ex.category_id); highlightField(catEl); }
        if (descEl && ex.description) { descEl.value = ex.description; }
        if (trialEl) {
            trialEl.checked = Boolean(ex.is_trial);
            if (trialWrap) trialWrap.classList.toggle('d-none', !trialEl.checked);
        }
        if (trialDateEl && ex.trial_ends_at) { trialDateEl.value = ex.trial_ends_at; }
        if (sourceEl && data.input_source) { sourceEl.value = data.input_source; }
        if (auditEl) { auditEl.value = data.audit_id ? String(data.audit_id) : ''; }

        modelBadge.textContent = data.sandbox_simulated ? 'Sandbox Demo Simulator' : (data.model_used || 'Gemini Flash');
        confidenceBadge.textContent = 'Overall Confidence: ' + Number(ex.confidence_score || 0).toFixed(0) + '%';

        const fieldConf = ex.field_confidence || {};
        Object.keys(fieldConf).forEach(function (fieldKey) {
            const pill = document.createElement('span');
            pill.className = 'badge bg-light text-dark border';
            pill.textContent = fieldKey.replace('_', ' ') + ': ' + fieldConf[fieldKey] + '%';
            fieldBadges.appendChild(pill);
        });
        successBanner.classList.remove('d-none');

        if (data.matched_subscription) {
            const m = data.matched_subscription;
            matchedTitle.textContent = m.name + ' (Current: ' + m.current_currency + ' ' + m.current_amount + ' -> Scanned: ' + m.new_currency + ' ' + m.new_amount + ')';
            let subText = 'Next renewal will update automatically to ' + ex.next_billing_date + ' and record a billing ledger entry.';
            if (m.price_delta !== null && m.price_delta > 0) {
                subText = 'Price hike of +' + m.symbol + Number(m.price_delta).toFixed(2) + ' (+' + m.price_change_percent + '%) detected! Click to update & log price creep.';
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

    async function runScan(allowEmptyForSandbox) {
        resetAlerts();
        const file = fileInput.files && fileInput.files[0];

        if (!file && !allowEmptyForSandbox) {
            errorAlert.textContent = 'Please select a receipt image (JPG/PNG/WebP) or PDF invoice first.';
            errorAlert.classList.remove('d-none');
            return;
        }

        const formData = new FormData();
        if (file) {
            formData.append('receipt', file);
        }

        setLoading(true);
        try {
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
                duplicateText.textContent = target + ' Gemini API was skipped to protect your quota.';
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
