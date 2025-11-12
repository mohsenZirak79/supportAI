@extends('admin.layouts.master')

@section('title', 'چت ها')

@push('styles')
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <style>
            .voice-card {
                background: #f6f9fc;
                border: 1px solid #e9ecef;
                border-radius: 12px;
                padding: 10px 12px;
            }

            .voice-card__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                margin-bottom: 8px;
            }

            .voice-title {
                font-weight: 600;
                color: #0f172a; /* slate-900 */
            }

            .voice-title .mic { opacity: .8; }

            .voice-time {
                font-size: 12px;
                color: #64748b; /* slate-500 */
            }

            .voice-card__body {
                background: #fff;
                border: 1px solid #eef2f7;
                border-radius: 10px;
                padding: 8px;
            }

            .voice-card audio {
                width: 100%;
                outline: none;
            }

            .voice-card__footer {
                margin-top: 6px;
                text-align: left;
            }

            .voice-card .download-link {
                font-size: 12px;
                color: #2563eb; /* indigo-600 */
                text-decoration: none;
            }

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

            document.querySelectorAll('.btn-view-conv').forEach(btn => {
                btn.addEventListener('click', () => openConversation(btn))
            })

            async function openConversation(btn) {
                const url = btn.getAttribute('data-url')
                const title = btn.getAttribute('data-title') || 'مکالمه'
                const convId = btn.getAttribute('data-conv')

                titleEl.textContent = title
                convMeta.innerHTML = ''
                msgList.innerHTML = '<div class="text-center text-muted py-2">در حال بارگذاری…</div>'
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
                    msgList.innerHTML = '<div class="text-danger">خطا در بارگذاری مکالمه.</div>'
                }
            }

            function voiceCardHtml({url, mime, title, created_at}) {
                // اگر مرورگر نتواند پخش کند، کاربر هنوز می‌تواند دانلود کند
                const downloadName = (title || 'voice') + guessExtFromMime(mime);
                return `<div class="voice-card">
                          <div class="voice-card__header">
                            <span class="voice-title">${escapeHtml(title || 'پیام صوتی')} <span class="mic">🎙️</span></span>
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

    // فقط یک پلیر در آنِ واحد پخش شود
            window.pauseSiblings = function (audioEl) {
                document.querySelectorAll('.voice-audio').forEach(a => {
                    if (a !== audioEl && !a.paused) a.pause();
                });
            };

        //     function renderAll(data) {
        //         convMeta.innerHTML = `
        //   <div>کاربر: <strong>${escapeHtml(data.conversation?.user?.name || '-')}</strong></div>
        //   <div>عنوان: <span>${escapeHtml(data.conversation?.title || '-')}</span></div>
        // `
        //
        //         // پیام‌ها + ویس/فایل‌ها
        //         msgList.innerHTML = ''
        //         ;(data.messages || []).forEach(m => {
        //             const side = m.sender_type === 'ai' ? 'end' : 'start'
        //             const item = document.createElement('div')
        //             item.className = 'list-group-item'
        //             item.id = 'msg-' + m.id
        //
        //             let mediaHtml = '';
        //             const media = m.media || [];
        //             const voices = media.filter(mm => (mm.mime || '').startsWith('audio/'));
        //             const files = media.filter(mm => !((mm.mime || '').startsWith('audio/')));
        //
        //             if (voices.length) {
        //                 mediaHtml += '<div class="mt-2 d-flex flex-column gap-2">';
        //                 voices.forEach(v => {
        //                     mediaHtml += voiceCardHtml({
        //                         url: v.url,
        //                         mime: v.mime || 'audio/webm',
        //                         title: 'پیام صوتی',
        //                         created_at: m.created_at
        //                     });
        //                 });
        //                 mediaHtml += '</div>';
        //             }
        //             if (files.length) {
        //                 mediaHtml += '<div class="mt-2 small">فایل‌ها: ';
        //                 files.forEach((f, idx) => {
        //                     mediaHtml += `<a href="${f.url}" class="me-2" target="_blank" rel="noopener">${escapeHtml(f.name || ('file' + (idx + 1)))}</a>`;
        //                 });
        //                 mediaHtml += '</div>';
        //             }
        //
        //             item.innerHTML = `
        //     <div class="d-flex justify-content-${side}">
        //       <div class="p-2 rounded ${side === 'end' ? 'bg-light' : 'bg-white'}" style="max-width: 90%;">
        //         <div class="small text-muted mb-1">${m.sender_type === 'ai' ? '🤖 هوش مصنوعی' : '👤 کاربر'}</div>
        //         <div>${escapeHtml(m.content || '')}</div>
        //         ${mediaHtml}
        //         <div class="mt-1 text-muted" style="font-size:12px">${fmtDate(m.created_at)}</div>
        //       </div>
        //     </div>
        //   `
        //             msgList.appendChild(item)
        //         })
        //
        //         renderReferrals(data)
        //     }
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
                // متادیتای مکالمه
                convMeta.innerHTML = `
        <div>کاربر: <strong>${escapeHtml(data.conversation?.user?.name || '-')}</strong></div>
        <div>عنوان: <span>${escapeHtml(data.conversation?.title || '-')}</span></div>
      `;

                // پیام‌ها + ویس/فایل‌ها
                msgList.innerHTML = '';
                (data.messages || []).forEach(m => {
                    const side = m.sender_type === 'ai' ? 'end' : 'start';
                    const item = document.createElement('div');
                    item.className = 'list-group-item';
                    item.id = 'msg-' + m.id;

                    let mediaHtml = '';
                    const media = m.media || [];

                    const voices = [];
                    const files  = [];

                    // تشخیص ویس بر اساس MIME یا پسوند فایل
                    media.forEach(mm => {
                        const mime = (mm.mime || '').toLowerCase();
                        const url  = (mm.url  || '').toString();

                        // ext مثل .webm یا .mp3
                        const clean = url.split('?')[0].split('#')[0];
                        const dot   = clean.lastIndexOf('.');
                        const ext   = dot >= 0 ? clean.slice(dot).toLowerCase() : '';

                        const isAudioByMime = mime.startsWith('audio/');
                        const isAudioByExt  = ['.webm','.mp3','.ogg','.wav','.m4a','.aac'].includes(ext);

                        if (isAudioByMime || isAudioByExt) {
                            voices.push({
                                ...mm,
                                _mime: mime || extToMime(ext) || 'audio/webm'
                            });
                        } else {
                            files.push(mm);
                        }
                    });

                    // کارت‌های ویس
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

                    // سایر فایل‌ها به‌صورت لینک
                    if (files.length) {
                        mediaHtml += '<div class="mt-2 small">فایل‌ها: ';
                        files.forEach((f, idx) => {
                            mediaHtml += `<a href="${f.url}" class="me-2" target="_blank" rel="noopener">${escapeHtml(f.name || ('file' + (idx + 1)))}</a>`;
                        });
                        mediaHtml += '</div>';
                    }

                    item.innerHTML = `
          <div class="d-flex justify-content-${side}">
            <div class="p-2 rounded ${side === 'end' ? 'bg-light' : 'bg-white'}" style="max-width: 90%;">
              <div class="small text-muted mb-1">${m.sender_type === 'ai' ? '🤖 هوش مصنوعی' : '👤 کاربر'}</div>
              <div>${escapeHtml(m.content || '')}</div>
              ${mediaHtml}
              <div class="mt-1 text-muted" style="font-size:12px">${fmtDate(m.created_at)}</div>
            </div>
          </div>
        `;
                    msgList.appendChild(item);
                });

                // ارجاع‌ها
                renderReferrals(data);
            }

            function renderReferrals(data) {
                refList.innerHTML = ''
                const conversationId = data.conversation?.id

                // داده سرور already asc است؛ اگر لازم شد دوباره sort:
                const refs = (data.referrals || []).slice().sort((a, b) =>
                    new Date(a.created_at || 0) - new Date(b.created_at || 0)
                )

                refs.forEach(r => {
                    const card = document.createElement('div')
                    card.className = 'card'

                    // دکمه تخصیص
                    const assignBtnHtml = (!r.assigned_agent_id && r.can_assign_me) ? `
            <button class="btn btn-sm btn-outline-primary assign-me-btn" data-ref="${r.id}">
              تخصیص به من
            </button>` : ''

                    // فایل‌های ارجاع (نمایش)
                    let refFilesHtml = ''
                    if ((r.files || []).length) {
                        refFilesHtml = '<div class="mt-2 small">فایل‌های پیوست پشتیبان: '
                        r.files.forEach((f, idx) => {
                            refFilesHtml += `<a href="${f.url}" target="_blank" rel="noopener" class="me-2">${escapeHtml(f.name || ('file' + (idx + 1)))}</a>`
                        })
                        refFilesHtml += '</div>'
                    }

                    // فرم پاسخ + فایل
                    const respondFormHtml = (!r.agent_response && r.can_respond) ? `
            <form class="mt-3 referral-reply-form" data-ref="${r.id}">
              <div class="mb-2">
                <label class="form-label small">پاسخ شما</label>
                <textarea name="agent_response" class="form-control" rows="3" required></textarea>
              </div>
              <div class="mb-2">
                <label class="form-label small d-block">فایل‌های پیوست (اختیاری)</label>
                <input type="file" name="files[]" class="form-control form-control-sm" multiple />
                <div class="form-text">PDF, تصویر، صوت و … (حداکثر 20MB برای هر فایل)</div>
              </div>
              <div class="mb-2">
                <label class="form-label small">نوع پاسخ</label>
                <select name="response_visibility" class="form-select form-select-sm">
                  <option value="public" selected>نمایش برای کاربر</option>
                  <option value="internal">فقط داخلی</option>
                </select>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success">ثبت پاسخ</button>
                ${assignBtnHtml}
              </div>
            </form>
          ` : `
            <div class="d-flex gap-2">
              ${assignBtnHtml}
            </div>
          `

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

                    // هایلایت پیام trigger
                    if (r.trigger_message_id) {
                        const el = document.getElementById('msg-' + r.trigger_message_id)
                        if (el) {
                            el.style.outline = '2px solid #0d6efd'
                            el.scrollIntoView({behavior: 'smooth', block: 'center'})
                            setTimeout(() => el.style.outline = 'none', 2000)
                        }
                    }
                })

                // لیسنرها
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
                        const t = await resp.text().catch(() => '')
                        console.error('assign failed', resp.status, t)
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
                    const fd = new FormData(form) // شامل متن + فایل‌ها + visibility
                    const resp = await fetch(`/admin/referrals/${refId}/respond`, {
                        method: 'POST',
                        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken()},
                        body: fd
                    })
                    if (!resp.ok) {
                        const t = await resp.text().catch(() => '')
                        console.error('respond failed', resp.status, t)
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

            // Helpers
            function badgeColor(status) {
                switch (status) {
                    case'pending':
                        return 'warning';
                    case'assigned':
                        return 'info';
                    case'responded':
                        return 'success';
                    case'closed':
                        return 'secondary';
                    default:
                        return 'light'
                }
            }

            function escapeHtml(s) {
                return (s || '').replace(/[&<>"']/g, m => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[m]))
            }

            function fmtDate(iso) {
                try {
                    return new Date(iso).toLocaleString('fa-IR')
                } catch {
                    return iso || ''
                }
            }

            function getCsrfToken() {
                const el = document.querySelector('meta[name="csrf-token"]');
                return el ? el.getAttribute('content') : '{{ csrf_token() }}'
            }
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
                    <h6>لیست مکالمات</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 datatable" id="example">
                            <thead>
                            <tr>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    عنوان
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    نام ارسال کننده
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    تاریخ ثبت
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    عملیات
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($conversations as $chat)
                                <tr>
                                    <td>{{ $chat->title }}</td>
                                    <td>{{ $chat->user->name }}</td>
                                    <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($chat->created_at)->format('Y/m/d') }}</td>
                                    <td class="text-center">
                                        <button
                                            class="btn btn-sm btn-primary btn-view-conv"
                                            data-title="{{ $chat->title }}"
                                            data-url="{{ route('admin.chats.detail', $chat->id) }}"
                                            data-conv="{{ $chat->id }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#convModal"
                                            type="button"
                                        >
                                            مشاهده
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="fixed-plugin">

    <div class="card shadow-lg ">
        <div class="card-header pb-0 pt-3 ">
            <div class="float-start">
                <h5 class="mt-3 mb-0">Soft UI Configurator</h5>
                <p>See our dashboard options.</p>
            </div>
            <div class="float-end mt-4">
                <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                    <i class="fa fa-close"></i>
                </button>
            </div>
            <!-- End Toggle Button -->
        </div>
        <hr class="horizontal dark my-1">
        <div class="card-body pt-sm-3 pt-0">
            <!-- Sidebar Backgrounds -->
            <div>
                <h6 class="mb-0">Sidebar Colors</h6>
            </div>
            <a href="javascript:void(0)" class="switch-trigger background-color">
                <div class="badge-colors my-2 text-start">
                    <span class="badge filter bg-gradient-primary active" data-color="primary"
                          onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-dark" data-color="dark" onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-success" data-color="success"
                          onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-warning" data-color="warning"
                          onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-danger" data-color="danger"
                          onclick="sidebarColor(this)"></span>
                </div>
            </a>
            <!-- Sidenav Type -->
            <div class="mt-3">
                <h6 class="mb-0">Sidenav Type</h6>
                <p class="text-sm">Choose between 2 different sidenav types.</p>
            </div>
            <div class="d-flex">
                <button class="btn bg-gradient-primary w-100 px-3 mb-2 active" data-class="bg-transparent"
                        onclick="sidebarType(this)">Transparent
                </button>
                <button class="btn bg-gradient-primary w-100 px-3 mb-2 ms-2" data-class="bg-white"
                        onclick="sidebarType(this)">White
                </button>
            </div>
            <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
            <!-- Navbar Fixed -->
            <div class="mt-3">
                <h6 class="mb-0">Navbar Fixed</h6>
            </div>
            <div class="form-check form-switch ps-0">
                <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed"
                       onclick="navbarFixed(this)">
            </div>
            <hr class="horizontal dark my-sm-4">
            <a class="btn bg-gradient-dark w-100" href="https://www.creative-tim.com/product/soft-ui-dashboard-pro">Free
                Download</a>
            <a class="btn btn-outline-dark w-100"
               href="https://www.creative-tim.com/learning-lab/bootstrap/license/soft-ui-dashboard">View
                documentation</a>
            <div class="w-100 text-center">
                <a class="github-button" href="https://github.com/creativetimofficial/soft-ui-dashboard"
                   data-icon="octicon-star" data-size="large" data-show-count="true"
                   aria-label="Star creativetimofficial/soft-ui-dashboard on GitHub">Star</a>
                <h6 class="mt-3">Thank you for sharing!</h6>
                <a href="https://twitter.com/intent/tweet?text=Check%20Soft%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard"
                   class="btn btn-dark mb-0 me-2" target="_blank">
                    <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/soft-ui-dashboard"
                   class="btn btn-dark mb-0 me-2" target="_blank">
                    <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="convModal" tabindex="-1" aria-labelledby="convModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="max-height: 90vh;">
            <div class="modal-header">
                <h5 class="modal-title" id="convModalLabel">مکالمه</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <div id="convMeta" class="mb-3 text-sm text-muted"></div>

                <div class="mb-4">
                    <h6 class="mb-2">پیام‌ها</h6>
                    <div id="msgList" class="list-group"
                         style="max-height: 45vh; overflow-y: auto; border:1px solid #eee;"></div>
                </div>

                <div class="mt-4">
                    <h6 class="mb-2">ارجاع‌ها</h6>
                    <div id="refList" class="d-flex flex-column gap-3"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@endsection
