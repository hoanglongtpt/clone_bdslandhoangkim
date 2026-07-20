@extends('layouts.app')
@section('title', 'Lịch sử ghi chú')
@section('content')
<div class="page-wrap notes-history-page">
    <section class="panel notes-history-panel">
        <div class="notes-history-toolbar">
            <form method="get" action="{{ route('notes.history') }}" class="notes-search">
                <input name="q" value="{{ request('q') }}" placeholder="Tìm mã căn hoặc nội dung ghi chú...">
                <button class="btn primary" type="submit">🔍 Tìm</button>
                @if(request('q'))<a class="btn ghost" href="{{ route('notes.history') }}">Đặt lại</a>@endif
            </form>
            <div class="notes-history-title"><h1>📝 Ghi chú của tôi</h1><span>Tổng cộng <strong>{{ number_format($totalNotes, 0, ',', '.') }}</strong> ghi chú</span></div>
        </div>

        <div class="table-wrap">
            <table class="data-table notes-history-table">
                <thead><tr><th>#</th><th>Mã căn</th><th>Nội dung</th><th>Loại</th><th>Ngày ghi</th><th>Tác giả</th></tr></thead>
                <tbody>
                @forelse($notes as $note)
                    <tr>
                        <td>{{ $notes->firstItem() + $loop->index }}</td>
                        <td>@if($note->property)<a class="property-code-link" href="{{ route('properties.show', $note->property) }}">Xem căn<br>{{ $note->property->code }}</a>@else—@endif</td>
                        <td><div class="history-note-content">{{ $note->note }}</div></td>
                        <td><span class="note-group-chip {{ $note->note_group === '1' ? 'sale' : 'rent' }}">{{ $note->note_group === '1' ? 'Bán' : 'Thuê' }}</span></td>
                        <td>{{ $note->note_date?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $note->author ?: auth()->user()->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="notes-empty">Không tìm thấy ghi chú của bạn.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($notes->hasPages()){{ $notes->onEachSide(1)->links('vendor.pagination.crm') }}@endif
    </section>
</div>
@endsection
