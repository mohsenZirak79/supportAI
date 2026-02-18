@extends('admin.layouts.master')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">
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
<div class="list-page">
    <header class="list-page__header">
        <div>
            <h1>پروفایل</h1>
            <p class="list-page__subtitle">ویرایش اطلاعات کاربری و تغییر رمز عبور</p>
        </div>
    </header>

    <div class="list-page__card">
        <div class="p-4 p-md-5">
            <h2 class="h5 mb-4" style="font-weight: 700; color: var(--admin-text);">ویرایش اطلاعات کاربری</h2>
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="admin-form">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">نام</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">نام خانوادگی</label>
                                    <input type="text" name="family" class="form-control @error('family') is-invalid @enderror"
                                           value="{{ old('family', $user->family) }}" required>
                                    @error('family')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ایمیل</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">شماره موبایل</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $user->phone) }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">کد ملی</label>
                                    <input type="text" name="national_id" class="form-control @error('national_id') is-invalid @enderror"
                                           value="{{ old('national_id', $user->national_id) }}">
                                    @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">کد پستی</label>
                                    <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                                           value="{{ old('postal_code', $user->postal_code) }}">
                                    @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3" dir="rtl">
                                <label for="birth_date_{{ $user->id }}" class="form-label">تاریخ تولد</label>
                                <input id="birth_date_{{ $user->id }}" name="birth_date" type="text" class="form-control input-jalali"
                                       value="{{ old('birth_date', $user->birth_date) }}" autocomplete="off" dir="ltr"
                                       data-jdp data-jdp-only-date="true" data-jdp-config='{"dateFormat":"YYYY/MM/DD","autoShow":true}'>
                                @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">آدرس</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $user->address) }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">رمز عبور جدید (اختیاری)</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="در صورت تمایل رمز جدید وارد کنید">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="admin-btn admin-btn--primary">ذخیره تغییرات</button>
                            </div>
                        </form>

            @if($errors->any())
                <div class="admin-error-box mt-4">
                    <span class="admin-error-box__msg">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
