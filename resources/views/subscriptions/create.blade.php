<x-guest-layout wide>
    @php($professional = $type === 'professional')
    <div class="flex flex-col gap-6 border-b border-slate-200 pb-7 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="subscription-kicker">Solicitud de membresía</span>
            <h1 class="subscription-title mt-2">Suscripción {{ $professional ? 'profesional' : 'institucional' }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Completa los datos solicitados. La cuenta se creará únicamente cuando la suscripción sea aprobada.</p>
        </div>
        <div class="subscription-icon shrink-0"><i class="fa-solid {{ $professional ? 'fa-user-graduate' : 'fa-building-columns' }}"></i></div>
    </div>

    @if ($errors->any())
        <div class="subscription-alert mt-6" role="alert"><i class="fa-solid fa-circle-exclamation"></i><div><strong>Revisa la información ingresada.</strong><p>Algunos campos están incompletos o ya se encuentran registrados.</p></div></div>
    @endif

    <form method="POST" action="{{ route('subscriptions.store', $type) }}" class="mt-8" x-data="{ otherArea: {{ in_array('Otra', old('research_areas', [])) ? 'true' : 'false' }}, otherInstitution: {{ old('institution_type') === 'Otra' ? 'true' : 'false' }} }">
        @csrf

        @if($professional)
            <section class="subscription-section">
                <div class="subscription-section-heading"><span>01</span><div><h2>Datos personales</h2><p>Información de identificación y contacto.</p></div></div>
                <div class="subscription-grid">
                    @include('subscriptions.partials.input', ['name'=>'first_names','label'=>'Nombres completos','required'=>true])
                    @include('subscriptions.partials.input', ['name'=>'last_names','label'=>'Apellidos completos','required'=>true])
                    @include('subscriptions.partials.input', ['name'=>'email','label'=>'Correo electrónico','type'=>'email','required'=>true])
                    @include('subscriptions.partials.input', ['name'=>'national_id','label'=>'Cédula','required'=>true,'inputmode'=>'numeric'])
                    @include('subscriptions.partials.input', ['name'=>'country','label'=>'País','required'=>true])
                    @include('subscriptions.partials.input', ['name'=>'city','label'=>'Ciudad de residencia','required'=>true])
                    @include('subscriptions.partials.input', ['name'=>'contact_phone','label'=>'Número de contacto','type'=>'tel','required'=>true,'inputmode'=>'tel'])
                    @include('subscriptions.partials.input', ['name'=>'orcid','label'=>'Código ORCID','placeholder'=>'0000-0000-0000-0000'])
                </div>
            </section>

            <section class="subscription-section mt-10">
                <div class="subscription-section-heading"><span>02</span><div><h2>Trayectoria académica</h2><p>Formación y experiencia investigativa.</p></div></div>
                <div class="subscription-grid">
                    @include('subscriptions.partials.input', ['name'=>'undergraduate_title','label'=>'Título de pregrado','required'=>true])
                    @include('subscriptions.partials.textarea', ['name'=>'postgraduate_titles','label'=>'Títulos de posgrado','placeholder'=>'Detalla tus títulos, uno por línea.'])
                    <div class="md:col-span-2">@include('subscriptions.partials.textarea', ['name'=>'scientific_communities','label'=>'Comunidades científicas a las que pertenece'])</div>
                    <div class="md:col-span-2">@include('subscriptions.partials.textarea', ['name'=>'research_activity','label'=>'Actividad investigativa','required'=>true,'rows'=>4])</div>
                </div>
            </section>

            <section class="subscription-section mt-10">
                <div class="subscription-section-heading"><span>03</span><div><h2>Áreas de investigación</h2><p>Selecciona todas las áreas relacionadas con tu trabajo.</p></div></div>
                <div class="subscription-check-grid">
                    @foreach($areas as $area)
                        <label class="subscription-check"><input type="checkbox" name="research_areas[]" value="{{ $area }}" @checked(in_array($area, old('research_areas', []))) @change="if ($event.target.value === 'Otra') otherArea = $event.target.checked"><span><i class="fa-solid fa-check"></i></span>{{ $area }}</label>
                    @endforeach
                </div>
                @error('research_areas')<p class="subscription-error">{{ $message }}</p>@enderror
                <div class="mt-5" x-show="otherArea" x-cloak>@include('subscriptions.partials.input', ['name'=>'other_research_area','label'=>'Especifique otra área','required'=>true,'requiredWhen'=>'otherArea'])</div>
            </section>
        @else
            <section class="subscription-section">
                <div class="subscription-section-heading"><span>01</span><div><h2>Datos de la institución</h2><p>Información legal y representación institucional.</p></div></div>
                <div class="subscription-grid">
                    <div class="md:col-span-2">@include('subscriptions.partials.input', ['name'=>'institution_name','label'=>'Nombre de la institución','required'=>true])</div>
                    @include('subscriptions.partials.input', ['name'=>'ruc','label'=>'RUC','required'=>true,'inputmode'=>'numeric'])
                    @include('subscriptions.partials.input', ['name'=>'rector_name','label'=>'Nombre del rector','required'=>true])
                    <div class="subscription-field"><label for="institution_type">Tipo de institución <b>*</b></label><select id="institution_type" name="institution_type" required @change="otherInstitution = $event.target.value === 'Otra'" class="@error('institution_type') is-invalid @enderror"><option value="">Selecciona una opción</option>@foreach($institutionTypes as $item)<option value="{{ $item }}" @selected(old('institution_type') === $item)>{{ $item }}</option>@endforeach</select>@error('institution_type')<p class="subscription-error">{{ $message }}</p>@enderror</div>
                    <div x-show="otherInstitution" x-cloak>@include('subscriptions.partials.input', ['name'=>'other_institution_type','label'=>'Especifique otro tipo','required'=>true,'requiredWhen'=>'otherInstitution'])</div>
                </div>
            </section>

            <section class="subscription-section mt-10">
                <div class="subscription-section-heading"><span>02</span><div><h2>Ubicación y contacto</h2><p>Canales oficiales para comunicarnos con la institución.</p></div></div>
                <div class="subscription-grid">
                    @include('subscriptions.partials.input', ['name'=>'country','label'=>'País','required'=>true])
                    @include('subscriptions.partials.input', ['name'=>'city','label'=>'Ciudad','required'=>true])
                    <div class="md:col-span-2">@include('subscriptions.partials.input', ['name'=>'email','label'=>'Correo electrónico institucional','type'=>'email','required'=>true])</div>
                    @include('subscriptions.partials.input', ['name'=>'main_phone','label'=>'Teléfono principal','type'=>'tel','inputmode'=>'tel'])
                    @include('subscriptions.partials.input', ['name'=>'mobile_phone','label'=>'Teléfono celular','type'=>'tel','required'=>true,'inputmode'=>'tel'])
                </div>
            </section>
        @endif

        <div class="mt-10 flex flex-col-reverse gap-3 border-t border-slate-200 pt-7 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('subscriptions.index') }}" class="subscription-back"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            <button class="subscription-submit">Enviar suscripción <i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </form>
</x-guest-layout>
