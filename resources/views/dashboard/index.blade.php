@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="dashboard-toolbar">
    <div><strong>{{ number_format($properties->total(), 0, ',', '.') }}</strong> căn phù hợp <span class="muted">· Trang {{ $properties->currentPage() }}/{{ $properties->lastPage() }}</span></div>
    <div class="toolbar-actions">
        @if(request()->query())<a class="btn ghost" href="{{ route('dashboard') }}">Xóa bộ lọc</a>@endif
        <button class="btn primary" type="button" data-open-filter>⚙ Bộ lọc</button>
    </div>
</div>

<section class="property-list">
@forelse($properties as $property)
    <article class="property-card">
        <div class="property-image">
            @if($property->firstImage)
                <img loading="lazy" src="{{ route('media.show', $property->firstImage) }}" alt="{{ $property->code }}">
            @else<span>No image</span>@endif
        </div>
        <div class="cell info"><small>THÔNG TIN</small><strong>{{ $property->code }}</strong><b>{{ $property->project?->project_name ?? '—' }}</b><b>THÁP {{ $property->tower ?? '—' }}</b><b>TẦNG {{ $property->floor ?? '—' }}</b><b>SỐ {{ $property->room ?? '—' }}</b></div>
        <div class="cell type"><small>LOẠI / DIỆN TÍCH</small><b>LOẠI: {{ $property->property_type ?: '—' }}</b><b>DIỆN TÍCH: {{ $property->area ? rtrim(rtrim(number_format((float)$property->area, 2, ',', '.'), '0'), ',') : '—' }} m²</b><b>Nội thất: {{ $property->interior ?: 'Chưa rõ' }}</b></div>
        <div class="cell status"><small>TRẠNG THÁI</small><span class="status-pill {{ str_contains(mb_strtolower($property->status_new ?? ''), 'đang') ? 'rented' : '' }}">{{ $property->status_new ?: 'Chưa cập nhật' }}</span><b>Hạn thuê: {{ $property->rent_expiry?->format('d/m/Y') ?? '—' }}</b></div>
        <div class="cell prices"><small>GIÁ</small><b class="sell">Bán: {{ $property->price_sell ? number_format((float)$property->price_sell, 0, ',', '.') : '—' }}</b><b class="rent">Thuê: {{ $property->price_rent ? number_format((float)$property->price_rent, 0, ',', '.') : '—' }}</b><b class="commission">Hoa hồng: {{ $property->sales_commission ? number_format((float)$property->sales_commission, 0, ',', '.') : '—' }}</b></div>
        <div class="cell notes"><small>GHI CHÚ</small><div class="note-row"><b>Bán:</b><span>{{ Str::limit($property->latestSaleNote?->note ?? '—', 58) }}</span><a href="{{ route('properties.show', $property) }}#sale-notes">Xem</a></div><div class="note-row"><b>Thuê:</b><span>{{ Str::limit($property->latestRentNote?->note ?? '—', 58) }}</span><a href="{{ route('properties.show', $property) }}#rent-notes">Xem</a></div></div>
        <div class="cell customers"><small>KHÁCH HÀNG</small>@forelse($property->customers->take(2) as $customer)<b>{{ $customer->full_name }}</b><span class="phone">☎ {{ $customer->phone1 ?: '—' }}</span>@empty<span>—</span>@endforelse</div>
        <div class="cell other"><small>KHÁC</small><span>Cập nhật: {{ $property->updated_date?->format('d/m/Y') ?? '—' }}</span><a class="btn outline-green" href="{{ route('properties.show', $property) }}">Chi tiết</a>@if(auth()->user()->canEditProperties())<a class="btn outline-green" href="{{ route('properties.edit', $property) }}">Chỉnh sửa</a>@endif</div>
    </article>
@empty
    <div class="empty-state"><h2>Không tìm thấy căn phù hợp</h2><a class="btn primary" href="{{ route('dashboard') }}">Đặt lại bộ lọc</a></div>
@endforelse
</section>

@if($properties->hasPages())
<nav class="pagination">
    @if($properties->onFirstPage())<span>‹ Trước</span>@else<a href="{{ $properties->previousPageUrl() }}">‹ Trước</a>@endif
    <strong>{{ $properties->currentPage() }} / {{ $properties->lastPage() }}</strong>
    @if($properties->hasMorePages())<a href="{{ $properties->nextPageUrl() }}">Sau ›</a>@else<span>Sau ›</span>@endif
</nav>
@endif

<div class="drawer-backdrop" data-filter-backdrop></div>
<aside class="filter-drawer" data-filter-drawer>
    <div class="drawer-head"><h2>Bộ lọc</h2><button type="button" data-close-filter>×</button></div>
    <form method="get" action="{{ route('dashboard') }}" class="filter-form">
        <label>Tìm kiếm<input name="q" value="{{ request('q') }}" placeholder="Mã căn, dự án, khách hàng..."></label>
        <div class="form-grid">
            <label>Dự án<select name="project_id"><option value="">Tất cả</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->project_name }}</option>@endforeach</select></label>
            @foreach(['tower'=>'Tháp','floor'=>'Tầng','room'=>'Số','property_type'=>'Loại căn','interior'=>'Nội thất','status_new'=>'Trạng thái'] as $key=>$label)
            <label>{{ $label }}<select name="{{ $key }}"><option value="">Tất cả</option>@foreach($options[$key] as $value)<option value="{{ $value }}" @selected(request($key)==$value)>{{ $value }}</option>@endforeach</select></label>
            @endforeach
        </div>
        @foreach([['price_sell','Giá bán'],['price_rent','Giá thuê'],['area','Diện tích (m²)'],['commission','Hoa hồng']] as [$key,$label])
        <fieldset><legend>{{ $label }}</legend><div class="range"><input type="number" name="{{ $key }}_min" value="{{ request($key.'_min') }}" placeholder="Từ"><input type="number" name="{{ $key }}_max" value="{{ request($key.'_max') }}" placeholder="Đến"></div></fieldset>
        @endforeach
        <label class="check"><input type="checkbox" name="has_image" value="1" @checked(request('has_image'))> Có hình ảnh</label>
        <label class="check"><input type="checkbox" name="has_document" value="1" @checked(request('has_document'))> Có hồ sơ / tài liệu</label>
        <label>Số kết quả / trang<select name="per_page">@foreach([10,20,50,100] as $size)<option value="{{ $size }}" @selected((int)request('per_page',20)===$size)>{{ $size }}</option>@endforeach</select></label>
        <div class="drawer-actions"><a class="btn ghost" href="{{ route('dashboard') }}">Reset</a><button class="btn primary" type="submit">Lọc</button></div>
    </form>
</aside>
@endsection

