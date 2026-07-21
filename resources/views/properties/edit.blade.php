@extends('layouts.app')
@section('title', 'Chỉnh sửa '.$property->code)
@section('content')
<div class="page-wrap narrow"><div class="page-head"><div><a class="back" href="{{ route('properties.show',$property) }}">← Chi tiết căn</a><h1>Chỉnh sửa {{ $property->code }}</h1></div></div>
<form method="post" action="{{ route('properties.update',$property) }}" class="panel edit-form">@csrf @method('PUT')
    <div class="form-grid two">
        <label>Mã căn<input name="code" required value="{{ old('code',$property->code) }}"></label>
        <label>Dự án<select name="project_id"><option value="">—</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected(old('project_id',$property->project_id)==$project->id)>{{ $project->project_name }}</option>@endforeach</select></label>
        @foreach(['tower'=>'Tháp','floor'=>'Tầng','room'=>'Số căn','property_type'=>'Loại căn','interior'=>'Nội thất','status_new'=>'Trạng thái'] as $field=>$label)<label>{{ $label }}<input name="{{ $field }}" value="{{ old($field,$property->$field) }}"></label>@endforeach
        @foreach(['area'=>'Diện tích','price_sell'=>'Giá bán','price_rent'=>'Giá thuê','sales_commission'=>'Hoa hồng'] as $field=>$label)<label>{{ $label }}<input type="number" step="0.01" min="0" name="{{ $field }}" value="{{ old($field,$property->$field) }}"></label>@endforeach
        <label>Hạn thuê<input type="date" name="rent_expiry" value="{{ old('rent_expiry',$property->rent_expiry?->format('Y-m-d')) }}"></label><label>Ngày cập nhật<input type="date" name="updated_date" value="{{ old('updated_date',$property->updated_date?->format('Y-m-d')) }}"></label>
    </div><div class="form-actions"><a class="btn ghost" href="{{ route('properties.show',$property) }}">Hủy</a><button class="btn primary" type="submit">Lưu thay đổi</button></div>
</form><section class="panel image-upload-panel"><h2>Thêm hình ảnh</h2>@include('properties._image-upload', ['property' => $property])</section></div>
@endsection
