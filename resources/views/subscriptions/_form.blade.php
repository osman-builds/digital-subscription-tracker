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
                   placeholder="e.g. netflix.com or https://spotify.com"
                   autocomplete="off">
        </div>
        <div class="form-text">Paste or type any service website URL to automatically fetch its official logo and details.</div>
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
                   placeholder="e.g. Netflix, Spotify"
                   list="popularServiceList"
                   autocomplete="off" required>
        </div>
        <div class="form-text">Smart typing assists with brand names and catches typos instantly.</div>
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- HTML5 Datalist for instant browser autocompletion --}}
    <datalist id="popularServiceList">
        <option value="Netflix">
        <option value="Spotify">
        <option value="YouTube Premium">
        <option value="GitHub Copilot">
        <option value="ChatGPT Plus">
        <option value="Claude Pro">
        <option value="Apple iCloud+">
        <option value="Adobe Creative Cloud">
        <option value="Canva Pro">
        <option value="Figma Professional">
        <option value="Notion Plus">
        <option value="Disney+">
        <option value="Amazon Prime">
        <option value="Google One">
        <option value="Microsoft 365">
        <option value="Discord Nitro">
        <option value="Duolingo Super">
        <option value="Midjourney">
        <option value="Slack Pro">
        <option value="Zoom One">
        <option value="Dropbox Plus">
        <option value="LinkedIn Premium">
        <option value="X / Twitter Premium">
        <option value="HBO Max">
        <option value="Hulu">
        <option value="Crunchyroll">
        <option value="Twitch Sub">
        <option value="1Password">
        <option value="Bitwarden">
        <option value="NordVPN">
        <option value="ExpressVPN">
        <option value="Surfshark">
        <option value="Proton Mail">
        <option value="Vercel Pro">
        <option value="Supabase Pro">
        <option value="DigitalOcean">
        <option value="AWS">
        <option value="Linear">
        <option value="Grammarly Premium">
        <option value="Coursera Plus">
        <option value="Udemy">
        <option value="Audible">
        <option value="Medium Membership">
        <option value="Substack">
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

    {{-- Hidden / Advanced Logo URL --}}
    <input type="hidden" id="sub_logo_url" name="logo_url" value="{{ old('logo_url', $subscription->logo_url ?? '') }}">

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

    // Typo notification elements
    const typoAlert = document.getElementById('smartTypoAlert');
    const suggestedNameText = document.getElementById('suggestedNameText');
    const suggestedDomainText = document.getElementById('suggestedDomainText');
    const btnApplyTypo = document.getElementById('btnApplyTypoCorrection');
    const btnDismissTypo = document.getElementById('btnDismissTypo');
    const detectedAlert = document.getElementById('smartDetectedAlert');
    const detectedNameText = document.getElementById('detectedNameText');

    let currentPendingCorrection = null;
    let typoDismissedFor = '';

    // Comprehensive knowledge base of 60+ global services with common typos/aliases
    const serviceDatabase = [
        { name: 'Netflix', domain: 'netflix.com', category: 'Entertainment', cycle: 'monthly', aliases: ['netfix', 'netflx', 'neflix', 'netfilx', 'netflixx', 'netfllix'] },
        { name: 'Spotify', domain: 'spotify.com', category: 'Music', cycle: 'monthly', aliases: ['spotfy', 'spotifi', 'spotifay', 'spotfiy', 'spotifyy'] },
        { name: 'YouTube Premium', domain: 'youtube.com', category: 'Entertainment', cycle: 'monthly', aliases: ['yutube', 'youtub', 'youtubepremium', 'yt', 'ytpremium', 'youtbe'] },
        { name: 'GitHub Copilot', domain: 'github.com', category: 'Software & Dev', cycle: 'monthly', aliases: ['githb', 'gitub', 'githubcopilot', 'copilot', 'ghcopilot', 'git hub'] },
        { name: 'ChatGPT Plus', domain: 'openai.com', category: 'Productivity', cycle: 'monthly', aliases: ['chatgtp', 'chagpt', 'chatgbt', 'chatgpt', 'openai', 'opena', 'open-ai', 'gpt4'] },
        { name: 'Claude Pro', domain: 'anthropic.com', category: 'Productivity', cycle: 'monthly', aliases: ['claude', 'claud', 'anthropic', 'claudeai', 'claudepro'] },
        { name: 'Apple iCloud+', domain: 'apple.com', category: 'Cloud Storage', cycle: 'monthly', aliases: ['icloud', 'icloude', 'aple', 'appletv', 'applemusic', 'appleone'] },
        { name: 'Adobe Creative Cloud', domain: 'adobe.com', category: 'Software & Dev', cycle: 'monthly', aliases: ['adob', 'photoshop', 'illustrator', 'creativecloud', 'lightroom'] },
        { name: 'Canva Pro', domain: 'canva.com', category: 'Software & Dev', cycle: 'annual', aliases: ['canav', 'canvapro', 'camva'] },
        { name: 'Figma Professional', domain: 'figma.com', category: 'Software & Dev', cycle: 'monthly', aliases: ['figm', 'figmaa', 'figmapro'] },
        { name: 'Notion Plus', domain: 'notion.so', category: 'Productivity', cycle: 'monthly', aliases: ['noton', 'notionapp', 'notionai'] },
        { name: 'Disney+', domain: 'disneyplus.com', category: 'Entertainment', cycle: 'monthly', aliases: ['disny', 'disney', 'disneyplus', 'disnep'] },
        { name: 'Amazon Prime', domain: 'amazon.com', category: 'Entertainment', cycle: 'annual', aliases: ['amzon', 'prime', 'amazonprime', 'primevideo', 'amazn'] },
        { name: 'Google One', domain: 'google.com', category: 'Cloud Storage', cycle: 'monthly', aliases: ['googl', 'googleone', 'gsuite', 'googleworkspace', 'gdrive'] },
        { name: 'Microsoft 365', domain: 'microsoft.com', category: 'Productivity', cycle: 'annual', aliases: ['microsft', 'office365', 'm365', 'msft', 'office'] },
        { name: 'Discord Nitro', domain: 'discord.com', category: 'Gaming', cycle: 'monthly', aliases: ['discrod', 'dicord', 'discordnitro', 'nitro'] },
        { name: 'Duolingo Super', domain: 'duolingo.com', category: 'Health & Fitness', cycle: 'annual', aliases: ['duo', 'doulingo', 'duolingosuper'] },
        { name: 'Midjourney', domain: 'midjourney.com', category: 'Software & Dev', cycle: 'monthly', aliases: ['midjourny', 'mid journy', 'mid-journey'] },
        { name: 'Slack Pro', domain: 'slack.com', category: 'Productivity', cycle: 'monthly', aliases: ['slak', 'slackpro'] },
        { name: 'Zoom One', domain: 'zoom.us', category: 'Productivity', cycle: 'monthly', aliases: ['zom', 'zoom'] },
        { name: 'Dropbox Plus', domain: 'dropbox.com', category: 'Cloud Storage', cycle: 'monthly', aliases: ['dropbx', 'dropox'] },
        { name: 'LinkedIn Premium', domain: 'linkedin.com', category: 'Productivity', cycle: 'monthly', aliases: ['linkdin', 'linkind', 'linkedinpremium'] },
        { name: 'X / Twitter Premium', domain: 'x.com', category: 'Entertainment', cycle: 'monthly', aliases: ['twitter', 'twiter', 'xpremium', 'twitterblue'] },
        { name: 'HBO Max', domain: 'max.com', category: 'Entertainment', cycle: 'monthly', aliases: ['hbomax', 'hbo', 'max'] },
        { name: 'Hulu', domain: 'hulu.com', category: 'Entertainment', cycle: 'monthly', aliases: ['hlu', 'hullu'] },
        { name: 'Crunchyroll', domain: 'crunchyroll.com', category: 'Entertainment', cycle: 'monthly', aliases: ['crunchy', 'crunchroll', 'crunchyrool'] },
        { name: 'Twitch Sub', domain: 'twitch.tv', category: 'Gaming', cycle: 'monthly', aliases: ['twich', 'twitch'] },
        { name: '1Password', domain: '1password.com', category: 'Software & Dev', cycle: 'annual', aliases: ['onepassword', '1pass', 'onepass'] },
        { name: 'Bitwarden', domain: 'bitwarden.com', category: 'Software & Dev', cycle: 'annual', aliases: ['bitwardn', 'bitwarder'] },
        { name: 'NordVPN', domain: 'nordvpn.com', category: 'Software & Dev', cycle: 'annual', aliases: ['nord', 'nord-vpn'] },
        { name: 'ExpressVPN', domain: 'expressvpn.com', category: 'Software & Dev', cycle: 'annual', aliases: ['express', 'express-vpn'] },
        { name: 'Surfshark', domain: 'surfshark.com', category: 'Software & Dev', cycle: 'annual', aliases: ['surf shark'] },
        { name: 'Proton Mail', domain: 'proton.me', category: 'Productivity', cycle: 'annual', aliases: ['proton', 'protonmail'] },
        { name: 'Vercel Pro', domain: 'vercel.com', category: 'Software & Dev', cycle: 'monthly', aliases: ['versel', 'vercel'] },
        { name: 'Supabase Pro', domain: 'supabase.com', category: 'Software & Dev', cycle: 'monthly', aliases: ['superbase', 'supa base'] },
        { name: 'DigitalOcean', domain: 'digitalocean.com', category: 'Software & Dev', cycle: 'monthly', aliases: ['digital ocean', 'docean'] },
        { name: 'AWS', domain: 'aws.amazon.com', category: 'Software & Dev', cycle: 'monthly', aliases: ['amazonwebservices', 'aws'] },
        { name: 'Linear', domain: 'linear.app', category: 'Productivity', cycle: 'monthly', aliases: ['linar', 'linearapp'] },
        { name: 'Grammarly Premium', domain: 'grammarly.com', category: 'Productivity', cycle: 'annual', aliases: ['grammerly', 'gramarly', 'gramerly'] },
        { name: 'Coursera Plus', domain: 'coursera.org', category: 'News & Reading', cycle: 'annual', aliases: ['corsera', 'coursera'] },
        { name: 'Audible', domain: 'audible.com', category: 'Entertainment', cycle: 'monthly', aliases: ['audibl', 'audibleplus'] },
        { name: 'Medium Membership', domain: 'medium.com', category: 'News & Reading', cycle: 'monthly', aliases: ['medum', 'medium'] },
        { name: 'Substack', domain: 'substack.com', category: 'News & Reading', cycle: 'monthly', aliases: ['substak', 'substack'] }
    ];

    // Levenshtein distance algorithm for fuzzy typo detection
    function levenshtein(a, b) {
        if (!a || !b) return (a || '').length + (b || '').length;
        if (a === b) return 0;
        const matrix = [];
        for (let i = 0; i <= b.length; i++) matrix[i] = [i];
        for (let j = 0; j <= a.length; j++) matrix[0][j] = j;

        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                if (b.charAt(i - 1) === a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1, // substitution
                        matrix[i][j - 1] + 1,     // insertion
                        matrix[i - 1][j] + 1      // deletion
                    );
                }
            }
        }
        return matrix[b.length][a.length];
    }

    // Clean and normalize text / domain
    function normalizeString(text) {
        if (!text) return '';
        return text.toLowerCase()
            .replace(/^https?:\/\//i, '')
            .replace(/^www\./i, '')
            .split('/')[0]
            .replace(/\.(com|org|net|io|so|app|us|tv|me|ai|co)$/i, '')
            .replace(/[^a-z0-9]/g, '');
    }

    function extractCleanDomain(input) {
        if (!input) return '';
        let clean = input.trim().toLowerCase();
        clean = clean.replace(/^https?:\/\//i, '');
        clean = clean.split('/')[0];
        clean = clean.replace(/^www\./i, '');
        return clean;
    }

    function getFaviconUrl(domain) {
        return 'https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=' + encodeURIComponent('https://' + domain) + '&size=128';
    }

    // Apply service details to the form
    function applyService(service, isManualCorrection = false) {
        currentPendingCorrection = null;
        typoAlert.classList.add('d-none');

        nameInput.value = service.name;
        urlInput.value = 'https://' + service.domain;
        const logoUrl = getFaviconUrl(service.domain);
        logoInput.value = logoUrl;
        previewImg.src = logoUrl;

        // Auto-select category
        if (categorySelect && service.category) {
            for (let i = 0; i < categorySelect.options.length; i++) {
                const opt = categorySelect.options[i];
                if (opt.getAttribute('data-name') === service.category || opt.text.trim() === service.category) {
                    categorySelect.selectedIndex = i;
                    break;
                }
            }
        }

        // Auto-select billing cycle
        if (cycleSelect && service.cycle) {
            cycleSelect.value = service.cycle;
        }

        // Surface positive match confirmation
        if (detectedAlert && detectedNameText) {
            detectedNameText.textContent = service.name;
            detectedAlert.classList.remove('d-none');
            setTimeout(() => {
                detectedAlert.classList.add('d-none');
            }, 4000);
        }
    }

    // Fuzzy spell check & typo detector
    function checkFuzzyMatch(rawInput) {
        const norm = normalizeString(rawInput);
        if (!norm || norm.length < 3) {
            typoAlert.classList.add('d-none');
            return null;
        }

        if (typoDismissedFor && typoDismissedFor === norm) {
            return null;
        }

        // 1. Check exact matches first
        for (const s of serviceDatabase) {
            const sNorm = normalizeString(s.name);
            const dNorm = normalizeString(s.domain);
            if (norm === sNorm || norm === dNorm) {
                // Exact match!
                typoAlert.classList.add('d-none');
                return { type: 'exact', service: s };
            }
            if (s.aliases && s.aliases.includes(norm)) {
                // Direct alias match (e.g. netfix -> Netflix)
                return { type: 'typo', service: s, distance: 1 };
            }
        }

        // 2. Check Levenshtein distance against brand names, domains, and aliases
        let bestMatch = null;
        let minDistance = 999;

        for (const s of serviceDatabase) {
            const candidates = [
                normalizeString(s.name),
                normalizeString(s.domain),
                ...(s.aliases || []).map(a => normalizeString(a))
            ];

            for (const cand of candidates) {
                if (!cand) continue;
                const dist = levenshtein(norm, cand);
                if (dist < minDistance) {
                    minDistance = dist;
                    bestMatch = s;
                }
            }
        }

        // Thresholds: string length 3 -> dist 1; length >= 4 -> dist <= 2
        const threshold = norm.length <= 3 ? 1 : 2;
        if (bestMatch && minDistance > 0 && minDistance <= threshold) {
            return { type: 'typo', service: bestMatch, distance: minDistance };
        }

        typoAlert.classList.add('d-none');
        return null;
    }

    // Auto-detect when URL or Domain is entered
    function autoDetectFromUrl(inputVal) {
        const domain = extractCleanDomain(inputVal);
        if (!domain) return;

        // Check if known or fuzzy match
        const match = checkFuzzyMatch(domain);
        if (match && match.type === 'exact') {
            applyService(match.service);
            return;
        } else if (match && match.type === 'typo') {
            showTypoSuggestion(match.service);
            return;
        }

        // Generic custom domain (e.g. customsaas.io)
        const fullUrl = 'https://' + domain;
        const logoUrl = getFaviconUrl(domain);
        urlInput.value = fullUrl;
        logoInput.value = logoUrl;
        previewImg.src = logoUrl;

        if (!nameInput.value || nameInput.value.trim().length <= 3) {
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

            // If user typed a URL into name box
            if (val.includes('.') && !val.includes(' ')) {
                autoDetectFromUrl(val);
                return;
            }

            const match = checkFuzzyMatch(val);
            if (match && match.type === 'exact') {
                applyService(match.service);
            } else if (match && match.type === 'typo') {
                showTypoSuggestion(match.service);
            } else {
                typoAlert.classList.add('d-none');
            }
        }, 150);
    });

    // Event handler for typing into Website URL
    urlInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const val = urlInput.value.trim();
            if (!val) {
                typoAlert.classList.add('d-none');
                return;
            }

            const match = checkFuzzyMatch(val);
            if (match && match.type === 'exact') {
                applyService(match.service);
            } else if (match && match.type === 'typo') {
                showTypoSuggestion(match.service);
            } else if (val.includes('.')) {
                autoDetectFromUrl(val);
            }
        }, 150);
    });

    urlInput.addEventListener('blur', function() {
        if (this.value) autoDetectFromUrl(this.value);
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
            const name = this.getAttribute('data-name');
            const url = this.getAttribute('data-url');
            const cat = this.getAttribute('data-category');
            const cycle = this.getAttribute('data-cycle');
            const domain = extractCleanDomain(url);

            applyService({
                name: name,
                domain: domain,
                category: cat,
                cycle: cycle
            });
        });
    });

    // Live logo preview error fallback
    if (liveLogoPreview) {
        liveLogoPreview.addEventListener('error', function() {
            this.src = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%2394a3b8' viewBox='0 0 16 16'><path d='M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm0 14A6 6 0 1 1 8 2a6 6 0 0 1 0 12z'/></svg>";
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
