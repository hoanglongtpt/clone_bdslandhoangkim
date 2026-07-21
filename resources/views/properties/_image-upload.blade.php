<form method="post" action="{{ route('properties.images.store', $property) }}" enctype="multipart/form-data" class="image-upload-form">
    @csrf
    <label class="image-upload-picker">
        <span>Chọn hình ảnh</span>
        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required data-image-upload-input>
        <small>JPG, PNG hoặc WebP · tối đa 20 ảnh/lần · 10 MB/ảnh</small>
    </label>
    <div class="image-upload-actions"><span class="muted" data-image-upload-count>Chưa chọn ảnh</span><button class="btn primary" type="submit">↑ Tải ảnh lên</button></div>
</form>
