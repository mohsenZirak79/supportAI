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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-3 shadow-sm border-0" role="alert">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-exclamation-circle fa-lg me-2"></i>
                <strong>خطا در ورود اطلاعات:</strong>
            </div>
            <ul class="mb-0 ps-4">
                @foreach ($errors->all() as $error)
                    <li class="mb-1">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif
    <!-- Navbar -->
    <!-- End Navbar -->
    <div class="container-fluid py-4">
        <div class="container-fluid py-4">
            <div class="card">

                <div class="card-body px-25 pt-0 pb-2">
                    <h6 style="text-align: center; margin-top: 20px">لیست کاربران</h6>

                    <div class="table-responsive p-0">

                        <table class="table align-items-center mb-0 datatable">

                            <thead>

                            <tr>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    نام و کد ملی
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    نقش
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    تلفن همراه
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    ایمیل
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    تاریخ شروع
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    عملیات
                                </th>
                            </tr>
                            </thead>
                            <tbody>


                            <button class="btn btn-add-user" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-user-plus me-2"></i> افزودن کاربر
                            </button>

                            @foreach($users as $user)
                                <tr>
                                    <td class="text-center">
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-0 text-sm">{{ $user->name }} {{ $user->family }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $user->national_id }}</p>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $user->roles->pluck('name')->join(', ') }}
                                        </p>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $user->phone }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $user->email }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="text-secondary text-xs font-weight-bold">{{ $user->created_at_jalali }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn-action edit" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                            <i class="fas fa-edit"></i> ویرایش
                                        </button>

                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;"
                                              onsubmit="return confirm('آیا از حذف این کاربر مطمئن هستید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action delete">
                                                <i class="fas fa-trash-alt"></i> حذف
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                                <!-- Modal ویرایش -->
                                <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1"
                                     aria-labelledby="editUserModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editUserModalLabel">ویرایش کاربر</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="بستن"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="{{ route('users.update', $user->id) }}">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">نام</label>
                                                            <input type="text" name="name" class="form-control"
                                                                   value="{{ $user->name }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">نام خانوادگی</label>
                                                            <input type="text" name="family" class="form-control"
                                                                   value="{{ $user->family }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">کد ملی</label>
                                                            <input type="text" name="national_id" class="form-control"
                                                                   value="{{ $user->national_id }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-1" dir="rtl">
                                                            <label for="birth_date_{{ $user->id }}" class="form-label">تاریخ
                                                                تولد </label>
                                                            <input
                                                                id="birth_date_{{ $user->id }}"
                                                                name="birth_date"
                                                                type="text"
                                                                class="form-control"
                                                                value="{{ $user->birth_date }}"
                                                                autocomplete="off"
                                                                dir="ltr"
                                                                data-jdp
                                                                data-jdp-only-date="true"
                                                                data-jdp-config='{"dateFormat":"YYYY/MM/DD","autoShow":true}'
                                                            >
                                                        </div>

                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">ایمیل</label>
                                                            <input type="text" name="email" class="form-control"
                                                                   value="{{ $user->email }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">شماره تلفن</label>
                                                            <input type="number" name="phone" class="form-control"
                                                                   value="{{ $user->phone }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">کد پستی</label>
                                                            <input type="number" name="postal_code" class="form-control"
                                                                   value="{{ $user->postal_code }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">آدرس</label>
                                                            <input type="text" name="address"
                                                                   value="{{ $user->address }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="role" class="form-label">نقش کاربر</label>
                                                        <select name="role" id="role" class="form-select" required>
                                                            <option value="" disabled selected>انتخاب نقش...</option>
                                                            @foreach($roles as $role)
                                                                <option
                                                                    value="{{ $role->id }}">{{ $role->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                                <!-- Modal افزودن کاربر -->
                                <div class="modal fade" id="addUserModal" tabindex="-1"
                                     aria-labelledby="addUserModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="addUserModalLabel">افزودن کاربر جدید</h5>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('users.store') }}" method="POST">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">نام</label>
                                                            <input type="text" name="name" class="form-control"
                                                                   required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">نام خانوادگی</label>
                                                            <input type="text" name="family" class="form-control"
                                                                   required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">کد ملی</label>
                                                            <input type="text" name="national_id" class="form-control"
                                                                   required>
                                                        </div>
                                                        <div class="col-md-6 mb-1" dir="rtl">
                                                            <label for="birth_date" class="form-label">تاریخ تولد
                                                                </label>
                                                            <input
                                                                id="birth_date"
                                                                name="birth_date"
                                                                type="text"
                                                                class="form-control"
                                                                autocomplete="off"
                                                                dir="ltr"
                                                                data-jdp
                                                                data-jdp-only-date="true"
                                                                data-jdp-config='{"selector":"#birth_date","dateFormat":"YYYY/MM/DD","autoShow":true}'
                                                            >
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">ایمیل</label>
                                                            <input type="text" name="email" class="form-control"
                                                                   required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">شماره تلفن</label>
                                                            <input type="number" name="phone" class="form-control"
                                                                   required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">رمز عبور</label>
                                                            <input type="password" name="password" class="form-control"
                                                                   required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">تکرار رمز عبور</label>
                                                            <input type="password" name="password_confirmation"
                                                                   class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">کد پستی</label>
                                                            <input type="number" name="postal_code" class="form-control"
                                                                   required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">آدرس</label>
                                                            <input type="text" name="address"
                                                                   class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="role" class="form-label">نقش کاربر</label>
                                                        <select name="role" id="role" class="form-select" required>
                                                            <option value="" disabled selected>انتخاب نقش...</option>
                                                            @foreach($roles as $role)
                                                                <option
                                                                    value="{{ $role->id }}">{{ $role->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary">ثبت</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
@endsection
