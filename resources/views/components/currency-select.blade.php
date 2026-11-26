@props([
    'currencies' => [],
    'selected' => 'USD',
    'id' => 'sub_currency',
    'name' => 'currency',
    'label' => 'Original Currency',
    'help' => 'Will be auto-converted to your base tracking currency.',
])

<label for="{{ $id }}" class="form-label fw-semibold">{{ $label }} <span class="text-danger">*</span></label>
<select id="{{ $id }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" required>
    @foreach($currencies as $code => $optionLabel)
        <option value="{{ $code }}" {{ old($name, $selected) === $code ? 'selected' : '' }}>
            {{ $optionLabel }}
        </option>
    @endforeach
</select>
@if($help)
    <div class="form-text">{{ $help }}</div>
@endif
@error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
