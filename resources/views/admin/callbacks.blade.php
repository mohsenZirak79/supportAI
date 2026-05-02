@extends('admin.layouts.master')

@section('title', 'درخواست‌های تماس (ویجت)')

@push('styles')
<style>
    .callback-snippet { font-size: 0.8125rem; color: var(--admin-muted-text); max-width: 280px; }
    .callback-actions { min-width: 220px; }
    .callback-actions .form-select, .callback-actions textarea { font-size: 0.8125rem; }
    .callback-tel { font-weight: 600; color: var(--admin-primary); text-decoration: none; white-space: nowrap; }
    .callback-tel:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="list-page">
    <header class="list-page__header">
        <div>
            <h1>درخواست‌های تماس از ویجت چت</h1>
            <p class="list-page__subtitle">کاربرانی که پس از پیام دستیار موافقت تماس داده‌اند — با شمارهٔ ثبت‌شده در حساب</p>
        </div>
        <div class="list-page__actions">
            @can('read-chat')
                <a href="{{ route('admin.chats') }}" class="admin-btn admin-btn--secondary">گفت‌وگوها</a>
            @endcan
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success mb-3" role="alert">{{ session('success') }}</div>
    @endif

    @include('admin.partials.list-filters', [
        'action' => route('admin.callbacks'),
        'searchPlaceholder' => 'جستجو در نام یا شمارهٔ کاربر یا عنوان گفتگو...',
        'searchValue' => request('search'),
        'filters' => [
            [
                'name' => 'status',
                'label' => 'وضعیت',
                'empty_option' => 'همه',
                'options' => [
                    'pending' => $statusLabels['pending'],
                    'contacted' => $statusLabels['contacted'],
                    'declined' => $statusLabels['declined'],
                    'cancelled' => $statusLabels['cancelled'],
                ],
            ],
        ],
    ])

    <div class="list-page__card">
        <div class="table-responsive">
            <table class="list-page__table">
                <thead>
                    <tr>
                        <th>تاریخ</th>
                        <th>کاربر</th>
                        <th>تماس</th>
                        <th>گفتگو</th>
                        <th>خلاصهٔ پیام دستیار</th>
                        <th>وضعیت فعلی</th>
                        @can('update-chat')
                            <th>به‌روزرسانی</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $row)
                        @php
                            $phone = $row->user->phone ?? '';
                            $tel = $phone ? 'tel:' . preg_replace('/\s+/', '', $phone) : '';
                            $snippet = \Illuminate\Support\Str::limit(strip_tags($row->triggerMessage->content ?? ''), 120);
                            $st = $row->status;
                            $badgeClass = $st === 'pending' ? 'status-badge--pending' : ($st === 'contacted' ? 'status-badge--answered' : ($st === 'cancelled' ? 'status-badge--closed' : 'status-badge--closed'));
                        @endphp
                        <tr>
                            <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($row->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $row->user->name ?? '—' }}</div>
                                @if($row->handler)
                                    <div class="text-muted small">ثبت توسط: {{ $row->handler->name }}</div>
                                @endif
                            </td>
                            <td>
                                @if($tel)
                                    <a class="callback-tel" href="{{ $tel }}">{{ $phone }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="truncate-1 d-inline-block" style="max-width:160px" title="{{ $row->conversation->title ?? '' }}">{{ $row->conversation->title ?? '—' }}</span>
                                @can('read-chat')
                                    <div class="mt-1">
                                        <a class="small" href="{{ route('admin.chats', ['open' => $row->conversation_id]) }}">مشاهدهٔ گفتگو</a>
                                    </div>
                                @endcan
                            </td>
                            <td><span class="callback-snippet" title="{{ e($snippet) }}">{{ e($snippet) }}</span></td>
                            <td>
                                <span class="status-badge {{ $badgeClass }}">{{ $statusLabels[$st] ?? $st }}</span>
                                @if($row->contacted_at)
                                    <div class="small text-muted mt-1">تماس: {{ \Morilog\Jalali\Jalalian::fromDateTime($row->contacted_at)->format('Y/m/d H:i') }}</div>
                                @endif
                            </td>
                            @can('update-chat')
                                <td class="callback-actions">
                                    @if($st === 'cancelled')
                                        <span class="text-muted small">لغو شده توسط کاربر</span>
                                    @else
                                        <form method="post" action="{{ route('admin.callbacks.update', $row) }}" class="d-flex flex-column gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm" required>
                                                @foreach($statusLabels as $val => $label)
                                                    @if($val === 'cancelled')
                                                        @continue
                                                    @endif
                                                    <option value="{{ $val }}" @selected($st === $val)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <textarea name="admin_note" class="form-control form-control-sm" rows="2" placeholder="یادداشت داخلی (اختیاری)">{{ old('admin_note', $row->admin_note) }}</textarea>
                                            <button type="submit" class="admin-btn admin-btn--primary btn-sm">ذخیره</button>
                                        </form>
                                    @endif
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->can('update-chat') ? 7 : 6 }}" class="list-page__empty">موردی ثبت نشده است.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $requests])
    </div>
</div>
@endsection
