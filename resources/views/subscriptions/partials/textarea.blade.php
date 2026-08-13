<div class="subscription-field">
    <label for="{{ $name }}">{{ $label }} @if($required ?? false)<b>*</b>@endif</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows ?? 3 }}" @if($required ?? false) required @endif @isset($placeholder) placeholder="{{ $placeholder }}" @endisset class="@error($name) is-invalid @enderror">{{ old($name) }}</textarea>
    @error($name)<p class="subscription-error">{{ $message }}</p>@enderror
</div>
