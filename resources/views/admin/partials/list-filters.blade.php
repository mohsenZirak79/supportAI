{{-- نوار جستجو و فیلتر برای صفحات لیست --}}
@php
    $action = $action ?? request()->url();
    $searchName = $searchName ?? 'search';
    $searchValue = $searchValue ?? request('search', '');
    $searchPlaceholder = $searchPlaceholder ?? 'جستجو...';
    $filters = $filters ?? [];
@endphp
<form method="get" action="{{ $action }}" class="list-filters" role="search">
    <div class="list-filters__row">
        <div class="list-filters__search-wrap">
            <label for="list-filters-search" class="visually-hidden">جستجو</label>
            <input type="search"
                   id="list-filters-search"
                   name="{{ $searchName }}"
                   value="{{ $searchValue }}"
                   class="list-filters__input"
                   placeholder="{{ $searchPlaceholder }}"
                   autocomplete="off">
            @foreach(request()->except([$searchName, 'page']) as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
        </div>
        @foreach($filters as $f)
            <div class="list-filters__filter">
                <label for="list-filters-{{ $f['name'] }}" class="list-filters__label">{{ $f['label'] ?? $f['name'] }}</label>
                <select name="{{ $f['name'] }}" id="list-filters-{{ $f['name'] }}" class="list-filters__select">
                    @isset($f['empty_option'])
                        <option value="">{{ $f['empty_option'] }}</option>
                    @endisset
                    @foreach($f['options'] ?? [] as $optVal => $optLabel)
                        <option value="{{ $optVal }}" {{ (string)request($f['name'], $f['selected'] ?? '') === (string)$optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
        <div class="list-filters__actions">
            <button type="submit" class="list-filters__btn list-filters__btn--primary">جستجو</button>
            @if($searchValue || collect($filters)->filter(fn($f) => request($f['name']))->isNotEmpty())
                <a href="{{ $action }}" class="list-filters__btn list-filters__btn--secondary">پاک کردن</a>
            @endif
        </div>
    </div>
</form>
