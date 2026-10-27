@if(session('status'))<div role="status" class="rounded-xl border border-teal-200 bg-teal-50 p-3 text-sm text-[#2D7080]">{{ session('status') }}</div>@endif
@if($errors->any())
    <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800"><p class="font-semibold">Vérifiez les informations saisies.</p><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
