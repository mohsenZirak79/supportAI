@extends('admin.layouts.master')

@section('title', 'نقش ها')

@push('styles')
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <style>
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

            /* 💥 حذف (قرمز با افکت هشدار و لطیف) */
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

            /* دکمه افزودن نقش */
            .btn-add-role {
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
            .btn-add-role:hover {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                box-shadow: 0 5px 15px rgba(34, 197, 94, 0.6);
                transform: translateY(-2px);
            }

            /* حالت فعال یا فشرده */
            .btn-add-role:active {
                transform: scale(0.96);
                box-shadow: 0 2px 5px rgba(34, 197, 94, 0.3);
            }

        </style>
@endpush

@section('content')
<section class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg ">
    <!-- Navbar -->
    <!-- End Navbar -->
    <div class="container-fluid py-4">
        <div class="container-fluid py-4">
            <div class="card">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>خطا در ارسال فرم:</strong>
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card-body px-25 pt-0 pb-2">
                    <h6 style="text-align: center; margin-top: 20px">لیست نقش ها</h6>
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 datatable">
                            <thead>
                            <tr>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    عنوان نقش
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    تعداد کاربران
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    ایجاد شده در تاریخ
                                </th>

                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    عملیات
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <button class="btn btn-add-role" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                <i class="fas fa-role-plus me-2"></i> افزودن نقش جدید
                            </button>
                            @foreach($roles as $role)
                                <tr>
                                    <td style="text-align: center">
                                        <h6 class="mb-0 text-sm">{{ $role->name }}</h6>
                                    </td>
                                    <td style="text-align: center">
                                        <span class="badge bg-primary">{{ $role->users_count }}</span>
                                    </td>

                                    <td style="text-align: center">
                                <span class="text-secondary text-xs font-weight-bold">

                                        {{ $role->created_at_jalali }}
                                </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- دکمه ویرایش -->
                                        <!-- دکمه ویرایش -->
                                        <button class="btn-action edit" data-bs-toggle="modal"
                                                data-bs-target="#editRoleModal{{ $role->id }}">
                                            <i class="fas fa-edit"></i> ویرایش
                                        </button>

                                        <!-- دکمه حذف -->
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                              style="display:inline-block;"
                                              onsubmit="return confirm('آیا از حذف این نقش مطمئن هستید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action delete">
                                                <i class="fas fa-trash-alt"></i> حذف
                                            </button>
                                        </form>
                                </tr>
                                <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1"
                                     aria-labelledby="editRoleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editRoleModalLabel">ویرایش نقش</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="بستن"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="{{ route('roles.update', $role->id) }}">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">عنوان نقش</label>
                                                        <input type="text" name="name" class="form-control"
                                                               value="{{ $role->name }}" required>
                                                    </div>
                                                    <hr>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label"></label>
                                                        <input type="checkbox" id="allowTicket" name="allow_ticket">
                                                        <label for="allowTicket">امکان مشاهده و پاسخ به تیکت
                                                            ها</label><br>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label"></label>
                                                        <input type="checkbox" id="allowChat" name="allow_chat">
                                                        <label for="allowChat">امکان مشاهده و پاسخ به چت ها
                                                        </label><br>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label"></label>
                                                        <input type="checkbox" id="allowUsers" name="allow_users">
                                                        <label for="allowUsers">امکان مشاهده و مدیریت کاربران
                                                        </label><br>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label"></label>
                                                        <input type="checkbox" id="allowRoles" name="allow_role">
                                                        <label for="allowRoles">امکان مشاهده و مدیریت نقش ها
                                                        </label><br>
                                                    </div>
                                                    <hr>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label"></label>
                                                        <input type="checkbox" id="isInternal" name="is_internal">
                                                        <label for="isInternal">کاربر داخلی سیستم
                                                        </label><br>
                                                        <p>در صورت انتخاب این گزینه, امکان ارجاع چت و یا تیکت به این
                                                            کاربر امکان پذیر نمی باشد و صرفا میتواند آن ها را مشاهده
                                                            نماید</p>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Modal افزودن نقش -->
                            <div class="modal fade" id="addRoleModal" tabindex="-1"
                                 aria-labelledby="addRoleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addRoleModalLabel">افزودن نقش جدید</h5>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('roles.store') }}" method="POST">
                                                @csrf

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">عنوان نقش</label>
                                                    <input type="text" name="name" class="form-control" required>
                                                </div>
                                                <hr>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"></label>
                                                    <input type="checkbox" id="allowTicket" name="allow_ticket">
                                                    <label for="allowTicket">امکان مشاهده و پاسخ به تیکت
                                                        ها</label><br>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"></label>
                                                    <input type="checkbox" id="allowChat" name="allow_chat">
                                                    <label for="allowChat">امکان مشاهده و پاسخ به چت ها
                                                    </label><br>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"></label>
                                                    <input type="checkbox" id="allowUsers" name="allow_users">
                                                    <label for="allowUsers">امکان مشاهده و مدیریت کاربران
                                                    </label><br>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"></label>
                                                    <input type="checkbox" id="allowRoles" name="allow_role">
                                                    <label for="allowRoles">امکان مشاهده و مدیریت نقش ها
                                                    </label><br>
                                                </div>

                                                <hr>
                                                <div class="col-sm-12 mb-3">

                                                    <p>در صورت انتخاب این گزینه, امکان ارجاع چت و یا تیکت به این
                                                        کاربر امکان پذیر نمی باشد و صرفا میتواند آن ها را مشاهده
                                                        نماید</p>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"></label>
                                                    <input type="checkbox" id="isInternal" name="is_internal">
                                                    <label for="isInternal">کاربر داخلی سیستم
                                                    </label><br>
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
<!--   Core JS Files   -->
@endsection
