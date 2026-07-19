@extends('layouts.app')
@section('title', $property->code)
@section('content')
<div class="page-wrap">
    <div class="page-head"><div><a class="back" href="{{ route('dashboard') }}">← Dashboard</a><h1>{{ $property->code }}</h1><p>{{ $property->project?->project_name }}</p></div>@if(auth()->user()->canEditProperties())<a class="btn primary" href="{{ route('properties.edit', $property) }}">Chỉnh sửa</a>@endif</div>
    <div class="detail-grid">
        <section class="panel"><h2>Thông tin căn</h2><dl class="data-list"><dt>Dự án</dt><dd>{{ $property->project?->project_name ?? '—' }}</dd><dt>Tháp / tầng / số</dt><dd>{{ $property->tower ?? '—' }} / {{ $property->floor ?? '—' }} / {{ $property->room ?? '—' }}</dd><dt>Loại căn</dt><dd>{{ $property->property_type ?: '—' }}</dd><dt>Diện tích</dt><dd>{{ $property->area ?: '—' }} m²</dd><dt>Nội thất</dt><dd>{{ $property->interior ?: 'Chưa rõ' }}</dd><dt>Trạng thái</dt><dd>{{ $property->status_new ?: '—' }}</dd><dt>Giá bán</dt><dd>{{ $property->price_sell ? number_format((float)$property->price_sell,0,',','.') : '—' }}</dd><dt>Giá thuê</dt><dd>{{ $property->price_rent ? number_format((float)$property->price_rent,0,',','.') : '—' }}</dd><dt>Hoa hồng</dt><dd>{{ $property->sales_commission ? number_format((float)$property->sales_commission,0,',','.') : '—' }}</dd></dl></section>
        <section class="panel"><h2>Khách hàng</h2>@forelse($property->customers as $customer)<div class="customer-box"><strong>{{ $customer->full_name }}</strong><span>☎ {{ $customer->phone1 ?: '—' }}</span><span>{{ $customer->email }}</span></div>@empty<p class="muted">Chưa có khách hàng.</p>@endforelse</section>
    </div>
    <section class="panel" id="media"><h2>Hình ảnh & tài liệu <span class="count">{{ $property->media->count() }}</span></h2><div class="media-grid">@forelse($property->media->take(100) as $media)<a class="media-item" href="{{ route('media.show',$media) }}" target="_blank">@if($media->media_type==='image')<img loading="lazy" src="{{ route('media.show',$media) }}" alt="">@else<div class="file-icon">▤</div>@endif<span>{{ $media->media_type }}</span></a>@empty<p class="muted">Không có media.</p>@endforelse</div>@if($property->media->count()>100)<p class="muted">Đang hiển thị 100/{{ $property->media->count() }} mục đầu tiên.</p>@endif</section>
    <div class="detail-grid">
        @foreach([['saleNotes','sale-notes','Ghi chú bán','1'],['rentNotes','rent-notes','Ghi chú thuê','2']] as [$relation,$anchor,$title,$group])
        <section class="panel" id="{{ $anchor }}"><h2>{{ $title }} <span class="count">{{ $property->$relation->count() }}</span></h2>
            @if(auth()->user()->canEditProperties())<form method="post" action="{{ route('properties.notes.store',$property) }}" class="note-form">@csrf<input type="hidden" name="note_group" value="{{ $group }}"><textarea name="note" required placeholder="Thêm {{ mb_strtolower($title) }}..."></textarea><button class="btn outline-green" type="submit">+ Thêm</button></form>@endif
            <div class="timeline">@forelse($property->$relation as $note)<article><div><time>{{ $note->note_date?->format('d/m/Y H:i') }}</time><b>{{ $note->author }}</b></div><p>{{ $note->note }}</p></article>@empty<p class="muted">Chưa có ghi chú.</p>@endforelse</div>
        </section>@endforeach
    </div>
</div>
@endsection

