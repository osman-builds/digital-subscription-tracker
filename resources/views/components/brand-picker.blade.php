@php
    $quickBrands = [
        ['Netflix', 'Netflix', 'https://netflix.com', 'Entertainment', 'monthly'],
        ['Spotify', 'Spotify', 'https://spotify.com', 'Music', 'monthly'],
        ['YouTube', 'YouTube Premium', 'https://youtube.com', 'Entertainment', 'monthly'],
        ['GitHub', 'GitHub Copilot', 'https://github.com', 'Software & Dev', 'monthly'],
        ['ChatGPT', 'ChatGPT Plus', 'https://openai.com', 'Productivity', 'monthly'],
        ['Claude', 'Claude Pro', 'https://anthropic.com', 'Productivity', 'monthly'],
        ['Apple', 'Apple iCloud+', 'https://apple.com', 'Cloud Storage', 'monthly'],
        ['Adobe', 'Adobe Creative Cloud', 'https://adobe.com', 'Software & Dev', 'monthly'],
        ['Canva', 'Canva Pro', 'https://canva.com', 'Software & Dev', 'annual'],
        ['Figma', 'Figma Professional', 'https://figma.com', 'Software & Dev', 'monthly'],
        ['Disney+', 'Disney+', 'https://disneyplus.com', 'Entertainment', 'monthly'],
        ['Notion', 'Notion Plus', 'https://notion.so', 'Productivity', 'monthly'],
        ['Calm', 'Calm', 'https://calm.com', 'Health & Fitness', 'annual'],
        ['Strava', 'Strava', 'https://strava.com', 'Health & Fitness', 'annual'],
    ];
@endphp

<div class="card border-0 mb-4" style="background: rgba(99, 102, 241, 0.05); border: 1px dashed rgba(99, 102, 241, 0.3) !important; border-radius: 14px;">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
            <span class="small fw-bold text-primary">
                <i class="bi bi-magic me-1"></i>Quick Brand Auto-Fill
            </span>
            <small class="text-muted">Pick a popular service or type below — spelling typos are automatically detected and corrected</small>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-1" id="quickPills">
            @foreach($quickBrands as [$label, $name, $url, $category, $cycle])
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 border-opacity-25 quick-brand-btn"
                        data-name="{{ $name }}" data-url="{{ $url }}" data-category="{{ $category }}" data-cycle="{{ $cycle }}">
                    <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url={{ $url }}&size=32" width="14" height="14" class="me-1" alt="">{{ $label }}
                </button>
            @endforeach
        </div>
    </div>
</div>
