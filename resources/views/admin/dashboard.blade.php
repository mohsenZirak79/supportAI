@extends('admin.layouts.master')

@section('title', 'لیست کاربران')

@push('styles')
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">
    <style>

            .modal,
            .modal .modal-dialog,
            .modal .modal-content,
            .modal .modal-body {
                overflow: visible !important;
            }

            .jdp-container, .jalali-datepicker {
                z-index: 200000 !important;
            }

            .modal, .modal .modal-dialog, .modal .modal-content, .modal .modal-body {
                overflow: visible !important;
            }
            /* دکمه افزودن کاربر */
            .btn-add-user {
                background: linear-gradient(135deg, #16a34a, #22c55e); /* سبز گرادیانی */
                color: #fff;
                font-weight: 600;
                font-size: 14px;
                border: none;
                border-radius: 10px;
                padding: 8px 20px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                box-shadow: 0 3px 8px rgba(34, 197, 94, 0.4);
                transition: all 0.25s ease-in-out;
            }

            /* حالت hover */
            .btn-add-user:hover {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                box-shadow: 0 5px 15px rgba(34, 197, 94, 0.6);
                transform: translateY(-2px);
            }

            /* حالت فعال یا فشرده */
            .btn-add-user:active {
                transform: scale(0.96);
                box-shadow: 0 2px 5px rgba(34, 197, 94, 0.3);
            }

            /* ==== دکمه‌های عملیات در جدول (ویرایش و حذف) ==== */
            .btn-action {
                border: none;
                border-radius: 8px;
                padding: 6px 14px;
                font-weight: 600;
                font-size: 13px;
                color: #fff;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.25s ease-in-out;
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            }

            /* 🎨 ویرایش (آبی ملایم با گرادیان) */
            .btn-action.edit {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                box-shadow: 0 3px 8px rgba(59, 130, 246, 0.4);
            }

            .btn-action.edit:hover {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(59, 130, 246, 0.6);
            }

            .btn-action.delete {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                box-shadow: 0 3px 8px rgba(239, 68, 68, 0.4);
            }

            .btn-action.delete:hover {
                background: linear-gradient(135deg, #dc2626, #b91c1c);
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(239, 68, 68, 0.6);
            }

            /* حالت فشرده شدن هنگام کلیک */
            .btn-action:active {
                transform: scale(0.96);
            }

        </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js"></script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jalaliDatepicker?.startWatch) {
                jalaliDatepicker.startWatch({
                    time: false,
                    container: 'body',
                    zIndex: 200000
                });

                document.addEventListener('shown.bs.modal', function () {
                    jalaliDatepicker.updateOptions({zIndex: 200000, container: 'body'});
                });
            }
        });

    </script>
@endpush

@section('content')
<section class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg ">
    <div class="container-fluid py-5">
        <div class="text-center mb-4">
            <span class="badge bg-gradient-primary rounded-pill px-3 py-2">پنل مدیریت • چت با هوش مصنوعی</span>
            <h2 class="fw-bolder mt-3">سلام {{ auth()->user()->name ?? 'دوست عزیز' }} 👋</h2>
            <p class="text-muted mb-0">
                به داشبورد خوش آمدی—همه‌چیز آماده‌ست تا گفت‌وگوهای هوشمند و پشتیبانی سریع شروع بشه.
            </p>
        </div>
        <div class="row justify-content-center g-3 g-lg-4 align-items-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius:20px;">
                    <img
                        src="{{ asset('images/dashboard-1.png') }}"
                        alt="رابط چت هوش مصنوعی روی موبایل"
                        class="img-fluid w-100"
                        style="border-top-left-radius:20px;border-top-right-radius:20px;"
                    >
                    <div class="card-body text-center">
                        <h6 class="mb-1">چت هوش مصنوعی روی موبایل</h6>
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
@endsection
