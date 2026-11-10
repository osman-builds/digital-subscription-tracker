@extends('settings.layout')

@section('settings_content')
<div class="d-flex flex-column gap-4">

    {{-- 1. PREFERENCES & DISPLAY --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-sliders2 fs-5"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0">Preferences &amp; Display</h5>
                    <small class="text-muted">Tracking currency, appearance, and renewal notifications</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('settings.preferences') }}" method="POST">
                @csrf @method('PATCH')

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label for="base_currency" class="form-label fw-semibold">Base Tracking Currency</label>
                        <select id="base_currency" name="base_currency" class="form-select form-select-lg fs-6">
                            <optgroup label="Popular Global Currencies">
                                <option value="USD" {{ $settings->base_currency === 'USD' ? 'selected' : '' }}>USD – US Dollar ($)</option>
                                <option value="EUR" {{ $settings->base_currency === 'EUR' ? 'selected' : '' }}>EUR – Euro (€)</option>
                                <option value="GBP" {{ $settings->base_currency === 'GBP' ? 'selected' : '' }}>GBP – British Pound (£)</option>
                                <option value="MYR" {{ $settings->base_currency === 'MYR' ? 'selected' : '' }}>MYR – Malaysian Ringgit (RM)</option>
                                <option value="SGD" {{ $settings->base_currency === 'SGD' ? 'selected' : '' }}>SGD – Singapore Dollar (S$)</option>
                                <option value="AUD" {{ $settings->base_currency === 'AUD' ? 'selected' : '' }}>AUD – Australian Dollar (A$)</option>
                                <option value="CAD" {{ $settings->base_currency === 'CAD' ? 'selected' : '' }}>CAD – Canadian Dollar (C$)</option>
                                <option value="JPY" {{ $settings->base_currency === 'JPY' ? 'selected' : '' }}>JPY – Japanese Yen (¥)</option>
                                <option value="CNY" {{ $settings->base_currency === 'CNY' ? 'selected' : '' }}>CNY – Chinese Yuan (¥)</option>
                                <option value="INR" {{ $settings->base_currency === 'INR' ? 'selected' : '' }}>INR – Indian Rupee (₹)</option>
                            </optgroup>
                            <optgroup label="Asia & Pacific">
                                <option value="IDR" {{ $settings->base_currency === 'IDR' ? 'selected' : '' }}>IDR – Indonesian Rupiah (Rp)</option>
                                <option value="PHP" {{ $settings->base_currency === 'PHP' ? 'selected' : '' }}>PHP – Philippine Peso (₱)</option>
                                <option value="THB" {{ $settings->base_currency === 'THB' ? 'selected' : '' }}>THB – Thai Baht (฿)</option>
                                <option value="VND" {{ $settings->base_currency === 'VND' ? 'selected' : '' }}>VND – Vietnamese Dong (₫)</option>
                                <option value="KRW" {{ $settings->base_currency === 'KRW' ? 'selected' : '' }}>KRW – South Korean Won (₩)</option>
                                <option value="HKD" {{ $settings->base_currency === 'HKD' ? 'selected' : '' }}>HKD – Hong Kong Dollar (HK$)</option>
                                <option value="TWD" {{ $settings->base_currency === 'TWD' ? 'selected' : '' }}>TWD – New Taiwan Dollar (NT$)</option>
                                <option value="NZD" {{ $settings->base_currency === 'NZD' ? 'selected' : '' }}>NZD – New Zealand Dollar (NZ$)</option>
                                <option value="PKR" {{ $settings->base_currency === 'PKR' ? 'selected' : '' }}>PKR – Pakistani Rupee (₨)</option>
                                <option value="BDT" {{ $settings->base_currency === 'BDT' ? 'selected' : '' }}>BDT – Bangladeshi Taka (৳)</option>
                            </optgroup>
                            <optgroup label="Middle East & Africa">
                                <option value="SAR" {{ $settings->base_currency === 'SAR' ? 'selected' : '' }}>SAR – Saudi Riyal (SAR)</option>
                                <option value="AED" {{ $settings->base_currency === 'AED' ? 'selected' : '' }}>AED – UAE Dirham (AED)</option>
                                <option value="QAR" {{ $settings->base_currency === 'QAR' ? 'selected' : '' }}>QAR – Qatari Riyal (QAR)</option>
                                <option value="KWD" {{ $settings->base_currency === 'KWD' ? 'selected' : '' }}>KWD – Kuwaiti Dinar (KWD)</option>
                                <option value="EGP" {{ $settings->base_currency === 'EGP' ? 'selected' : '' }}>EGP – Egyptian Pound (EGP)</option>
                                <option value="ZAR" {{ $settings->base_currency === 'ZAR' ? 'selected' : '' }}>ZAR – South African Rand (R)</option>
                                <option value="NGN" {{ $settings->base_currency === 'NGN' ? 'selected' : '' }}>NGN – Nigerian Naira (₦)</option>
                                <option value="KES" {{ $settings->base_currency === 'KES' ? 'selected' : '' }}>KES – Kenyan Shilling (KES)</option>
                            </optgroup>
                            <optgroup label="Europe & Americas">
                                <option value="CHF" {{ $settings->base_currency === 'CHF' ? 'selected' : '' }}>CHF – Swiss Franc (CHF)</option>
                                <option value="SEK" {{ $settings->base_currency === 'SEK' ? 'selected' : '' }}>SEK – Swedish Krona (SEK)</option>
                                <option value="NOK" {{ $settings->base_currency === 'NOK' ? 'selected' : '' }}>NOK – Norwegian Krone (NOK)</option>
                                <option value="DKK" {{ $settings->base_currency === 'DKK' ? 'selected' : '' }}>DKK – Danish Krone (DKK)</option>
                                <option value="PLN" {{ $settings->base_currency === 'PLN' ? 'selected' : '' }}>PLN – Polish Zloty (zł)</option>
                                <option value="TRY" {{ $settings->base_currency === 'TRY' ? 'selected' : '' }}>TRY – Turkish Lira (₺)</option>
                                <option value="BRL" {{ $settings->base_currency === 'BRL' ? 'selected' : '' }}>BRL – Brazilian Real (R$)</option>
                                <option value="MXN" {{ $settings->base_currency === 'MXN' ? 'selected' : '' }}>MXN – Mexican Peso ($)</option>
                                <option value="CLP" {{ $settings->base_currency === 'CLP' ? 'selected' : '' }}>CLP – Chilean Peso ($)</option>
                                <option value="COP" {{ $settings->base_currency === 'COP' ? 'selected' : '' }}>COP – Colombian Peso ($)</option>
                                <option value="ARS" {{ $settings->base_currency === 'ARS' ? 'selected' : '' }}>ARS – Argentine Peso ($)</option>
                            </optgroup>
                        </select>
                        <div class="form-text">All dashboard spend charts and expenditure reports will automatically convert and display in this currency.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <span class="form-label fw-semibold d-block">Interface Theme</span>
                        <div class="card bg-body-tertiary border-0 p-3 mt-1">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       aria-checked="{{ $settings->dark_mode ? 'true' : 'false' }}"
                                       id="darkModeToggle" {{ $settings->dark_mode ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="darkModeToggle">
                                    <span id="darkModeLabel">{{ $settings->dark_mode ? 'Dark' : 'Light' }} mode</span>
                                </label>
                            </div>
                            <small class="text-muted mt-2 d-block">Theme preference saves automatically and syncs instantly across devices.</small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- ALERT PREFERENCES --}}
                <div>
                    <h6 class="fw-bold mb-3"><i class="bi bi-bell text-warning me-2"></i>Renewal Alerts & Lead Times</h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="card bg-body-tertiary border-0 p-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       aria-checked="{{ $settings->alert_7_days ? 'true' : 'false' }}"
                                       name="alert_7_days" id="alert7" value="1"
                                       {{ $settings->alert_7_days ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert7">
                                    <span class="fw-semibold">7-day reminder</span>
                                    <span class="text-muted ms-1 d-block d-sm-inline">— Display on upcoming dashboard renewals 7 days prior</span>
                                </label>
                            </div>
                        </div>

                        <div class="card bg-body-tertiary border-0 p-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       aria-checked="{{ $settings->alert_3_days ? 'true' : 'false' }}"
                                       name="alert_3_days" id="alert3" value="1"
                                       {{ $settings->alert_3_days ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert3">
                                    <span class="fw-semibold">3-day urgent reminder</span>
                                    <span class="text-muted ms-1 d-block d-sm-inline">— Highlight urgently on dashboard 3 days prior</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-2">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i>Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FX CONVERSION INTELLIGENCE NOTE --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
                <span class="p-2 rounded-2 bg-success bg-opacity-10 text-success flex-shrink-0">
                    <i class="bi bi-currency-exchange fs-5"></i>
                </span>
                <div>
                    <h6 class="fw-bold mb-1">Multi-Currency Normalization</h6>
                    <p class="text-muted small mb-0">
                        SubShield supports subscriptions entered in any world currency (e.g. USD Netflix, EUR Spotify, GBP AWS). Selecting your base tracking currency above instantly recomputes your monthly burn and annual forecasts using live European Central Bank and central bank FX rates without altering original invoice values.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
