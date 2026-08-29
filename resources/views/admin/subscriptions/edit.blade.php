<x-app-layout>
    <x-slot name="header"><h1>Editar suscripción</h1></x-slot>

    <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row">
            <div class="col-lg-9">
                @if($errors->any())
                    <div class="alert alert-danger"><strong>Revisa los datos ingresados.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="card"><div class="card-header"><h3 class="card-title">Datos {{ $subscription->isProfessional() ? 'profesionales' : 'institucionales' }}</h3></div><div class="card-body">
                    @if($subscription->isProfessional())
                        <div class="row">
                            @foreach(['first_names'=>'Nombres completos','last_names'=>'Apellidos completos','email'=>'Correo electrónico','national_id'=>'Cédula','orcid'=>'Código ORCID','undergraduate_title'=>'Título de pregrado','affiliated_institution'=>'Institución afiliada','country'=>'País','city'=>'Ciudad','contact_phone'=>'Número de contacto'] as $name=>$label)
                                <div class="form-group col-md-6"><label for="{{ $name }}">{{ $label }} @if(!in_array($name,['orcid']))<span class="text-danger">*</span>@endif</label><input id="{{ $name }}" name="{{ $name }}" type="{{ $name==='email'?'email':'text' }}" value="{{ old($name,$subscription->{$name}) }}" class="form-control @error($name) is-invalid @enderror" @if(!in_array($name,['orcid'])) required @endif>@error($name)<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                            @endforeach
                            @foreach(['postgraduate_titles'=>'Títulos de posgrado','scientific_communities'=>'Comunidades científicas','teaching_functions'=>'Funciones de docencia actuales','current_research_functions'=>'Funciones de investigación actuales','research_activity'=>'Actividad investigativa'] as $name=>$label)
                                <div class="form-group col-12"><label for="{{ $name }}">{{ $label }} @if(in_array($name,['teaching_functions','current_research_functions','research_activity']))<span class="text-danger">*</span>@endif</label><textarea id="{{ $name }}" name="{{ $name }}" rows="3" class="form-control @error($name) is-invalid @enderror" @if(in_array($name,['teaching_functions','current_research_functions','research_activity'])) required @endif>{{ old($name,$subscription->{$name}) }}</textarea>@error($name)<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                            @endforeach
                        </div>
                        <h5 class="border-bottom pb-2 mt-3">Áreas de investigación</h5>
                        <div class="row">@foreach($researchAreas as $area)<div class="col-md-6"><div class="custom-control custom-checkbox mb-2"><input class="custom-control-input" id="area_{{ $loop->index }}" type="checkbox" name="research_areas[]" value="{{ $area }}" @checked(in_array($area,old('research_areas',$subscription->research_areas??[])))><label class="custom-control-label" for="area_{{ $loop->index }}">{{ $area }}</label></div></div>@endforeach</div>
                        @error('research_areas')<div class="text-danger small">{{ $message }}</div>@enderror
                        <div class="form-group mt-3"><label for="other_research_area">Especifique otra área</label><input id="other_research_area" name="other_research_area" value="{{ old('other_research_area',$subscription->other_research_area) }}" class="form-control @error('other_research_area') is-invalid @enderror">@error('other_research_area')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="form-group"><label for="personal_photo">Reemplazar foto personal</label>@if($subscription->personal_photo_path)<div class="mb-2"><img src="{{ Storage::disk('public')->url($subscription->personal_photo_path) }}" alt="Foto actual" style="width:90px;height:90px;object-fit:cover" class="img-thumbnail"></div>@endif<input id="personal_photo" name="personal_photo" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control-file @error('personal_photo') is-invalid @enderror"><small class="form-text text-muted">Opcional. JPG, PNG o WebP; máximo 5 MB.</small>@error('personal_photo')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    @else
                        <div class="row">
                            @foreach(['institution_name'=>'Nombre de la institución','principal_authority_name'=>'Autoridad principal','foundation_year'=>'Año de creación','email'=>'Correo institucional','country'=>'País','city'=>'Ciudad','main_phone'=>'Teléfono principal','mobile_phone'=>'Teléfono celular','requester_name'=>'Nombre del solicitante','requester_position'=>'Función del solicitante','requester_email'=>'Correo del solicitante'] as $name=>$label)
                                <div class="form-group col-md-6"><label for="{{ $name }}">{{ $label }} @if($name!=='main_phone')<span class="text-danger">*</span>@endif</label><input id="{{ $name }}" name="{{ $name }}" type="{{ str_contains($name,'email')?'email':($name==='foundation_year'?'number':'text') }}" value="{{ old($name,$subscription->{$name}) }}" class="form-control @error($name) is-invalid @enderror" @if($name!=='main_phone') required @endif>@error($name)<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                            @endforeach
                            <div class="form-group col-md-6"><label for="institution_type">Tipo de institución <span class="text-danger">*</span></label><select id="institution_type" name="institution_type" class="form-control @error('institution_type') is-invalid @enderror" required><option value="">Seleccione</option>@foreach($institutionTypes as $type)<option value="{{ $type }}" @selected(old('institution_type',$subscription->institution_type)===$type)>{{ $type }}</option>@endforeach</select>@error('institution_type')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                            <div class="form-group col-md-6"><label for="other_institution_type">Especifique otro tipo</label><input id="other_institution_type" name="other_institution_type" value="{{ old('other_institution_type',$subscription->other_institution_type) }}" class="form-control @error('other_institution_type') is-invalid @enderror">@error('other_institution_type')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        </div>
                        <div class="form-group"><label for="institution_logo">Reemplazar logotipo</label>@if($subscription->institution_logo_path)<div class="mb-2"><img src="{{ Storage::disk('public')->url($subscription->institution_logo_path) }}" alt="Logotipo actual" style="width:110px;height:90px;object-fit:contain" class="img-thumbnail"></div>@endif<input id="institution_logo" name="institution_logo" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control-file"><small class="form-text text-muted">Opcional. JPG, PNG o WebP; máximo 5 MB.</small>@error('institution_logo')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    @endif
                </div></div>
            </div>
            <div class="col-lg-3"><div class="card"><div class="card-body"><p><strong>Tipo:</strong><br>{{ \App\Models\Subscription::TYPE_LABELS[$subscription->type] }}</p><p><strong>Estado:</strong><br>{{ \App\Models\Subscription::STATUS_LABELS[$subscription->status] }}</p><small class="text-muted">El tipo y el estado no se modifican desde esta pantalla.</small><hr><button class="btn btn-primary btn-block"><i class="fas fa-save mr-1"></i> Guardar cambios</button><a href="{{ route('admin.subscriptions.show',$subscription) }}" class="btn btn-outline-secondary btn-block">Cancelar</a></div></div></div>
        </div>
    </form>
</x-app-layout>
