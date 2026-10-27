@php
    $tone = match($status) {
        \App\Enums\ProspectStatus::Nouveau => 'bg-slate-100 text-slate-700',
        \App\Enums\ProspectStatus::Contacte => 'bg-sky-50 text-sky-800',
        \App\Enums\ProspectStatus::Engage => 'bg-teal-50 text-teal-800',
        \App\Enums\ProspectStatus::DemoEssai => 'bg-amber-50 text-amber-800',
        \App\Enums\ProspectStatus::Client => 'bg-emerald-50 text-emerald-800',
        \App\Enums\ProspectStatus::Perdu => 'bg-rose-50 text-rose-800',
    };
@endphp
<span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $tone }}">{{ $status->label() }}</span>
