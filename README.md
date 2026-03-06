
# ABCSNoobMC Skin Viewer

**ABCSNoobMC Skin Viewer** là một thư viện JavaScript nhỏ gọn giúp bạn **tìm kiếm và hiển thị Minecraft Skin của người chơi** trực tiếp trên website.

Thư viện cung cấp:
- 🔎 Tìm kiếm username Minecraft
- 🧍 Hiển thị **3D Skin Viewer**
- 🧥 Hỗ trợ **Cape**
- ⬇️ Tải Skin / Cape
- 📋 Copy lệnh `curl` để gọi API
- ⚡ Loading progress và alert đẹp với **NProgress** và **SweetAlert2**

---

## Demo

Giao diện bao gồm:

- Input nhập username
- Nút tìm kiếm
- Viewer 3D skin
- Thông tin player (name + UUID)
- Các nút hành động

---

## Cài đặt

Chỉ cần import file JS vào website của bạn.

```html
<script src="abcsmc.js"></script>
````

Sau đó tạo container:

```html
<div id="abcsmc"></div>
```

---

## Khởi tạo

```javascript
abcsnoobmc.init()
```

Hoặc custom cấu hình:

```javascript
abcsnoobmc.init({
  containerId: "abcsmc",
  apiUrl: "https://mcskin.abcsnoob.workers.dev/api/"
})
```

---

## Ví dụ hoàn chỉnh

```html
<!DOCTYPE html>
<html>
<head>
  <title>Minecraft Skin Viewer</title>
</head>
<body>

<div id="abcsmc"></div>

<script src="abcsmc.js"></script>
<script>
  abcsnoobmc.init();
</script>

</body>
</html>
```

---

## API Response

API trả về JSON dạng:

```json
{
  "name": "Notch",
  "uuid": "069a79f444e94726a5befca90e38aaf5",
  "skin": "https://...",
  "cape": "https://...",
  "skin3d": "https://..."
}
```

---

## Tính năng

### Tìm kiếm player

Người dùng nhập username Minecraft để tìm skin.

### 3D Skin Viewer

Skin được hiển thị bằng iframe viewer 3D.

### Tải Skin / Cape

Có các nút:

* Download Skin
* Download Cape
* View Cape

### Copy CURL

Có thể sao chép nhanh lệnh:

```bash
curl "https://mcskin.abcsnoob.workers.dev/api/<username>"
```

---

## Dependencies (Auto Load)

Thư viện tự động tải:

* NProgress
* SweetAlert2

CDN:

```
https://cdnjs.cloudflare.com/ajax/libs/nprogress
https://cdn.jsdelivr.net/npm/sweetalert2
```

Bạn **không cần cài đặt thủ công**.

---

## Cấu trúc

Thư viện export một object:

```javascript
abcsnoobmc
```

### Methods

#### `init(options)`

Khởi tạo UI.

| Option      | Type   | Default      |
| ----------- | ------ | ------------ |
| containerId | string | `abcsmc`     |
| apiUrl      | string | API mặc định |

---

#### `render(username)`

Render dữ liệu player.

```javascript
abcsnoobmc.render("Notch")
```

---

## Yêu cầu

* Trình duyệt hỗ trợ **ES6**
* Fetch API
* Clipboard API

---

## License

MIT License

---

## Author

**ABCSNoob**

API:

```
https://mcskin.abcsnoob.workers.dev
```
 

để project nhìn **xịn như library thật**.
```
