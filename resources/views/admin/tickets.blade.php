@extends('admin.layouts.master')

@section('title', 'تیکت ها')

@push('styles')
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
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

            const urlParams = new URLSearchParams(window.location.search);
            const preselectId = urlParams.get('ticket');
            if (preselectId) {
                const btn = document.querySelector(`.btn-view-ticket[data-id="${preselectId}"]`);
                if (btn) {
                    openTicket(btn);
                } else {
                    openTicketById(preselectId);
                }
                const modalEl = document.getElementById('ticketModal');
                if (modalEl && window.bootstrap?.Modal) {
                    const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            }

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

            async function openTicketById(ticketId){
                const url = `/admin/tickets/${ticketId}`;
                currentTicketId = ticketId;

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
                  <div class="d-flex justify-content-${side}">
                    <div class="bubble ${isSupport ? 'bubble-agent' : 'bubble-user'}" dir="rtl">
                        <div class="small text-muted mb-1">${who}</div>
                        <div class="mb-2" style="white-space:pre-wrap;word-break:break-word;">${escapeHtml(m.message || '')}</div>
                        ${renderFiles(m.attachments||[])}
                        <div class="mt-1 msg-time">${toEnDate(m.created_at)}</div>
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
<section class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg ">
    <!-- Navbar -->
    <!-- End Navbar -->
    <div class="container-fluid py-4">
        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>لیست تیکت ها</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
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
                        <div class="px-3">
                            {{ $tickets->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="max-height:90vh;">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketModalLabel">جزئیات تیکت</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <div id="tkMeta" class="mb-3 text-sm text-muted"></div>

                <div class="mb-4">
                    <h6 class="mb-2">پیام‌ها</h6>
                    <div id="tkMsgList" class="d-flex flex-column gap-3" style="max-height:45vh; overflow-y:auto; border:1px solid #eee; padding:10px;"></div>
                </div>

                <div id="replyBox" class="mt-3" style="display:none;">
                    <hr>
                    <h6 class="mb-2">ارسال پاسخ</h6>
                    <form id="replyForm">
                        <div class="mb-2">
                            <label class="form-label small">پاسخ شما</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small d-block">فایل‌های پیوست (اختیاری)</label>
                            <input type="file" name="files[]" class="form-control form-control-sm" multiple>
                            <div class="form-text">حداکثر 10 فایل، هر کدام تا 5MB</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-sm">ثبت پاسخ</button>

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
