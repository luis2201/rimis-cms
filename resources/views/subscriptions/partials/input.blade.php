@php($inputType = in_array($type ?? null, ['email', 'number', 'password', 'search', 'tel', 'text', 'url'], true) ? $type : 'text')
<div class="subscription-field">
    <label for="{{ $name }}">{{ $label }} @if($required ?? false)<b>*</b>@endif</label>
    <input id="{{ $name }}" type="{{ $inputType }}" name="{{ $name }}" value="{{ old($name) }}" @if(($required ?? false) && !isset($requiredWhen)) required @endif @isset($requiredWhen) x-bind:required="{{ $requiredWhen }}" @endisset @isset($placeholder) placeholder="{{ $placeholder }}" @endisset @isset($inputmode) inputmode="{{ $inputmode }}" @endisset class="@error($name) is-invalid @enderror">
    @error($name)<p class="subscription-error">{{ $message }}</p>@enderror
</div>
