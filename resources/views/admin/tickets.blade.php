@extends('admin.layouts.master')
@php
use Illuminate\Support\Facades\Auth;
@endphp

@section('title', 'تیکت ها')

@push('styles')
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    @vite(['resources/css/admin-support.css'])
    <style>
            .ticket-chip{display:inline-flex;align-items:center;gap:.35rem;max-width:230px;padding:.35rem .6rem;border-radius:9999px;font-size:.78rem;line-height:1;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe}
            .ticket-chip .truncate{display:inline-block;max-width:150px;vertical-align:middle;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
            .bubble{max-width:88%;border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px}
            .bubble-user{background:#eff6ff;border-color:#bfdbfe;color:#1e40af}
            .bubble-agent{background:#ecfdf5;border-color:#a7f3d0;color:#064e3b}
            .msg-time{font-size:12px;color:#94a3b8}
            .truncate-1{max-width:380px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
            .status-badge{display:inline-block;padding:.25rem .55rem;border-radius:9999px;font-weight:700;font-size:.75rem}
            .st-pending{background:#fff7ed;color:#92400e;border:1px solid #fcd34d99}
            .st-answered{background:#ecfdf5;color:#065f46;border:1px solid #34d39999}
            .st-closed{background:#f1f5f9;color:#334155;border:1px solid #cbd5e199}
        </style>
@endpush

@push('scripts')
    <script>
        function getCsrfToken() {
            const el = document.querySelector('meta[name="csrf-token"]');
            return el ? el.getAttribute('content') : '{{ csrf_token() }}'
        }
        (function(){
            const token = getCsrfToken();
            const tkMsgList = document.getElementById('tkMsgList');
            const tkMeta = document.getElementById('tkMeta');
            const replyBox = document.getElementById('replyBox');
            const replyForm = document.getElementById('replyForm');

            let currentTicketId = null;
            let canReply = false;

            document.querySelectorAll('.btn-view-ticket').forEach(btn=>{
                btn.addEventListener('click', ()=> openTicket(btn));
            });

            async function openTicket(btn){
                const url = btn.getAttribute('data-url');
                currentTicketId = btn.getAttribute('data-id');

                tkMeta.innerHTML = '';
                tkMsgList.innerHTML = '<div class="text-center text-muted py-2">در حال بارگذاری…</div>';
                replyBox.style.display = 'none';

                try{
                    const res = await fetch(url, { headers: { 'Accept':'application/json' }});
                    if(!res.ok){
                        window.toast?.error('خطا در بارگذاری جزئیات');
                        tkMsgList.innerHTML = '<div class="text-danger">خطا در بارگذاری.</div>';
                        return;
                    }
                    const data = await res.json();
                    renderDetails(data);
                }catch(e){
                    tkMsgList.innerHTML = '<div class="text-danger">خطا در بارگذاری.</div>';
                }
            }

            function renderDetails(data){
                document.getElementById('ticketModalLabel').textContent = 'تیکت: ' + (data.ticket?.title || '-');

                const createdAt = toEnDate(data.ticket?.created_at);
                const statusLbl = statusLabel(data.ticket?.status);
                tkMeta.innerHTML = `
                <div>ارسال‌کننده: <strong>${escapeHtml(data.ticket?.sender?.name || '-')}</strong></div>
                <div>تاریخ ایجاد: <span>${createdAt}</span></div>
                <div>وضعیت: <span>${statusLbl}</span></div>
            `;

                tkMsgList.innerHTML = '';
                (data.messages || []).forEach(m=>{
                    const isSupport = String(m.sender_type||'').toLowerCase() !== 'user';
                    const side = isSupport ? 'start' : 'end';
                    const who  = isSupport ? 'پشتیبان' : 'کاربر';

                    const box = document.createElement('div');
                    box.innerHTML = `
                  <div class="d-flex justify-content-${side} mb-3">
                    <div class="message-bubble ${isSupport ? 'bubble-agent' : 'bubble-user'}" dir="rtl" style="max-width: 85%;">
                        <div class="small mb-2 fw-semibold" style="opacity: 0.8;">${who}</div>
                        <div class="mb-2" style="white-space:pre-wrap;word-break:break-word;">${escapeHtml(m.message || '')}</div>
                        ${renderFiles(m.attachments||[])}
                        <div class="msg-time mt-2">${toEnDate(m.created_at)}</div>
                    </div>
                  </div>
                `;
                    tkMsgList.appendChild(box);
                });

                canReply = !!data.can_reply;
                replyBox.style.display = canReply ? 'block' : 'none';
            }

            function renderFiles(files){
                if(!files.length) return '';
                let html = '<div class="d-flex flex-wrap gap-2">';
                files.slice(0, 100).forEach(f=>{
                    html += `
                <a class="ticket-chip" href="${f.url}" target="_blank" title="${escapeHtml(f.name||'file')}">
                  <span>📎</span>
                  <span class="truncate">${escapeHtml(f.name||'file')}</span>
                </a>`;
                });
                html += '</div>';
                return html;
            }

            replyForm.addEventListener('submit', async (e)=>{
                e.preventDefault();
                if(!currentTicketId) return;
                const formData = new FormData(replyForm);
                try{
                    const res = await fetch(`/admin/tickets/${currentTicketId}/messages`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: formData
                    });
                    if(!res.ok){
                        const t = await res.text().catch(()=> '');
                        console.error('reply failed:', res.status, t);
                        window.toast?.error('ثبت پاسخ ناموفق بود.');
                        return;
                    }
                    window.toast?.success('پاسخ ثبت شد.');
                    // رفرش دیتیل
                    const showUrl = document.querySelector(`.btn-view-ticket[data-id="${currentTicketId}"]`)?.getAttribute('data-url');
                    if(showUrl){
                        const r = await fetch(showUrl, { headers: { 'Accept':'application/json' }});
                        renderDetails(await r.json());
                    }
                }catch(err){
                    window.toast?.error('خطای شبکه در ثبت پاسخ');
                }
            });

            function toEnDate(iso){
                try{
                    return new Date(iso).toLocaleString('en-GB', {year:'numeric', month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit'});
                }catch{ return iso || '' }
            }
            function statusLabel(s){
                if(s==='pending') return 'در انتظار پاسخ';
                if(s==='answered') return 'پاسخ داده شده';
                if(s==='closed') return 'بسته شده';
                return s || '-';
            }
            function escapeHtml(s){return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]))}
        })();
    </script>
@endpush

@section('content')
<div class="admin-support-page">
    <section class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
        <div class="container-fluid py-4">
            <div class="page-header mb-4">
                <h1 class="page-title">تیکت‌ها</h1>
                <p class="page-subtitle">مدیریت و پیگیری درخواست‌های پشتیبانی</p>
            </div>

            @php
                $user = Auth::user();
                $hasInternal = $user->roles()->where('is_internal', 1)->exists();
                $isAdmin = $user->hasRole('ادمین') || $hasInternal;
                $query = \App\Domains\Shared\Models\Ticket::whereNull('parent_id');
                if (!$isAdmin) {
                    $roleIds = $user->roles()->pluck('id')->all();
                    $query->whereIn('department_role_id', $roleIds);
                }
                $totalTickets = $query->count();
                $pendingCount = (clone $query)->where('status', 'pending')->count();
                $answeredCount = (clone $query)->where('status', 'answered')->count();
                $closedCount = (clone $query)->where('status', 'closed')->count();
            @endphp

            <div class="summary-grid mb-4">
                <div class="summary-card">
                    <div class="summary-card__icon">📋</div>
                    <div class="summary-card__label">کل تیکت‌ها</div>
                    <div class="summary-card__value">{{ $totalTickets }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card__icon">⏳</div>
                    <div class="summary-card__label">در انتظار پاسخ</div>
                    <div class="summary-card__value" style="color: #f59e0b;">{{ $pendingCount }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card__icon">✅</div>
                    <div class="summary-card__label">پاسخ داده شده</div>
                    <div class="summary-card__value" style="color: #10b981;">{{ $answeredCount }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card__icon">🔒</div>
                    <div class="summary-card__label">بسته شده</div>
                    <div class="summary-card__value" style="color: #6b7280;">{{ $closedCount }}</div>
                </div>
            </div>

            <div class="support-card">
                <div class="support-card-header">
                    <h6>لیست تیکت‌ها</h6>
                </div>
                <div class="support-card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0 datatable" id="example">
                            <thead>
                            <tr>
                                <th class="text-center text-secondary text-xxs font-weight-bolder opacity-7">عنوان</th>
                                <th class="text-center text-secondary text-xxs font-weight-bolder opacity-7">نام ارسال‌کننده</th>
                                <th class="text-center text-secondary text-xxs font-weight-bolder opacity-7">تاریخ ایجاد</th>
                                <th class="text-center text-secondary text-xxs font-weight-bolder opacity-7">وضعیت</th>
                                <th class="text-center text-secondary text-xxs font-weight-bolder opacity-7">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($tickets as $t)
                                <tr>
                                    <td class="text-center">
                                        <span class="truncate-1" title="{{ $t->title }}">{{ $t->title }}</span>
                                    </td>
                                    <td class="text-center">{{ $t->sender->name ?? '-' }}</td>
                                    <td class="text-center">
                                        {{ \Morilog\Jalali\Jalalian::fromDateTime($t->created_at)->format('Y/m/d') }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $class = $t->status === 'pending' ? 'st-pending' : ($t->status==='answered'?'st-answered':'st-closed');
                                            $label = $t->status === 'pending' ? 'در انتظار پاسخ' : ($t->status==='answered'?'پاسخ داده شده':'بسته شده');
                                        @endphp
                                        <span class="status-badge {{ $class }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button
                                            class="btn btn-sm btn-primary btn-view-ticket"
                                            data-id="{{ $t->id }}"
                                            data-url="{{ route('admin.tickets.show', $t->id) }}"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#ticketModal">
                                            مشاهده
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="px-3 mt-3">
                            {{ $tickets->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="max-height:90vh;">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketModalLabel">جزئیات تیکت</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <div id="tkMeta" class="mb-4 p-3 bg-light rounded-3" style="background: #f8fafb !important; border: 1px solid #e5e7eb;"></div>

                <div class="mb-4">
                    <h6 class="mb-3 fw-bold" style="color: var(--support-deep-blue);">پیام‌ها</h6>
                    <div id="tkMsgList" class="d-flex flex-column gap-3 p-3 rounded-3" style="max-height:45vh; overflow-y:auto; background: #f8fafb; border: 1px solid #e5e7eb;"></div>
                </div>

                <div id="replyBox" class="mt-4" style="display:none;">
                    <hr class="my-4">
                    <h6 class="mb-3 fw-bold" style="color: var(--support-deep-blue);">ارسال پاسخ</h6>
                    <form id="replyForm">
                        <div class="mb-3">
                            <label class="form-label">پاسخ شما</label>
                            <textarea name="message" class="form-control" rows="4" required placeholder="پاسخ خود را وارد کنید..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">فایل‌های پیوست (اختیاری)</label>
                            <input type="file" name="files[]" class="form-control" multiple>
                            <div class="form-text">حداکثر 10 فایل، هر کدام تا 5MB</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">ثبت پاسخ</button>
                        </div>
                    </form>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@endsection
