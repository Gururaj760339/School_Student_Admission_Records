@php
    $name = $name ?? '';
    $field = $field ?? ['label' => $name, 'type' => 'text'];
    $value = $value ?? null;
    $required = $required ?? [];

    $type = $field['type'] ?? 'text';
    $id = 'field-' . $name;
    $current = old($name, $value);
    $isRequired = in_array($name, $required, true);
@endphp

<div class="form-field {{ $errors->has($name) ? 'has-error' : '' }}">
    <label for="{{ $id }}">
        {{ $field['label'] ?? $name }}

        @if ($isRequired)
            <span class="req" title="Required">*</span>
        @endif
    </label>

    @if ($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="3"
            autocomplete="off"
            autocapitalize="off"
            spellcheck="false"
            @if ($isRequired) required @endif
        >{{ $current }}</textarea>

    @elseif ($type === 'select')
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            autocomplete="off"
            @if ($isRequired) required @endif
        >
            <option value="">— select —</option>

            @foreach (($field['options'] ?? []) as $option)
                <option
                    value="{{ $option }}"
                    @selected((string) $current === (string) $option)
                >
                    {{ $option }}
                </option>
            @endforeach
        </select>

    @else
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ $current }}"
            autocomplete="off"
            autocapitalize="off"
            @if ($type === 'password')
                autocomplete="new-password"
            @endif
            @if (isset($field['step']))
                step="{{ $field['step'] }}"
            @endif
            @if ($type === 'tel')
                inputmode="tel"
            @endif
            @if ($type === 'date')
                placeholder="YYYY-MM-DD"
            @endif
            @if ($isRequired)
                required
            @endif
        >
    @endif

    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>