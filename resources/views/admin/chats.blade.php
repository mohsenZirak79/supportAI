@extends('admin.layouts.master')

@section('title', 'چت ها')

@push('styles')
    <style>
        .voice-card { background: #f6f9fc; border: 1px solid #e9ecef; border-radius: 12px; padding: 10px 12px; }
        .voice-card__header { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
        .voice-title { font-weight: 600; color: #0f172a; }
        .voice-title .mic { opacity: .8; }
        .voice-time { font-size: 12px; color: #64748b; }
        .voice-card__body { background: #fff; border: 1px solid #eef2f7; border-radius: 10px; padding: 8px; }
        .voice-card audio { width: 100%; outline: none; }
        .voice-card__footer { margin-top: 6px; text-align: left; }
        .voice-card .download-link { font-size: 12px; color: #2563eb; text-decoration: none; }
        .voice-card .download-link:hover { text-decoration: underline; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const titleEl = document.getElementById('convModalLabel')
            const msgList = document.getElementById('msgList')
            const refList = document.getElementById('refList')
            const convMeta = document.getElementById('convMeta')

            let currentConvBtn = null;
            document.querySelectorAll('.btn-view-conv').forEach(btn => {
                btn.addEventListener('click', () => { currentConvBtn = btn; openConversation(btn); })
            });
            document.getElementById('convModal').addEventListener('click', function(e){
                var retryBtn = e.target.closest('.admin-retry-btn[data-retry-conv]');
                if (retryBtn && currentConvBtn) openConversation(currentConvBtn);
            });

            async function openConversation(btn) {
                const url = btn.getAttribute('data-url')
                const title = btn.getAttribute('data-title') || 'مکالمه'
                const convId = btn.getAttribute('data-conv')

                titleEl.textContent = 'جزئیات مکالمه'
                convMeta.innerHTML = ''
                msgList.innerHTML = '<div class="detail-messages__loading">در حال بارگذاری…</div>'
                refList.innerHTML = ''

                try {
                    const res = await fetch(url, {headers: {'Accept': 'application/json'}})
                    if (!res.ok) {
                        const t = await res.text().catch(() => '')
                        console.error('detail fetch failed', res.status, t)
                        window.toast?.error('خطا در بارگذاری جزئیات')
                        throw new Error('LOAD_FAILED')
                    }
                    renderAll(await res.json())
                } catch (e) {
                    msgList.innerHTML = '<div class="admin-error-box"><span class="admin-error-box__msg">خطا در بارگذاری مکالمه.</span><button type="button" class="admin-retry-btn" data-retry-conv="">تلاش مجدد</button></div>'
                }
            }

            function voiceCardHtml({url, mime, title, created_at}) {
                const downloadName = (title || 'voice') + guessExtFromMime(mime);
                return `<div class="voice-card">
                          <div class="voice-card__header">
                            <span class="voice-title">${escapeHtml(title || 'پیام صوتی')}</span>
                            <span class="voice-time">${fmtDate(created_at)}</span>
                          </div>
                          <div class="voice-card__body">
                            <audio controls preload="metadata" class="voice-audio" onplay="pauseSiblings(this)">
                              <source src="${url}" type="${mime}">
                              مرورگر شما از پخش این فرمت پشتیبانی نمی‌کند.
                            </audio>
                          </div>
                          <div class="voice-card__footer">
                            <a href="${url}" download="${downloadName}" class="download-link">دانلود فایل</a>
                          </div>
                        </div>`;
            }

            function guessExtFromMime(mime) {
                if (!mime) return '.webm';
                if (mime.includes('mpeg')) return '.mp3';
                if (mime.includes('ogg')) return '.ogg';
                if (mime.includes('wav')) return '.wav';
                return '.webm';
            }

            window.pauseSiblings = function (audioEl) {
                document.querySelectorAll('.voice-audio').forEach(a => {
                    if (a !== audioEl && !a.paused) a.pause();
                });
            };

            function extToMime(ext) {
                switch (ext) {
                    case '.mp3': return 'audio/mpeg';
                    case '.ogg': return 'audio/ogg';
                    case '.wav': return 'audio/wav';
                    case '.m4a': return 'audio/mp4';
                    case '.aac': return 'audio/aac';
                    case '.webm':return 'audio/webm';
                    default:     return '';
                }
            }

            function renderAll(data) {
                const convTitle = data.conversation?.title || '-';
                const userName = data.conversation?.user?.name || '-';
                convMeta.innerHTML = `
                <div class="detail-meta">
                    <h6 class="detail-meta__title">${escapeHtml(convTitle)}</h6>
                    <div class="detail-meta__row"><span class="detail-meta__label">کاربر:</span><span class="detail-meta__value">${escapeHtml(userName)}</span></div>
                </div>`;

                msgList.innerHTML = '';
                (data.messages || []).forEach(m => {
                    const isAi = m.sender_type === 'ai';
                    const side = isAi ? 'end' : 'start';
                    const bubbleClass = isAi ? 'detail-bubble--agent' : 'detail-bubble--user';
                    const senderLabel = isAi ? 'هوش مصنوعی' : 'کاربر';
                    const item = document.createElement('div');
                    item.className = 'list-group-item border-0 px-0';
                    item.id = 'msg-' + m.id;

                    let mediaHtml = '';
                    const media = m.media || [];
                    const voices = [];
                    const files  = [];

                    media.forEach(mm => {
                        const mime = (mm.mime || '').toLowerCase();
                        const url  = (mm.url  || '').toString();
                        const clean = url.split('?')[0].split('#')[0];
                        const dot   = clean.lastIndexOf('.');
                        const ext   = dot >= 0 ? clean.slice(dot).toLowerCase() : '';
                        const isAudioByMime = mime.startsWith('audio/');
                        const isAudioByExt  = ['.webm','.mp3','.ogg','.wav','.m4a','.aac'].includes(ext);
                        if (isAudioByMime || isAudioByExt) {
                            voices.push({ ...mm, _mime: mime || extToMime(ext) || 'audio/webm' });
                        } else {
                            files.push(mm);
                        }
                    });

                    if (voices.length) {
                        mediaHtml += '<div class="mt-2 d-flex flex-column gap-2">';
                        voices.forEach(v => {
                            mediaHtml += voiceCardHtml({
                                url: v.url,
                                mime: v._mime,
                                title: 'پیام صوتی',
                                created_at: m.created_at
                            });
                        });
                        mediaHtml += '</div>';
                    }
                    if (files.length) {
                        mediaHtml += '<div class="detail-attachments mt-2">';
                        files.forEach((f, idx) => {
                            mediaHtml += `<a href="${f.url}" class="detail-attachment-link" target="_blank" rel="noopener"><span class="truncate">${escapeHtml(f.name || ('file' + (idx + 1)))}</span></a>`;
                        });
                        mediaHtml += '</div>';
                    }

                    item.innerHTML = `
          <div class="d-flex justify-content-${side}">
            <div class="detail-bubble ${bubbleClass}" style="max-width: 90%;">
              <div class="detail-bubble__sender">${senderLabel}</div>
              <div>${escapeHtml(m.content || '')}</div>
              ${mediaHtml}
              <div class="detail-bubble__time">${fmtDate(m.created_at)}</div>
            </div>
          </div>
        `;
                    msgList.appendChild(item);
                });

                renderReferrals(data);
            }

            function renderReferrals(data) {
                const conversationId = data.conversation?.id
                const refs = (data.referrals || []).slice().sort((a, b) =>
                    new Date(a.created_at || 0) - new Date(b.created_at || 0)
                )

                if (refs.length === 0) {
                    refList.innerHTML = '<div class="conv-detail__empty">ارجاعی ثبت نشده است.</div>'
                    return
                }

                refList.innerHTML = ''
                refs.forEach(r => {
                    const card = document.createElement('div')
                    card.className = 'card detail-referral-card'
                    const assignBtnHtml = '';

                    let refFilesHtml = ''
                    if ((r.files || []).length) {
                        refFilesHtml = '<div class="mt-2 small">فایل‌های پیوست پشتیبان: '
                        r.files.forEach((f, idx) => {
                            refFilesHtml += `<a href="${f.url}" target="_blank" rel="noopener" class="me-2">${escapeHtml(f.name || ('file' + (idx + 1)))}</a>`
                        })
                        refFilesHtml += '</div>'
                    }

                    const respondFormHtml = (!r.agent_response && r.can_respond) ? `
            <form class="mt-3 referral-reply-form admin-form" data-ajax-form="1" data-ref="${r.id}">
              <div class="mb-3">
                <label class="form-label">پاسخ شما</label>
                <textarea name="agent_response" class="form-control" rows="3" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label d-block">فایل‌های پیوست (اختیاری)</label>
                <input type="file" name="files[]" class="form-control form-control-sm" multiple />
                <div class="form-text">PDF, تصویر، صوت و … (حداکثر 20MB برای هر فایل)</div>
              </div>
              <div class="mb-3">
                <label class="form-label">نوع پاسخ</label>
                <select name="response_visibility" class="form-select form-select-sm">
                  <option value="public" selected>نمایش برای کاربر</option>
                  <option value="internal">فقط داخلی</option>
                </select>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" class="admin-btn admin-btn--primary">ثبت پاسخ</button>
                ${assignBtnHtml}
              </div>
            </form>
          ` : `<div class="d-flex gap-2">${assignBtnHtml}</div>`

                    card.innerHTML = `
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>ارجاع به: ${escapeHtml(r.assigned_role || '-')}</strong>
                <span class="badge bg-${badgeColor(r.status)}">${r.status}</span>
              </div>
              <div class="mb-2">
                <div class="text-muted small mb-1">توضیحات کاربر:</div>
                <div>${escapeHtml(r.description || '-')}</div>
              </div>
              ${r.agent_response ? `
                <div class="mt-3 p-2 bg-light rounded">
                  <div class="text-muted small mb-1">پاسخ پشتیبان:</div>
                  <div>${escapeHtml(r.agent_response)}</div>
                  ${refFilesHtml}
                </div>
              ` : ''}
              ${respondFormHtml}
            </div>
          `
                    refList.appendChild(card)

                    if (r.trigger_message_id) {
                        const el = document.getElementById('msg-' + r.trigger_message_id)
                        if (el) {
                            el.style.outline = '2px solid #0d6efd'
                            el.scrollIntoView({behavior: 'smooth', block: 'center'})
                            setTimeout(() => el.style.outline = 'none', 2000)
                        }
                    }
                })

                refList.querySelectorAll('.referral-reply-form').forEach(form => {
                    form.addEventListener('submit', (e) => onSubmitResponse(e, conversationId))
                })
                refList.querySelectorAll('.assign-me-btn').forEach(btn => {
                    btn.addEventListener('click', () => onAssignMe(btn, conversationId))
                })
            }

            async function onAssignMe(btn, conversationId) {
                const refId = btn.getAttribute('data-ref')
                const old = btn.innerText
                btn.disabled = true;
                btn.innerText = 'در حال تخصیص...'
                try {
                    const resp = await fetch(`/admin/referrals/${refId}/assign-me`, {
                        method: 'POST',
                        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken()}
                    })
                    if (!resp.ok) {
                        window.toast?.error('خطا در تخصیص')
                        btn.disabled = false;
                        btn.innerText = old
                        return
                    }
                    window.toast?.success('برای شما تخصیص داده شد.')
                    await reloadDetails(conversationId)
                } catch (e) {
                    window.toast?.error('خطای شبکه در تخصیص')
                    btn.disabled = false;
                    btn.innerText = old
                }
            }

            async function onSubmitResponse(e, conversationId) {
                e.preventDefault()
                const form = e.currentTarget
                const refId = form.getAttribute('data-ref')
                const btn = form.querySelector('button[type="submit"]')
                const old = btn.innerText
                btn.disabled = true;
                btn.innerText = 'در حال ثبت...'
                try {
                    const fd = new FormData(form)
                    const resp = await fetch(`/admin/referrals/${refId}/respond`, {
                        method: 'POST',
                        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken()},
                        body: fd
                    })
                    if (!resp.ok) {
                        window.toast?.error('ثبت پاسخ ناموفق بود.')
                        btn.disabled = false;
                        btn.innerText = old
                        return
                    }
                    window.toast?.success('پاسخ ثبت شد.')
                    await reloadDetails(conversationId)
                } catch (err) {
                    window.toast?.error('خطای شبکه در ثبت پاسخ')
                    btn.disabled = false;
                    btn.innerText = old
                }
            }

            async function reloadDetails(conversationId) {
                const btn = document.querySelector(`.btn-view-conv[data-conv="${conversationId}"]`)
                const url = btn ? btn.getAttribute('data-url') : `/admin/chats/${conversationId}/detail`
                const res = await fetch(url, {headers: {'Accept': 'application/json'}})
                const data = await res.json()
                renderAll(data)
            }

            function badgeColor(status) {
                switch (status) {
                    case'pending': return 'warning';
                    case'assigned': return 'info';
                    case'responded': return 'success';
                    case'closed': return 'secondary';
                    default: return 'light'
                }
            }

            function escapeHtml(s) {
                return (s || '').replace(/[&<>"']/g, m => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                }[m]))
            }

            function fmtDate(iso) {
                try { return new Date(iso).toLocaleString('fa-IR') } catch { return iso || '' }
            }

            function getCsrfToken() {
                const el = document.querySelector('meta[name="csrf-token"]');
                return el ? el.getAttribute('content') : '{{ csrf_token() }}'
            }
        })();
    </script>
@endpush

@section('content')
<div class="list-page">
    <header class="list-page__header">
        <div>
            <h1>گفت‌وگوها</h1>
            <p class="list-page__subtitle">مکالمات چت و ارجاع‌ها</p>
        </div>
        <div class="list-page__actions"></div>
    </header>

    <div class="list-page__card">
        <div class="table-responsive">
            <table class="list-page__table">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>نام ارسال‌کننده</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $chat)
                        <tr>
                            <td>{{ $chat->title }}</td>
                            <td>{{ $chat->user->name ?? '-' }}</td>
                            <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($chat->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <button
                                    class="list-page__btn-view btn-view-conv"
                                    data-title="{{ $chat->title }}"
                                    data-url="{{ route('admin.chats.detail', $chat->id) }}"
                                    data-conv="{{ $chat->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#convModal"
                                    type="button">
                                    مشاهده
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="list-page__empty">هیچ موردی یافت نشد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $conversations])
    </div>
</div>

<div class="modal fade admin-modal" id="convModal" tabindex="-1" aria-labelledby="convModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="max-height: 90vh;">
            <div class="modal-header">
                <h5 class="modal-title" id="convModalLabel">مکالمه</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body conv-detail-body">
                <div id="convMeta" class="conv-detail__meta"></div>
                <section class="conv-detail__section">
                    <h6 class="conv-detail__section-title">پیام‌ها</h6>
                    <div id="msgList" class="detail-messages"></div>
                </section>
                <section class="conv-detail__section">
                    <h6 class="conv-detail__section-title">ارجاع‌ها</h6>
                    <div id="refList" class="conv-detail__refs"></div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="admin-btn admin-btn--secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@endsection
