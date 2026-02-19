@extends('admin.layouts.master')

@section('title', 'تیکت ها')

{{-- استایل‌های جزئیات تیکت در admin.css (فاز ۵: detail-meta, detail-bubble, detail-attachment-link) --}}

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
            document.getElementById('ticketModal').addEventListener('click', function(e){
                var retryBtn = e.target.closest('[data-retry-ticket]');
                if (retryBtn && retryBtn.classList.contains('admin-retry-btn')) {
                    var id = retryBtn.getAttribute('data-retry-ticket');
                    if (id) openTicketById(id);
                }
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
                tkMsgList.innerHTML = '<div class="detail-messages__loading">در حال بارگذاری…</div>';
                replyBox.style.display = 'none';

                try{
                    const res = await fetch(url, { headers: { 'Accept':'application/json' }});
                    if(!res.ok){
                        window.toast?.error('خطا در بارگذاری جزئیات');
                        showTicketError();
                        return;
                    }
                    const data = await res.json();
                    renderDetails(data);
                }catch(e){
                    showTicketError();
                }
            }

            async function openTicketById(ticketId){
                const url = `/admin/tickets/${ticketId}`;
                currentTicketId = ticketId;

                tkMeta.innerHTML = '';
                tkMsgList.innerHTML = '<div class="detail-messages__loading">در حال بارگذاری…</div>';
                replyBox.style.display = 'none';

                try{
                    const res = await fetch(url, { headers: { 'Accept':'application/json' }});
                    if(!res.ok){
                        window.toast?.error('خطا در بارگذاری جزئیات');
                        showTicketError();
                        return;
                    }
                    const data = await res.json();
                    renderDetails(data);
                }catch(e){
                    showTicketError();
                }
            }

            function showTicketError(){
                tkMsgList.innerHTML = '<div class="admin-error-box"><span class="admin-error-box__msg">خطا در بارگذاری.</span><button type="button" class="admin-retry-btn" data-retry-ticket="' + (currentTicketId || '') + '">تلاش مجدد</button></div>';
            }

            function renderDetails(data){
                const title = data.ticket?.title || '-';
                document.getElementById('ticketModalLabel').textContent = 'جزئیات تیکت';

                const createdAt = toEnDate(data.ticket?.created_at);
                const statusLbl = statusLabel(data.ticket?.status);
                const statusClass = data.ticket?.status === 'pending' ? 'status-badge--pending' : (data.ticket?.status === 'answered' ? 'status-badge--answered' : 'status-badge--closed');
                tkMeta.innerHTML = `
                <div class="detail-meta">
                    <h6 class="detail-meta__title">${escapeHtml(title)}</h6>
                    <div class="detail-meta__row"><span class="detail-meta__label">ارسال‌کننده:</span><span class="detail-meta__value">${escapeHtml(data.ticket?.sender?.name || '-')}</span></div>
                    <div class="detail-meta__row"><span class="detail-meta__label">تاریخ ایجاد:</span><span>${createdAt}</span></div>
                    <div class="detail-meta__row"><span class="detail-meta__label">وضعیت:</span><span class="status-badge ${statusClass}">${statusLbl}</span></div>
                </div>`;

                tkMsgList.innerHTML = '';
                (data.messages || []).forEach(m=>{
                    const isSupport = String(m.sender_type||'').toLowerCase() !== 'user';
                    const side = isSupport ? 'start' : 'end';
                    const who  = isSupport ? 'پشتیبان' : 'کاربر';

                    const box = document.createElement('div');
                    box.innerHTML = `
                  <div class="d-flex justify-content-${side}">
                    <div class="detail-bubble ${isSupport ? 'detail-bubble--agent' : 'detail-bubble--user'}" dir="rtl">
                        <div class="detail-bubble__sender">${who}</div>
                        <div style="white-space:pre-wrap;word-break:break-word;">${escapeHtml(m.message || '')}</div>
                        ${renderFiles(m.attachments||[])}
                        <div class="detail-bubble__time">${toEnDate(m.created_at)}</div>
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
                let html = '<div class="detail-attachments">';
                files.slice(0, 100).forEach(f=>{
                    html += `<a class="detail-attachment-link" href="${f.url}" target="_blank" rel="noopener" title="${escapeHtml(f.name||'file')}"><span class="truncate">${escapeHtml(f.name||'file')}</span></a>`;
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
<div class="list-page">
    <header class="list-page__header">
        <div>
            <h1>تیکت‌ها</h1>
            <p class="list-page__subtitle">مشاهده و پاسخ به تیکت‌های پشتیبانی</p>
        </div>
        <div class="list-page__actions"></div>
    </header>

    @include('admin.partials.list-filters', [
        'action' => route('admin.tickets'),
        'searchPlaceholder' => 'جستجو در عنوان تیکت یا نام ارسال‌کننده...',
        'searchValue' => request('search'),
        'filters' => [
            [
                'name' => 'status',
                'label' => 'وضعیت',
                'empty_option' => 'همه',
                'options' => [
                    'pending' => 'در انتظار پاسخ',
                    'answered' => 'پاسخ داده شده',
                    'closed' => 'بسته شده',
                ],
            ],
        ],
    ])

    <div class="list-page__card">
        <div class="table-responsive">
            <table class="list-page__table">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>نام ارسال‌کننده</th>
                        <th>تاریخ ایجاد</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                        <tr>
                            <td>
                                <span class="truncate-1" title="{{ $t->title }}">{{ $t->title }}</span>
                            </td>
                            <td>{{ $t->sender->name ?? '-' }}</td>
                            <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($t->created_at)->format('Y/m/d') }}</td>
                            <td>
                                @php
                                    $statusClass = $t->status === 'pending' ? 'status-badge--pending' : ($t->status === 'answered' ? 'status-badge--answered' : 'status-badge--closed');
                                    $statusLabel = $t->status === 'pending' ? 'در انتظار پاسخ' : ($t->status === 'answered' ? 'پاسخ داده شده' : 'بسته شده');
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <button
                                    class="list-page__btn-view btn-view-ticket"
                                    data-id="{{ $t->id }}"
                                    data-url="{{ route('admin.tickets.show', $t->id) }}"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ticketModal">
                                    مشاهده
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="list-page__empty">هیچ موردی یافت نشد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $tickets])
    </div>
</div>

<!-- Modal -->
<div class="modal fade admin-modal" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
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
                    <div id="tkMsgList" class="detail-messages"></div>
                </div>
                <div id="replyBox" class="mt-3" style="display:none;">
                    <hr>
                    <h6 class="mb-2">ارسال پاسخ</h6>
                    <form id="replyForm" class="admin-form" data-ajax-form="1">
                        <div class="mb-3">
                            <label class="form-label">پاسخ شما</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-block">فایل‌های پیوست (اختیاری)</label>
                            <input type="file" name="files[]" class="form-control form-control-sm" multiple>
                            <div class="form-text">حداکثر 10 فایل، هر کدام تا 5MB</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="admin-btn admin-btn--primary">ثبت پاسخ</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="admin-btn admin-btn--secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@endsection
