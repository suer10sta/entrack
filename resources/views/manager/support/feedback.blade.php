@foreach(['success','warning'] as $kind)
    @if(session($kind))<div role="status" class="cs-feedback cs-feedback-{{ $kind }}">{{ session($kind) }}</div>@endif
@endforeach
@if($errors->any())<div role="alert" class="cs-feedback cs-feedback-warning"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
