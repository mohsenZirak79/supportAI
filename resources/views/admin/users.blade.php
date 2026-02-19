@extends('admin.layouts.master')

@section('title', 'لیست کاربران')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">
    <style>
        .modal, .modal .modal-dialog, .modal .modal-content, .modal .modal-body { overflow: visible !important; }
        .jdp-container, .jalali-datepicker { z-index: 200000 !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jalaliDatepicker?.startWatch) {
                jalaliDatepicker.startWatch({ time: false, container: 'body', zIndex: 200000 });
                document.addEventListener('shown.bs.modal', function () {
                    jalaliDatepicker.updateOptions({zIndex: 200000, container: 'body'});
                });
            }
        });
    </script>
@endpush

@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
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

<div class="list-page">
    <header class="list-page__header">
        <div>
            <h1>کاربران</h1>
            <p class="list-page__subtitle">مدیریت کاربران و نقش‌ها</p>
        </div>
        <div class="list-page__actions">
            <button class="list-page__btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus"></i> افزودن کاربر
            </button>
        </div>
    </header>

    @include('admin.partials.list-filters', [
        'action' => route('admin.users'),
        'searchPlaceholder' => 'جستجو در نام، ایمیل، تلفن، کد ملی...',
        'searchValue' => request('search'),
        'filters' => [
            [
                'name' => 'role_id',
                'label' => 'نقش',
                'empty_option' => 'همه نقش‌ها',
                'options' => $roles->pluck('name', 'id')->toArray(),
            ],
        ],
    ])

    <div class="list-page__card">
        <div class="table-responsive">
            <table class="list-page__table">
                <thead>
                    <tr>
                        <th>نام و کد ملی</th>
                        <th>نقش</th>
                        <th>تلفن همراه</th>
                        <th>ایمیل</th>
                        <th>تاریخ شروع</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="mb-0">{{ $user->name }} {{ $user->family }}</strong>
                                    <span class="text-secondary small">{{ $user->national_id }}</span>
                                </div>
                            </td>
                            <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at_jalali }}</td>
                            <td>
                                <button type="button" class="list-page__btn-action list-page__btn-action--edit" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" aria-label="ویرایش {{ $user->name }}">
                                    <i class="fas fa-edit"></i> ویرایش
                                </button>
                                <form id="deleteUserForm{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="list-page__btn-action list-page__btn-action--delete" data-confirm-form="deleteUserForm{{ $user->id }}" data-confirm-title="حذف کاربر" data-confirm-body="آیا از حذف این کاربر مطمئن هستید؟" data-confirm-btn="بله، حذف شود" aria-label="حذف {{ $user->name }}">
                                        <i class="fas fa-trash-alt"></i> حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="list-page__empty">هیچ موردی یافت نشد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($users, 'hasPages') && $users->hasPages())
            @include('admin.partials.pagination', ['paginator' => $users])
        @endif
    </div>
</div>

<!-- Modal افزودن کاربر -->
<div class="modal fade admin-modal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">افزودن کاربر جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('users.store') }}" method="POST" class="admin-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نام</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نام خانوادگی</label>
                            <input type="text" name="family" class="form-control @error('family') is-invalid @enderror" value="{{ old('family') }}" required>
                            @error('family')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">کد ملی</label>
                            <input type="text" name="national_id" class="form-control @error('national_id') is-invalid @enderror" value="{{ old('national_id') }}" required>
                            @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3" dir="rtl">
                            <label for="birth_date" class="form-label">تاریخ تولد</label>
                            <input id="birth_date" name="birth_date" type="text" class="form-control input-jalali" autocomplete="off" dir="ltr" value="{{ old('birth_date') }}"
                                   data-jdp data-jdp-only-date="true" data-jdp-config='{"selector":"#birth_date","dateFormat":"YYYY/MM/DD","autoShow":true}'>
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ایمیل</label>
                            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">شماره تلفن</label>
                            <input type="number" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رمز عبور</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تکرار رمز عبور</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">کد پستی</label>
                            <input type="number" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror" value="{{ old('postal_code') }}" required>
                            @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">آدرس</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}">
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">نقش کاربر</label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="" disabled selected>انتخاب نقش...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @if(old('role') == $role->id) selected @endif>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="admin-btn admin-btn--primary">ثبت</button>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach($users as $user)
<!-- Modal ویرایش کاربر {{ $user->id }} -->
<div class="modal fade admin-modal" id="editUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">ویرایش کاربر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('users.update', $user->id) }}" class="admin-form">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نام</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نام خانوادگی</label>
                            <input type="text" name="family" class="form-control @error('family') is-invalid @enderror" value="{{ old('family', $user->family) }}" required>
                            @error('family')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">کد ملی</label>
                            <input type="text" name="national_id" class="form-control @error('national_id') is-invalid @enderror" value="{{ old('national_id', $user->national_id) }}" required>
                            @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3" dir="rtl">
                            <label for="birth_date_{{ $user->id }}" class="form-label">تاریخ تولد</label>
                            <input id="birth_date_{{ $user->id }}" name="birth_date" type="text" class="form-control input-jalali" value="{{ old('birth_date', $user->birth_date) }}"
                                   autocomplete="off" dir="ltr" data-jdp data-jdp-only-date="true" data-jdp-config='{"dateFormat":"YYYY/MM/DD","autoShow":true}'>
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ایمیل</label>
                            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">شماره تلفن</label>
                            <input type="number" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">کد پستی</label>
                            <input type="number" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror" value="{{ old('postal_code', $user->postal_code) }}" required>
                            @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">آدرس</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $user->address) }}">
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">نقش کاربر</label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="" disabled selected>انتخاب نقش...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @if(old('role', $user->roles->first()?->id) == $role->id) selected @endif>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="admin-btn admin-btn--primary">ذخیره تغییرات</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
