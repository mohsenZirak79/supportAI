@extends('admin.layouts.master')

@section('title', 'نقش ها')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
        <strong>خطا در ارسال فرم:</strong>
        <ul class="mt-2 mb-0 ps-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
    </div>
@endif

<div class="list-page">
    <header class="list-page__header">
        <div>
            <h1>نقش‌ها</h1>
            <p class="list-page__subtitle">مدیریت نقش‌ها و دسترسی‌ها</p>
        </div>
        <div class="list-page__actions">
            <button class="list-page__btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="fas fa-plus"></i> افزودن نقش
            </button>
        </div>
    </header>

    <div class="list-page__card">
        <div class="table-responsive">
            <table class="list-page__table">
                <thead>
                    <tr>
                        <th>عنوان نقش</th>
                        <th>تعداد کاربران</th>
                        <th>ایجاد شده در تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td><strong>{{ $role->name }}</strong></td>
                            <td><span class="badge bg-primary">{{ $role->users_count }}</span></td>
                            <td>{{ $role->created_at_jalali }}</td>
                            <td>
                                <button type="button" class="list-page__btn-action list-page__btn-action--edit" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}" aria-label="ویرایش نقش {{ $role->name }}">
                                    <i class="fas fa-edit"></i> ویرایش
                                </button>
                                <form id="deleteRoleForm{{ $role->id }}" action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="list-page__btn-action list-page__btn-action--delete" data-confirm-form="deleteRoleForm{{ $role->id }}" data-confirm-title="حذف نقش" data-confirm-body="آیا از حذف این نقش مطمئن هستید؟" data-confirm-btn="بله، حذف شود" aria-label="حذف نقش {{ $role->name }}">
                                        <i class="fas fa-trash-alt"></i> حذف
                                    </button>
                                </form>
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
    </div>
</div>

<!-- Modal افزودن نقش -->
<div class="modal fade admin-modal" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">افزودن نقش جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('roles.store') }}" method="POST" class="admin-form">
                    @csrf
                    <div class="col-md-6 mb-3">
                        <label class="form-label">عنوان نقش</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <hr class="admin-form__divider">
                    <p class="admin-form__section-label">دسترسی‌ها</p>
                    <div class="admin-form__check-list">
                        <label class="admin-form__check-item" for="add_allowTicket">
                            <input type="checkbox" id="add_allowTicket" name="allow_ticket" class="admin-form__check-input">
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">امکان مشاهده و پاسخ به تیکت‌ها</span>
                        </label>
                        <label class="admin-form__check-item" for="add_allowChat">
                            <input type="checkbox" id="add_allowChat" name="allow_chat" class="admin-form__check-input">
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">امکان مشاهده و پاسخ به چت‌ها</span>
                        </label>
                        <label class="admin-form__check-item" for="add_allowUsers">
                            <input type="checkbox" id="add_allowUsers" name="allow_users" class="admin-form__check-input">
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">امکان مشاهده و مدیریت کاربران</span>
                        </label>
                        <label class="admin-form__check-item" for="add_allowRoles">
                            <input type="checkbox" id="add_allowRoles" name="allow_role" class="admin-form__check-input">
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">امکان مشاهده و مدیریت نقش‌ها</span>
                        </label>
                    </div>
                    <hr class="admin-form__divider">
                    <div class="admin-form__check-list">
                        <p class="admin-form__hint mb-2">در صورت انتخاب «کاربر داخلی»، امکان ارجاع چت یا تیکت به این نقش وجود ندارد و فقط مشاهده امکان‌پذیر است.</p>
                        <label class="admin-form__check-item" for="add_isInternal">
                            <input type="checkbox" id="add_isInternal" name="is_internal" class="admin-form__check-input">
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">کاربر داخلی سیستم</span>
                        </label>
                    </div>
                    <button type="submit" class="admin-btn admin-btn--primary">ثبت</button>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach($roles as $role)
<!-- Modal ویرایش نقش {{ $role->id }} -->
<div class="modal fade admin-modal" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">ویرایش نقش</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('roles.update', $role->id) }}" class="admin-form">
                    @csrf
                    @method('PUT')
                    <div class="col-md-6 mb-3">
                        <label class="form-label">عنوان نقش</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <hr class="admin-form__divider">
                    <p class="admin-form__section-label">دسترسی‌ها</p>
                    <div class="admin-form__check-list">
                        <label class="admin-form__check-item" for="edit_allowTicket_{{ $role->id }}">
                            <input type="checkbox" id="edit_allowTicket_{{ $role->id }}" name="allow_ticket" class="admin-form__check-input" @if($role->allow_ticket) checked @endif>
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">امکان مشاهده و پاسخ به تیکت‌ها</span>
                        </label>
                        <label class="admin-form__check-item" for="edit_allowChat_{{ $role->id }}">
                            <input type="checkbox" id="edit_allowChat_{{ $role->id }}" name="allow_chat" class="admin-form__check-input" @if($role->allow_chat) checked @endif>
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">امکان مشاهده و پاسخ به چت‌ها</span>
                        </label>
                        <label class="admin-form__check-item" for="edit_allowUsers_{{ $role->id }}">
                            <input type="checkbox" id="edit_allowUsers_{{ $role->id }}" name="allow_users" class="admin-form__check-input" @if($role->allow_users) checked @endif>
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">امکان مشاهده و مدیریت کاربران</span>
                        </label>
                        <label class="admin-form__check-item" for="edit_allowRoles_{{ $role->id }}">
                            <input type="checkbox" id="edit_allowRoles_{{ $role->id }}" name="allow_role" class="admin-form__check-input" @if($role->allow_role) checked @endif>
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">امکان مشاهده و مدیریت نقش‌ها</span>
                        </label>
                    </div>
                    <hr class="admin-form__divider">
                    <div class="admin-form__check-list">
                        <label class="admin-form__check-item" for="edit_isInternal_{{ $role->id }}">
                            <input type="checkbox" id="edit_isInternal_{{ $role->id }}" name="is_internal" class="admin-form__check-input" @if($role->is_internal) checked @endif>
                            <span class="admin-form__check-box"></span>
                            <span class="admin-form__check-text">کاربر داخلی سیستم</span>
                        </label>
                        <p class="admin-form__hint mt-1 mb-0">در صورت انتخاب، امکان ارجاع چت یا تیکت به این نقش وجود ندارد.</p>
                    </div>
                    <button type="submit" class="admin-btn admin-btn--primary">ذخیره تغییرات</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
