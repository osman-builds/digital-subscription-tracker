{{-- Shared form fields for create & edit --}}
<div class="row g-3">
    {{-- Name --}}
    <div class="col-12 col-md-8">
        <label for="sub_name" class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
        <input type="text" id="sub_name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $subscription->name ?? '') }}" placeholder="e.g. Netflix, Spotify" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Status --}}
    <div class="col-12 col-md-4">
        <label for="sub_status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
        <select id="sub_status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'cancelled' => 'Cancelled'] as $val => $label)
                <option value="{{ $val }}" {{ old('status', $subscription->status ?? 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Amount --}}
    <div class="col-12 col-md-4">
        <label for="sub_amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="number" id="sub_amount" step="0.01" min="0" name="amount"
                   class="form-control @error('amount') is-invalid @enderror"
                   value="{{ old('amount', $subscription->amount ?? '') }}" placeholder="9.99" required>
            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Currency --}}
    <div class="col-12 col-md-4">
        <label for="sub_currency" class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
        <select id="sub_currency" name="currency" class="form-select @error('currency') is-invalid @enderror" required>
            @foreach($currencies as $code => $label)
                <option value="{{ $code }}" {{ old('currency', $subscription->currency ?? 'MYR') === $code ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Will be auto-converted to MYR for tracking</div>
        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
        @error('billing_cycle')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Category --}}
    <div class="col-12 col-md-4">
        <label for="sub_category_id" class="form-label fw-semibold">Category</label>
        <select id="sub_category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ old('category_id', $subscription->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Start Date --}}
    <div class="col-12 col-md-6">
        <label for="sub_start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
        <input type="date" id="sub_start_date" name="start_date"
               class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', isset($subscription) ? $subscription->start_date->format('Y-m-d') : '') }}" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Next Billing Date --}}
    <div class="col-12 col-md-6">
        <label for="sub_next_billing_date" class="form-label fw-semibold">Next Billing Date <span class="text-danger">*</span></label>
        <input type="date" id="sub_next_billing_date" name="next_billing_date"
               class="form-control @error('next_billing_date') is-invalid @enderror"
               value="{{ old('next_billing_date', isset($subscription) ? $subscription->next_billing_date->format('Y-m-d') : '') }}" required>
        @error('next_billing_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Description --}}
    <div class="col-12">
        <label for="sub_description" class="form-label fw-semibold">Description</label>
        <textarea id="sub_description" name="description" rows="2" class="form-control @error('description') is-invalid @enderror"
                  placeholder="Brief description…">{{ old('description', $subscription->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Website & Logo --}}
    <div class="col-12 col-md-6">
        <label for="sub_website_url" class="form-label fw-semibold">Website URL</label>
        <input type="url" id="sub_website_url" name="website_url" class="form-control @error('website_url') is-invalid @enderror"
               value="{{ old('website_url', $subscription->website_url ?? '') }}" placeholder="https://…">
        @error('website_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="sub_logo_url" class="form-label fw-semibold">Logo URL</label>
        <input type="url" id="sub_logo_url" name="logo_url" class="form-control @error('logo_url') is-invalid @enderror"
               value="{{ old('logo_url', $subscription->logo_url ?? '') }}" placeholder="https://…">
        @error('logo_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Notes --}}
    <div class="col-12">
        <label for="sub_notes" class="form-label fw-semibold">Notes</label>
        <input type="text" id="sub_notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
               value="{{ old('notes', $subscription->notes ?? '') }}" placeholder="Any extra notes…">
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>