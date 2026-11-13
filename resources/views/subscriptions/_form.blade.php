{{-- Shared form fields for create & edit with Intelligent URL, Fuzzy Typo Correction & Official Brand Logo Sync --}}

{{-- ── Quick Brand Auto-Detect Bar ───────────────────────────────────── --}}
<div class="card border-0 mb-4" style="background: rgba(99, 102, 241, 0.05); border: 1px dashed rgba(99, 102, 241, 0.3) !important; border-radius: 14px;">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
            <span class="small fw-bold text-primary">
                <i class="bi bi-magic me-1"></i>Quick Brand Auto-Fill
            </span>
            <small class="text-muted">Pick a popular service or type below — spelling typos are automatically detected and corrected</small>
        </div>

        {{-- One-click popular service pills --}}
        <div class="d-flex flex-wrap gap-2 mb-1" id="quickPills">
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Netflix" data-url="https://netflix.com" data-category="Entertainment" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://netflix.com&size=32" width="14" height="14" class="me-1" alt="">Netflix
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Spotify" data-url="https://spotify.com" data-category="Music" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://spotify.com&size=32" width="14" height="14" class="me-1" alt="">Spotify
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="YouTube Premium" data-url="https://youtube.com" data-category="Entertainment" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://youtube.com&size=32" width="14" height="14" class="me-1" alt="">YouTube
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="GitHub Copilot" data-url="https://github.com" data-category="Software & Dev" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://github.com&size=32" width="14" height="14" class="me-1" alt="">GitHub
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="ChatGPT Plus" data-url="https://openai.com" data-category="Productivity" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://openai.com&size=32" width="14" height="14" class="me-1" alt="">ChatGPT
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Claude Pro" data-url="https://anthropic.com" data-category="Productivity" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://anthropic.com&size=32" width="14" height="14" class="me-1" alt="">Claude
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Apple iCloud+" data-url="https://apple.com" data-category="Cloud Storage" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://apple.com&size=32" width="14" height="14" class="me-1" alt="">Apple
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Adobe Creative Cloud" data-url="https://adobe.com" data-category="Software & Dev" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://adobe.com&size=32" width="14" height="14" class="me-1" alt="">Adobe
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Canva Pro" data-url="https://canva.com" data-category="Software & Dev" data-cycle="annual">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://canva.com&size=32" width="14" height="14" class="me-1" alt="">Canva
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Figma Professional" data-url="https://figma.com" data-category="Software & Dev" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://figma.com&size=32" width="14" height="14" class="me-1" alt="">Figma
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Disney+" data-url="https://disneyplus.com" data-category="Entertainment" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://disneyplus.com&size=32" width="14" height="14" class="me-1" alt="">Disney+
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Notion Plus" data-url="https://notion.so" data-category="Productivity" data-cycle="monthly">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://notion.so&size=32" width="14" height="14" class="me-1" alt="">Notion
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Calm" data-url="https://calm.com" data-category="Health & Fitness" data-cycle="annual">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://calm.com&size=32" width="14" height="14" class="me-1" alt="">Calm
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn" data-name="Strava" data-url="https://strava.com" data-category="Health & Fitness" data-cycle="annual">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://strava.com&size=32" width="14" height="14" class="me-1" alt="">Strava
            </button>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Service Website URL (Primary Auto-Detector) --}}
    <div class="col-12 col-md-6">
        <label for="sub_website_url" class="form-label fw-semibold">
            Service Website URL <span class="badge bg-primary bg-opacity-10 text-primary ms-1">Auto-Detects Brand</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-globe"></i></span>
            <input type="text" id="sub_website_url" name="website_url"
                   class="form-control border-start-0 @error('website_url') is-invalid @enderror"
                   value="{{ old('website_url', $subscription->website_url ?? '') }}"
                   placeholder="e.g. calm.com, strava.com, or any domain"
                   autocomplete="off">
            <button type="button" id="btnFetchIcon" class="btn btn-outline-secondary" title="Fetch Brand Icon">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
        <div class="form-text">
            Type any domain to resolve its icon automatically.
            <button type="button" id="btnToggleCustomLogo" class="btn btn-link btn-sm p-0 ms-1 text-decoration-none align-baseline">Custom icon URL</button>
        </div>
        @error('website_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Service Name with Live Brand Icon Preview --}}
    <div class="col-12 col-md-6">
        <label for="sub_name" class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text p-1 bg-light border-end-0" style="width: 44px; display: flex; align-items: center; justify-content: center;">
                <img id="liveLogoPreview"
                     src="{{ old('logo_url', $subscription->logo_url ?? '') ?: 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' fill=\'%2394a3b8\' viewBox=\'0 0 16 16\'><path d=\'M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm0 14A6 6 0 1 1 8 2a6 6 0 0 1 0 12z\'/></svg>' }}"
                     width="26" height="26"
                     class="rounded-1"
                     alt="Icon">
            </span>
            <input type="text" id="sub_name" name="name"
                   class="form-control border-start-0 @error('name') is-invalid @enderror"
                   value="{{ old('name', $subscription->name ?? '') }}"
                   placeholder="e.g. Netflix, Calm, Strava"
                   list="popularServiceList"
                   autocomplete="off" required>
        </div>
        <div class="form-text">Supports Health & Fitness, Software, Streaming, Finance, Education, and custom domains.</div>
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- HTML5 Datalist populated from BrandIconService catalog --}}
    <datalist id="popularServiceList">
        @foreach(($brandCatalog ?? []) as $brandItem)
            <option value="{{ $brandItem['name'] }}">
        @endforeach
    </datalist>

    {{-- ── Dynamic Smart Auto-Correction & Typo Suggestion Box ─────────────── --}}
    <div class="col-12 d-none" id="smartTypoAlert">
        <div class="alert alert-warning py-2 px-3 mb-0 d-flex align-items-center justify-content-between flex-wrap gap-2 border-0 shadow-sm"
             style="background: rgba(245, 158, 11, 0.12); border-left: 4px solid #f59e0b !important; border-radius: 8px;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-lightbulb-fill text-warning fs-5"></i>
                <div>
                    <span class="small text-muted">Did you mean:</span>
                    <strong id="suggestedNameText" class="text-white ms-1">Netflix</strong>
                    <span class="badge bg-secondary bg-opacity-25 text-white-50 ms-1 font-monospace" id="suggestedDomainText">netflix.com</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="btnApplyTypoCorrection" class="btn btn-sm btn-warning text-dark fw-bold px-3 py-1 shadow-sm d-flex align-items-center gap-1">
                    <i class="bi bi-check-circle-fill"></i> Auto-Correct & Sync Logo
                </button>
                <button type="button" id="btnDismissTypo" class="btn btn-sm btn-link text-muted p-0 ms-1 text-decoration-none" title="Dismiss">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Match Confirmed Notification Box ──────────────────────────────── --}}
    <div class="col-12 d-none" id="smartDetectedAlert">
        <div class="p-2 px-3 rounded-2 small text-success d-flex align-items-center gap-2 border"
             style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.3) !important;">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span>Recognized <strong id="detectedNameText" class="text-success">Netflix</strong>. Official brand logo, clean URL, and category automatically synced.</span>
        </div>
    </div>

    {{-- Amount --}}
    <div class="col-12 col-md-4">
        <label for="sub_amount" class="form-label fw-semibold">Billing Amount <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted" id="amountCurrencySymbol">$</span>
            <input type="number" id="sub_amount" step="0.01" min="0" name="amount"
                   class="form-control @error('amount') is-invalid @enderror"
                   value="{{ old('amount', $subscription->amount ?? '') }}" placeholder="9.99" required>
        </div>
        @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Currency --}}
    <div class="col-12 col-md-4">
        <label for="sub_currency" class="form-label fw-semibold">Original Currency <span class="text-danger">*</span></label>
        <select id="sub_currency" name="currency" class="form-select @error('currency') is-invalid @enderror" required>
            @foreach($currencies as $code => $label)
                <option value="{{ $code }}" {{ old('currency', $subscription->currency ?? 'USD') === $code ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Will be auto-converted to your base tracking currency.</div>
        @error('currency')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Billing Cycle --}}
    <div class="col-12 col-md-4">
        <label for="sub_billing_cycle" class="form-label fw-semibold">Billing Cycle <span class="text-danger">*</span></label>
        <select id="sub_billing_cycle" name="billing_cycle" class="form-select @error('billing_cycle') is-invalid @enderror" required>
            @foreach(['weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'semi_annual' => 'Semi-Annual', 'annual' => 'Annual'] as $val => $label)
                <option value="{{ $val }}" {{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('billing_cycle')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Status --}}
    <div class="col-12 col-md-4">
        <label for="sub_status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
        <select id="sub_status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'cancelled' => 'Cancelled'] as $val => $label)
                <option value="{{ $val }}" {{ old('status', $subscription->status ?? 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Category --}}
    <div class="col-12 col-md-4">
        <label for="sub_category_id" class="form-label fw-semibold">Category</label>
        <select id="sub_category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">— Select Category —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" data-name="{{ $cat->name }}"
                    {{ old('category_id', $subscription->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Start Date --}}
    <div class="col-12 col-md-4">
        <label for="sub_start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
        <input type="date" id="sub_start_date" name="start_date"
               class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', isset($subscription) ? $subscription->start_date->format('Y-m-d') : date('Y-m-d')) }}" required>
        @error('start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Next Billing Date --}}
    <div class="col-12 col-md-4">
        <div class="d-flex justify-content-between align-items-center">
            <label for="sub_next_billing_date" class="form-label fw-semibold mb-1">Next Billing Date <span class="text-danger">*</span></label>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="btnPlusMonth" style="font-size:0.75rem;">+1 Month</button>
        </div>
        <input type="date" id="sub_next_billing_date" name="next_billing_date"
               class="form-control @error('next_billing_date') is-invalid @enderror"
               value="{{ old('next_billing_date', isset($subscription) ? $subscription->next_billing_date->format('Y-m-d') : date('Y-m-d', strtotime('+1 month'))) }}" required>
        @error('next_billing_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Description --}}
    <div class="col-12">
        <label for="sub_description" class="form-label fw-semibold">Description / Plan Details</label>
        <textarea id="sub_description" name="description" rows="2" class="form-control @error('description') is-invalid @enderror"
                  placeholder="e.g. 4K UHD 4 Screens plan">{{ old('description', $subscription->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Optional Custom Logo URL Override --}}
    <div class="col-12 d-none" id="customLogoWrapper">
        <label for="sub_logo_url" class="form-label fw-semibold">Custom Brand Icon URL <span class="text-muted fw-normal small">(Optional)</span></label>
        <input type="url" id="sub_logo_url" name="logo_url"
               class="form-control @error('logo_url') is-invalid @enderror"
               value="{{ old('logo_url', $subscription->logo_url ?? '') }}"
               placeholder="https://example.com/logo.png">
        <div class="form-text">Leave auto-detected or paste a direct HTTPS image URL if a niche provider has no public favicon.</div>
        @error('logo_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Notes --}}
    <div class="col-12">
        <label for="sub_notes" class="form-label fw-semibold">Private Notes</label>
        <input type="text" id="sub_notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
               value="{{ old('notes', $subscription->notes ?? '') }}" placeholder="e.g. Split with roommate or trial ends Friday">
        @error('notes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('sub_website_url');
    const nameInput = document.getElementById('sub_name');
    const logoInput = document.getElementById('sub_logo_url');
    const previewImg = document.getElementById('liveLogoPreview');
    const categorySelect = document.getElementById('sub_category_id');
    const cycleSelect = document.getElementById('sub_billing_cycle');
    const startDateInput = document.getElementById('sub_start_date');
    const nextDateInput = document.getElementById('sub_next_billing_date');
    const btnPlusMonth = document.getElementById('btnPlusMonth');
    const btnFetchIcon = document.getElementById('btnFetchIcon');
    const btnToggleCustomLogo = document.getElementById('btnToggleCustomLogo');
    const customLogoWrapper = document.getElementById('customLogoWrapper');

    // Typo notification elements
    const typoAlert = document.getElementById('smartTypoAlert');
    const suggestedNameText = document.getElementById('suggestedNameText');
    const suggestedDomainText = document.getElementById('suggestedDomainText');
    const btnApplyTypo = document.getElementById('btnApplyTypoCorrection');
    const btnDismissTypo = document.getElementById('btnDismissTypo');
    const detectedAlert = document.getElementById('smartDetectedAlert');
    const detectedNameText = document.getElementById('detectedNameText');

    const serviceDatabase = @json($brandCatalog ?? []);
    const resolveIconEndpoint = @json(route('subscriptions.resolve_icon'));
    const defaultPlaceholderSvg = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%2394a3b8' viewBox='0 0 16 16'><path d='M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm0 14A6 6 0 1 1 8 2a6 6 0 0 1 0 12z'/></svg>";

    let currentPendingCorrection = null;
    let typoDismissedFor = '';
    let activeFetchController = null;
    let previewFallbackQueue = [];
    let userEditedLogoManually = false;

    function levenshtein(a, b) {
        if (!a || !b) return (a || '').length + (b || '').length;
        if (a === b) return 0;
        const matrix = [];
        for (let i = 0; i <= b.length; i++) matrix[i] = [i];
        for (let j = 0; j <= a.length; j++) matrix[0][j] = j;

        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                matrix[i][j] = b.charAt(i - 1) === a.charAt(j - 1)
                    ? matrix[i - 1][j - 1]
                    : Math.min(matrix[i - 1][j - 1] + 1, matrix[i][j - 1] + 1, matrix[i - 1][j] + 1);
            }
        }
        return matrix[b.length][a.length];
    }

    function normalizeString(text) {
        if (!text) return '';
        return text.toLowerCase()
            .replace(/^https?:\/\//i, '')
            .replace(/^www\./i, '')
            .split('/')[0]
            .replace(/\.(com|org|net|io|so|app|us|tv|me|ai|co|health|fit|uk|my|sg|au|edu|gov)(\.[a-z]{2})?$/i, '')
            .replace(/[^a-z0-9]/g, '');
    }

    function extractCleanDomain(input) {
        if (!input) return '';
        let clean = input.trim().toLowerCase();
        clean = clean.replace(/^https?:\/\//i, '');
        clean = clean.split(/[/?#]/)[0];
        clean = clean.replace(/^www\./i, '');
        return clean;
    }

    function isValidDomain(domain) {
        return /^[a-z0-9.-]+\.[a-z]{2,}$/i.test(domain);
    }

    function getGoogleFaviconUrl(domain) {
        return 'https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=' + encodeURIComponent('https://' + domain) + '&size=128';
    }

    function getDuckDuckGoUrl(domain) {
        return 'https://icons.duckduckgo.com/ip3/' + encodeURIComponent(domain) + '.ico';
    }

    function getIconHorseUrl(domain) {
        return 'https://icon.horse/icon/' + encodeURIComponent(domain);
    }

    function setPreviewWithFallbacks(primaryUrl, domain) {
        if (!primaryUrl) return;
        const candidates = [
            primaryUrl,
            domain ? getGoogleFaviconUrl(domain) : null,
            domain ? getDuckDuckGoUrl(domain) : null,
            domain ? getIconHorseUrl(domain) : null,
        ].filter(Boolean);

        const unique = [...new Set(candidates)];
        const initial = unique.shift();
        previewFallbackQueue = unique;

        if (!userEditedLogoManually) {
            logoInput.value = initial;
        }
        previewImg.src = initial;
    }

    function applyCategoryAndCycle(service) {
        if (categorySelect && service.category) {
            for (let i = 0; i < categorySelect.options.length; i++) {
                const opt = categorySelect.options[i];
                if (opt.getAttribute('data-name') === service.category || opt.text.trim() === service.category) {
                    categorySelect.selectedIndex = i;
                    break;
                }
            }
        }
        if (cycleSelect && service.cycle) {
            cycleSelect.value = service.cycle;
        }
    }

    function applyService(service, overwriteUrl = true) {
        currentPendingCorrection = null;
        typoAlert.classList.add('d-none');

        nameInput.value = service.name;
        if (overwriteUrl) {
            urlInput.value = 'https://' + service.domain;
        }
        userEditedLogoManually = false;
        setPreviewWithFallbacks(getGoogleFaviconUrl(service.domain), service.domain);
        applyCategoryAndCycle(service);

        if (detectedAlert && detectedNameText) {
            detectedNameText.textContent = service.name;
            detectedAlert.classList.remove('d-none');
            setTimeout(() => {
                detectedAlert.classList.add('d-none');
            }, 4000);
        }
    }

    function findExactServiceByDomain(domain) {
        const clean = extractCleanDomain(domain);
        return serviceDatabase.find((s) => s.domain === clean || clean.endsWith('.' + s.domain)) || null;
    }

    function checkFuzzyMatch(rawInput, isDomainInput = false) {
        const norm = normalizeString(rawInput);
        if (!norm || norm.length < 3) {
            typoAlert.classList.add('d-none');
            return null;
        }

        if (typoDismissedFor && typoDismissedFor === norm) {
            return null;
        }

        for (const s of serviceDatabase) {
            const sNorm = normalizeString(s.name);
            const dNorm = normalizeString(s.domain);
            if (norm === sNorm || norm === dNorm) {
                typoAlert.classList.add('d-none');
                return { type: 'exact', service: s };
            }
            if (s.aliases && s.aliases.includes(norm)) {
                return { type: 'typo', service: s, distance: 1 };
            }
        }

        // Skip fuzzy typo guessing when user enters a valid non-catalog commercial domain (e.g. noom.com, calm.com)
        if (isDomainInput) {
            typoAlert.classList.add('d-none');
            return null;
        }

        let bestMatch = null;
        let minDistance = 999;

        for (const s of serviceDatabase) {
            const dNorm = normalizeString(s.domain);
            if (dNorm.length < 4) continue;

            const candidates = [
                normalizeString(s.name),
                dNorm,
                ...(s.aliases || []).map(a => normalizeString(a)),
            ];

            for (const cand of candidates) {
                if (!cand || cand.length < 4) continue;
                const dist = levenshtein(norm, cand);
                if (dist < minDistance) {
                    minDistance = dist;
                    bestMatch = s;
                }
            }
        }

        const threshold = norm.length <= 4 ? 1 : 2;
        if (bestMatch && minDistance > 0 && minDistance <= threshold) {
            return { type: 'typo', service: bestMatch, distance: minDistance };
        }

        typoAlert.classList.add('d-none');
        return null;
    }

    function fetchResolvedIconForDomain(domain) {
        if (!isValidDomain(domain)) return;

        if (activeFetchController) {
            activeFetchController.abort();
        }
        activeFetchController = new AbortController();

        fetch(resolveIconEndpoint + '?domain=' + encodeURIComponent(domain), {
            headers: { 'Accept': 'application/json' },
            signal: activeFetchController.signal,
        })
            .then((res) => res.ok ? res.json() : null)
            .then((data) => {
                if (!data || !data.logo_url) return;
                setPreviewWithFallbacks(data.logo_url, data.domain || domain);

                if (data.matched_brand) {
                    if (!nameInput.value.trim()) {
                        nameInput.value = data.matched_brand.name;
                    }
                    applyCategoryAndCycle(data.matched_brand);
                }
            })
            .catch((err) => {
                if (err.name !== 'AbortError') {
                    // Client fallback queue already handles offline failures
                }
            });
    }

    function autoDetectFromUrl(inputVal, normalizeUrlInput = false) {
        const domain = extractCleanDomain(inputVal);
        if (!domain) return;

        if (normalizeUrlInput && isValidDomain(domain)) {
            urlInput.value = 'https://' + domain;
        }

        const exactCatalogBrand = findExactServiceByDomain(domain);
        if (exactCatalogBrand) {
            applyService(exactCatalogBrand, normalizeUrlInput);
            return;
        }

        if (!isValidDomain(domain)) {
            const match = checkFuzzyMatch(domain, false);
            if (match && match.type === 'exact') {
                applyService(match.service, normalizeUrlInput);
            } else if (match && match.type === 'typo') {
                showTypoSuggestion(match.service);
            }
            return;
        }

        typoAlert.classList.add('d-none');
        userEditedLogoManually = false;
        setPreviewWithFallbacks(getGoogleFaviconUrl(domain), domain);
        fetchResolvedIconForDomain(domain);

        if (!nameInput.value.trim() && normalizeUrlInput) {
            const namePart = domain.split('.')[0];
            nameInput.value = namePart.charAt(0).toUpperCase() + namePart.slice(1);
        }
    }

    function showTypoSuggestion(service) {
        currentPendingCorrection = service;
        suggestedNameText.textContent = service.name;
        suggestedDomainText.textContent = service.domain;
        typoAlert.classList.remove('d-none');
    }

    // Event handler for typing into Service Name
    let debounceTimer = null;
    nameInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const val = nameInput.value.trim();
            if (!val) {
                typoAlert.classList.add('d-none');
                return;
            }

            if (val.includes('.') && !val.includes(' ')) {
                autoDetectFromUrl(val, false);
                return;
            }

            const match = checkFuzzyMatch(val, false);
            if (match && match.type === 'exact') {
                applyService(match.service, !urlInput.value.trim());
            } else if (match && match.type === 'typo') {
                showTypoSuggestion(match.service);
            } else {
                typoAlert.classList.add('d-none');
            }
        }, 200);
    });

    // Event handler for typing into Website URL (never mutates cursor mid-typing)
    urlInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const val = urlInput.value.trim();
            if (!val) {
                typoAlert.classList.add('d-none');
                return;
            }

            autoDetectFromUrl(val, false);
        }, 250);
    });

    urlInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            autoDetectFromUrl(this.value, true);
        }
    });

    if (btnFetchIcon) {
        btnFetchIcon.addEventListener('click', function() {
            if (urlInput.value.trim()) {
                userEditedLogoManually = false;
                autoDetectFromUrl(urlInput.value, true);
            }
        });
    }

    if (btnToggleCustomLogo && customLogoWrapper) {
        btnToggleCustomLogo.addEventListener('click', function() {
            customLogoWrapper.classList.toggle('d-none');
            if (!customLogoWrapper.classList.contains('d-none')) {
                logoInput.focus();
            }
        });
    }

    logoInput.addEventListener('input', function() {
        const customUrl = this.value.trim();
        userEditedLogoManually = customUrl.length > 0;
        previewFallbackQueue = [];
        previewImg.src = customUrl || defaultPlaceholderSvg;
    });

    // Apply typo auto-correction button click
    btnApplyTypo.addEventListener('click', function() {
        if (currentPendingCorrection) {
            applyService(currentPendingCorrection, true);
        }
    });

    // Dismiss typo suggestion
    btnDismissTypo.addEventListener('click', function() {
        const val = nameInput.value.trim() || urlInput.value.trim();
        typoDismissedFor = normalizeString(val);
        typoAlert.classList.add('d-none');
    });

    // One-click popular service pills
    document.querySelectorAll('.quick-brand-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            applyService({
                name: this.getAttribute('data-name'),
                domain: extractCleanDomain(this.getAttribute('data-url')),
                category: this.getAttribute('data-category'),
                cycle: this.getAttribute('data-cycle'),
            }, true);
        });
    });

    // Multi-provider live logo preview fallback queue
    if (previewImg) {
        previewImg.addEventListener('error', function() {
            if (previewFallbackQueue.length > 0) {
                const nextUrl = previewFallbackQueue.shift();
                if (!userEditedLogoManually) {
                    logoInput.value = nextUrl;
                }
                this.src = nextUrl;
                return;
            }
            this.src = defaultPlaceholderSvg;
        });
    }

    // Quick +1 Month helper
    if (btnPlusMonth && startDateInput && nextDateInput) {
        btnPlusMonth.addEventListener('click', function() {
            const base = startDateInput.value ? new Date(startDateInput.value) : new Date();
            base.setMonth(base.getMonth() + 1);
            const y = base.getFullYear();
            const m = String(base.getMonth() + 1).padStart(2, '0');
            const d = String(base.getDate()).padStart(2, '0');
            nextDateInput.value = `${y}-${m}-${d}`;
        });
    }
});
</script>
@endpush
