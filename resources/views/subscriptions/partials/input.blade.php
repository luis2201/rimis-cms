@php($type = $type ?? 'text')
<div class="subscription-field">
    <label for="{{ $name }}">{{ $label }} @if($required ?? false)<b>*</b>@endif</label>
    <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" @if($required ?? false) required @endif @isset($placeholder) placeholder="{{ $placeholder }}" @endisset @isset($inputmode) inputmode="{{ $inputmode }}" @endisset class="@error($name) is-invalid @enderror">
    @error($name)<p class="subscription-error">{{ $message }}</p>@enderror
</div>
