<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
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
    <title>
        چت ها
    </title>
</head>

<body class="g-sidenav-show rtl bg-gray-100">
<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-end me-3 rotate-caret"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute start-0 top-0 d-none d-xl-none"
           aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="#">
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
</main>


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

</body>
</html>
