<!--
=========================================================
* Soft UI Dashboard - v1.0.3
=========================================================

* Product Page: https://www.creative-tim.com/product/soft-ui-dashboard
* Copyright 2021 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)

* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
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
    <title>
        تیکت ها
    </title>
</head>

<body class="g-sidenav-show rtl bg-gray-100">
<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-end me-3 rotate-caret"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute start-0 top-0 d-none d-xl-none"
           aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="https://demos.creative-tim.com/soft-ui-dashboard/pages/dashboard.html"
           target="_blank">
            <span class="me-1 font-weight-bold">پنل مدیریت</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse px-0 w-auto  max-height-vh-100 h-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            @can('read-user')
                <li class="nav-item">
                    <a class="nav-link " href="{{ route('admin.users') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center ms-2 d-flex align-items-center justify-content-center">
                            <svg width="12px" height="12px" viewBox="0 0 42 42" version="1.1"
                                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>office</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-1869.000000, -293.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(153.000000, 2.000000)">
                                                <path class="color-background opacity-6"
                                                      d="M12.25,17.5 L8.75,17.5 L8.75,1.75 C8.75,0.78225 9.53225,0 10.5,0 L31.5,0 C32.46775,0 33.25,0.78225 33.25,1.75 L33.25,12.25 L29.75,12.25 L29.75,3.5 L12.25,3.5 L12.25,17.5 Z"></path>
                                                <path class="color-background"
                                                      d="M40.25,14 L24.5,14 C23.53225,14 22.75,14.78225 22.75,15.75 L22.75,38.5 L19.25,38.5 L19.25,22.75 C19.25,21.78225 18.46775,21 17.5,21 L1.75,21 C0.78225,21 0,21.78225 0,22.75 L0,40.25 C0,41.21775 0.78225,42 1.75,42 L40.25,42 C41.21775,42 42,41.21775 42,40.25 L42,15.75 C42,14.78225 41.21775,14 40.25,14 Z M12.25,36.75 L7,36.75 L7,33.25 L12.25,33.25 L12.25,36.75 Z M12.25,29.75 L7,29.75 L7,26.25 L12.25,26.25 L12.25,29.75 Z M35,36.75 L29.75,36.75 L29.75,33.25 L35,33.25 L35,36.75 Z M35,29.75 L29.75,29.75 L29.75,26.25 L35,26.25 L35,29.75 Z M35,22.75 L29.75,22.75 L29.75,19.25 L35,19.25 L35,22.75 Z"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <span class="nav-link-text me-1">مدیریت کاربران</span>
                    </a>
                </li>
            @endcan
            @can('read-role')
                <li class="nav-item">
                    <a class="nav-link " href="{{ route('admin.roles') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center ms-2 d-flex align-items-center justify-content-center">
                            <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1"
                                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>credit-card</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(453.000000, 454.000000)">
                                                <path class="color-background opacity-6"
                                                      d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z"></path>
                                                <path class="color-background"
                                                      d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <span class="nav-link-text me-1">مدیریت نقش ها</span>
                    </a>
                </li>
            @endcan
            @can('read-ticket')
                <li class="nav-item">
                    <a class="nav-link " href="{{ route('admin.tickets') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center ms-2 d-flex align-items-center justify-content-center">
                            <svg width="12px" height="12px" viewBox="0 0 42 42" version="1.1"
                                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>box-3d-50</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-2319.000000, -291.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(603.000000, 0.000000)">
                                                <path class="color-background"
                                                      d="M22.7597136,19.3090182 L38.8987031,11.2395234 C39.3926816,10.9925342 39.592906,10.3918611 39.3459167,9.89788265 C39.249157,9.70436312 39.0922432,9.5474453 38.8987261,9.45068056 L20.2741875,0.1378125 L20.2741875,0.1378125 C19.905375,-0.04725 19.469625,-0.04725 19.0995,0.1378125 L3.1011696,8.13815822 C2.60720568,8.38517662 2.40701679,8.98586148 2.6540352,9.4798254 C2.75080129,9.67332903 2.90771305,9.83023153 3.10122239,9.9269862 L21.8652864,19.3090182 C22.1468139,19.4497819 22.4781861,19.4497819 22.7597136,19.3090182 Z"></path>
                                                <path class="color-background opacity-6"
                                                      d="M23.625,22.429159 L23.625,39.8805372 C23.625,40.4328219 24.0727153,40.8805372 24.625,40.8805372 C24.7802551,40.8805372 24.9333778,40.8443874 25.0722402,40.7749511 L41.2741875,32.673375 L41.2741875,32.673375 C41.719125,32.4515625 42,31.9974375 42,31.5 L42,14.241659 C42,13.6893742 41.5522847,13.241659 41,13.241659 C40.8447549,13.241659 40.6916418,13.2778041 40.5527864,13.3472318 L24.1777864,21.5347318 C23.8390024,21.7041238 23.625,22.0503869 23.625,22.429159 Z"></path>
                                                <path class="color-background opacity-6"
                                                      d="M20.4472136,21.5347318 L1.4472136,12.0347318 C0.953235098,11.7877425 0.352562058,11.9879669 0.105572809,12.4819454 C0.0361450918,12.6208008 6.47121774e-16,12.7739139 0,12.929159 L0,30.1875 L0,30.1875 C0,30.6849375 0.280875,31.1390625 0.7258125,31.3621875 L19.5528096,40.7750766 C20.0467945,41.0220531 20.6474623,40.8218132 20.8944388,40.3278283 C20.963859,40.1889789 21,40.0358742 21,39.8806379 L21,22.429159 C21,22.0503869 20.7859976,21.7041238 20.4472136,21.5347318 Z"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <span class="nav-link-text me-1">مدیریت تیکت ها</span>
                    </a>
                </li>
            @endcan
            @can('read-chat')

                <li class="nav-item">
                    <a class="nav-link " href="{{ route('admin.chats') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center ms-2 d-flex align-items-center justify-content-center">
                            <svg width="12px" height="12px" viewBox="0 0 42 42" version="1.1"
                                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>box-3d-50</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-2319.000000, -291.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(603.000000, 0.000000)">
                                                <path class="color-background"
                                                      d="M22.7597136,19.3090182 L38.8987031,11.2395234 C39.3926816,10.9925342 39.592906,10.3918611 39.3459167,9.89788265 C39.249157,9.70436312 39.0922432,9.5474453 38.8987261,9.45068056 L20.2741875,0.1378125 L20.2741875,0.1378125 C19.905375,-0.04725 19.469625,-0.04725 19.0995,0.1378125 L3.1011696,8.13815822 C2.60720568,8.38517662 2.40701679,8.98586148 2.6540352,9.4798254 C2.75080129,9.67332903 2.90771305,9.83023153 3.10122239,9.9269862 L21.8652864,19.3090182 C22.1468139,19.4497819 22.4781861,19.4497819 22.7597136,19.3090182 Z"></path>
                                                <path class="color-background opacity-6"
                                                      d="M23.625,22.429159 L23.625,39.8805372 C23.625,40.4328219 24.0727153,40.8805372 24.625,40.8805372 C24.7802551,40.8805372 24.9333778,40.8443874 25.0722402,40.7749511 L41.2741875,32.673375 L41.2741875,32.673375 C41.719125,32.4515625 42,31.9974375 42,31.5 L42,14.241659 C42,13.6893742 41.5522847,13.241659 41,13.241659 C40.8447549,13.241659 40.6916418,13.2778041 40.5527864,13.3472318 L24.1777864,21.5347318 C23.8390024,21.7041238 23.625,22.0503869 23.625,22.429159 Z"></path>
                                                <path class="color-background opacity-6"
                                                      d="M20.4472136,21.5347318 L1.4472136,12.0347318 C0.953235098,11.7877425 0.352562058,11.9879669 0.105572809,12.4819454 C0.0361450918,12.6208008 6.47121774e-16,12.7739139 0,12.929159 L0,30.1875 L0,30.1875 C0,30.6849375 0.280875,31.1390625 0.7258125,31.3621875 L19.5528096,40.7750766 C20.0467945,41.0220531 20.6474623,40.8218132 20.8944388,40.3278283 C20.963859,40.1889789 21,40.0358742 21,39.8806379 L21,22.429159 C21,22.0503869 20.7859976,21.7041238 20.4472136,21.5347318 Z"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <span class="nav-link-text me-1">مدیریت چت ها</span>
                    </a>
                </li>
            @endcan
            <hr class="horizontal dark mt-0">

            <li class="nav-item">
                <a class="nav-link " href="{{ route('profile') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center ms-2 d-flex align-items-center justify-content-center">
                        <svg width="12px" height="12px" viewBox="0 0 46 42" version="1.1"
                             xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <title>customer-support</title>
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g transform="translate(-1717.000000, -291.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                    <g transform="translate(1716.000000, 291.000000)">
                                        <g transform="translate(1.000000, 0.000000)">
                                            <path class="color-background opacity-6"
                                                  d="M45,0 L26,0 C25.447,0 25,0.447 25,1 L25,20 C25,20.379 25.214,20.725 25.553,20.895 C25.694,20.965 25.848,21 26,21 C26.212,21 26.424,20.933 26.6,20.8 L34.333,15 L45,15 C45.553,15 46,14.553 46,14 L46,1 C46,0.447 45.553,0 45,0 Z"></path>
                                            <path class="color-background"
                                                  d="M22.883,32.86 C20.761,32.012 17.324,31 13,31 C8.676,31 5.239,32.012 3.116,32.86 C1.224,33.619 0,35.438 0,37.494 L0,41 C0,41.553 0.447,42 1,42 L25,42 C25.553,42 26,41.553 26,41 L26,37.494 C26,35.438 24.776,33.619 22.883,32.86 Z"></path>
                                            <path class="color-background"
                                                  d="M13,28 C17.432,28 21,22.529 21,18 C21,13.589 17.411,10 13,10 C8.589,10 5,13.589 5,18 C5,22.529 8.568,28 13,28 Z"></path>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <span class="nav-link-text me-1">حساب کاربری</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="logout-link">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center ms-2 d-flex align-items-center justify-content-center">
                        <svg width="12px" height="12px" viewBox="0 0 40 44" version="1.1"
                             xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <title>document</title>
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g transform="translate(-1870.000000, -591.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                    <g transform="translate(1716.000000, 291.000000)">
                                        <g transform="translate(154.000000, 300.000000)">
                                            <path class="color-background opacity-6"
                                                  d="M40,40 L36.3636364,40 L36.3636364,3.63636364 L5.45454545,3.63636364 L5.45454545,0 L38.1818182,0 C39.1854545,0 40,0.814545455 40,1.81818182 L40,40 Z"></path>
                                            <path class="color-background"
                                                  d="M30.9090909,7.27272727 L1.81818182,7.27272727 C0.814545455,7.27272727 0,8.08727273 0,9.09090909 L0,41.8181818 C0,42.8218182 0.814545455,43.6363636 1.81818182,43.6363636 L30.9090909,43.6363636 C31.9127273,43.6363636 32.7272727,42.8218182 32.7272727,41.8181818 L32.7272727,9.09090909 C32.7272727,8.08727273 31.9127273,7.27272727 30.9090909,7.27272727 Z M18.1818182,34.5454545 L7.27272727,34.5454545 L7.27272727,30.9090909 L18.1818182,30.9090909 L18.1818182,34.5454545 Z M25.4545455,27.2727273 L7.27272727,27.2727273 L7.27272727,23.6363636 L25.4545455,23.6363636 L25.4545455,27.2727273 Z M25.4545455,20 L7.27272727,20 L7.27272727,16.3636364 L25.4545455,16.3636364 L25.4545455,20 Z"></path>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <span class="nav-link-text me-1">خروج</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const link = document.getElementById('logout-link');
                    if (link) {
                        link.addEventListener('click', function (e) {
                            e.preventDefault();
                            document.getElementById('logout-form').submit();
                        });
                    }
                });
            </script>
        </ul>
    </div>
</aside>

<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg ">
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
</main>
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
</body>
</html>
