<div class="subscription-field">
    <label for="{{ $name }}">{{ $label }} @if($required ?? false)<b>*</b>@endif</label>
    <input id="{{ $name }}" type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" @if($required ?? false) required @endif class="subscription-file @error($name) is-invalid @enderror">
    @isset($help)<p class="mt-2 text-xs text-slate-500">{{ $help }}</p>@endisset
    @error($name)<p class="subscription-error">{{ $message }}</p>@enderror
</div>
