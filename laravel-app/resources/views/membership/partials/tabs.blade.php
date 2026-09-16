<style>
    .jb-shell { max-width: 1100px; margin: 0 auto; }
    .jb-nav {
        display: flex; flex-wrap: wrap; gap: 10px; margin: 0 0 1.5rem; padding: 0; border: 0;
    }
    .jb-nav a {
        position: relative; display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 16px; border-radius: 10px; border: 2px solid #cbd5e1;
        background: #fff; color: #64748b; text-decoration: none !important;
        font-weight: 700; font-size: 13px; line-height: 1.2; white-space: nowrap;
    }
    .jb-nav a.is-active { color: #fff !important; }
    .jb-nav a.tone-orange { border-color: #f59e0b; color: #c77708; }
    .jb-nav a.tone-orange.is-active, .jb-nav a.tone-orange:hover { background: #f59e0b; border-color: #f59e0b; color: #10213d !important; }
    .jb-nav a.tone-green { border-color: #10b981; color: #10b981; }
    .jb-nav a.tone-green.is-active, .jb-nav a.tone-green:hover { background: #10b981; border-color: #10b981; color: #fff !important; }
    .jb-nav a.tone-red { border-color: #ef4444; color: #ef4444; }
    .jb-nav a.tone-red.is-active, .jb-nav a.tone-red:hover { background: #ef4444; border-color: #ef4444; color: #fff !important; }
    .jb-title { color: #0b3f90; font-weight: 800; font-size: 1.75rem; margin: 0 0 4px; }
    .jb-subtitle { color: #6b7280; margin: 0; }
    .jb-card {
        background: #fff; border: 1px solid #eef2f7; border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15,23,42,.06); padding: 1.25rem; margin-bottom: 1rem;
    }
    .jb-btn {
        background: #0b3f90; border: 1px solid #0b3f90; color: #fff;
        border-radius: 8px; padding: 8px 14px; font-weight: 600; font-size: 14px;
        display: inline-flex; align-items: center; gap: 6px; cursor: pointer; text-decoration: none;
    }
    .jb-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #f1f5f9; color: #334155; }
    .jb-badge.ok { background: #dcfce7; color: #166534; }
    .jb-badge.wait { background: #ffedd5; color: #9a3412; }
    .jb-badge.no { background: #fee2e2; color: #991b1b; }
    .jb-nav-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px;
        font-size: 11px; font-weight: 800; background: rgba(15, 23, 42, 0.08);
    }
    .jb-field { width: 100%; border: 1px solid #d7deea; border-radius: 8px; padding: 9px 12px; font-size: 14px; }
    .jb-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .mem-portrait { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; background: #003D82; border: 2px solid #c6ab47; }
</style>
@php
    $membershipTab = $membershipTab ?? 'membership.awaiting';
    $counts = $counts ?? [];
    $tabs = [
        ['membership.awaiting', 'Awaiting Approvals', 'dripicons-clock', 'tone-orange'],
        ['membership.members', 'Members', 'dripicons-user-group', 'tone-green'],
        ['membership.rejected', 'Rejected', 'dripicons-wrong', 'tone-red'],
    ];
@endphp
<nav class="jb-nav" aria-label="Membership">
    @foreach($tabs as $tab)
        @php $count = $counts[$tab[0]] ?? null; @endphp
        <a href="{{ route($tab[0]) }}" class="{{ $tab[3] }} {{ $membershipTab === $tab[0] ? 'is-active' : '' }}">
            <i class="{{ $tab[2] }}"></i> {{ $tab[1] }}
            @if($count !== null)
                <span class="jb-nav-count">{{ $count }}</span>
            @endif
        </a>
    @endforeach
</nav>
