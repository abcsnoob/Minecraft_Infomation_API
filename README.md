# Abc's NoobMC Skin Viewer

Thư viện JavaScript nhẹ giúp tìm kiếm và hiển thị skin Minecraft 3D trực tiếp trên website của bạn.

## Tính năng
- Tìm kiếm tên người chơi Minecraft nhanh chóng.
- Trình xem skin 3D tùy chỉnh.
- Hỗ trợ hiển thị và tải xuống Cape.
- Sao chép lệnh cURL API chỉ với một cú nhấp.
- Tích hợp sẵn thông báo (SweetAlert2) và hiệu ứng tải trang (NProgress).

## Cài đặt
Nhúng script vào dự án của bạn:
```html
<script src="[https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft_Infomation_API@main/dist/abcsmc.min.js](https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft_Infomation_API@main/dist/abcsmc.min.js)"></script>

```

Tạo container để hiển thị:

```html
<div id="abcsmc"></div>

```

Khởi tạo thư viện:

```javascript
abcsnoobmc.init({
  containerId: "abcsmc",
  useCache: true
});

```

## API Endpoint

Đây:
[https://mcskin.abcsnoob.workers.dev/api/](https://mcskin.abcsnoob.workers.dev/api/)
## License

MIT
