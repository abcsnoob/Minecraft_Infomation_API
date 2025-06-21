# Minecraft_Infomation_API

## Giới thiệu

**Minecraft_Infomation_API** là một API REST đơn giản và hiệu quả giúp tra cứu thông tin người chơi Minecraft Java Edition một cách nhanh chóng và dễ dàng.  
API cung cấp dữ liệu về:

- Tên người chơi (username)
- UUID (unique user ID)
- Skin (hình ảnh mặc định)
- Cape (áo choàng nếu có)
- Skin 3D dạng embed để tích hợp trực quan

## Tính năng chính

- Tra cứu thông tin người chơi Minecraft theo username
- Trả về dữ liệu JSON dễ dàng sử dụng cho website, ứng dụng hoặc bot
- Cung cấp endpoint hiển thị skin 3D trực tiếp dưới dạng iframe hoặc embed
- Không phụ thuộc vào Mojang/Microsoft, fanmade và miễn phí sử dụng

## Cách sử dụng

### 1. Tra cứu thông tin người chơi
```http
GET https://abcsnoobmcname.42web.io/api/<tên-người-chơi>
```
Ví dụ:
```url
GET https://abcsnoobmcname.42web.io/api/Dream
```
Kết quả trả về:
```json
{
  "name": "Dream",
  "uuid": "ec70bcaf702f4bb8b48d276fa52a780c",
  "skin": "http://textures.minecraft.net/texture/ca93f6fc40488f1877cda94a830b54e9f6f54ab58a5453bad5c947726dd1f473",
  "cape": null,
  "skin3d": "https://abcsnoobmcname.42web.io/api/3dskin/Dream"
}
```
### 2. Hiển thị skin 3D dạng embed
```http
GET https://abcsnoobmcname.42web.io/api/3dskin?username=<tên-người-chơi>
```
Hoặc:
```http
GET https://abcsnoobmcname.42web.io/api/3dskin/<tên-người-chơi>
```
Ví dụ:
```http
https://abcsnoobmcname.42web.io/api/3dskin?username=Dream
```
## Ví dụ tích hợp trong JavaScript (Fetch API)
```
fetch('https://abcsnoobmcname.42web.io/api/Dream')
  .then(response => response.json())
  .then(data => {
    console.log('Tên:', data.name);
    console.log('UUID:', data.uuid);
    console.log('Skin URL:', data.skin);
    console.log('Cape URL:', data.cape);
    console.log('Skin 3D URL:', data.skin3d);
  })
  .catch(error => console.error('Lỗi:', error));
```
## Liên hệ

- Tác giả: Abc’s Noob  
- Trang chủ API: [https://abcsnoobmcname.42web.io/api/](https://abcsnoobmcname.42web.io/api/)
- Fanmade API – không thuộc Mojang/Microsoft.  
- Mojang mà chưa làm API dễ dùng thì... mình làm 😎

---

Cảm ơn bạn đã sử dụng Minecraft_Infomation_API!
