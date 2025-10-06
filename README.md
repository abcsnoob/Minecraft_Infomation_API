# Minecraft_Infomation_API

## Giới thiệu

**Minecraft_Infomation_API** là một API REST đơn giản và hiệu quả giúp tra cứu thông tin người chơi Minecraft Java Edition một cách nhanh chóng và dễ dàng.  
API cung cấp dữ liệu về:

- Tên người chơi (username)
- UUID (unique user ID)
- Skin (hình ảnh mặc định)
- Cape (áo choàng nếu có)
- Skin 3D dạng embed để tích hợp trực quan

Mục lục:
-[Tính năng chính](#Tính-năng-chính)
-[Cách dùng](#Cách-sử-dụng)
-[Ví dụ cho JS](#Ví-dụ-tích-hợp-trong-JavaScript-(Fetch-API))
-[API kiểu thư viện](#Còn-thư-viện-JS-thì-sao)
-[Biện pháp bảo mật](#Biện-pháp-bảo-mật)

## Tính năng chính

- Tra cứu thông tin người chơi Minecraft theo username
- Trả về dữ liệu JSON dễ dàng sử dụng cho website, ứng dụng hoặc bot
- Cung cấp endpoint hiển thị skin 3D trực tiếp dưới dạng iframe hoặc embed
- Không phụ thuộc vào Mojang/Microsoft, fanmade và miễn phí sử dụng

## Cách sử dụng

### 1. Tra cứu thông tin người chơi
```http
GET https://abcsmc.42web.io/<tên-người-chơi>
```
Ví dụ:
```http
GET https://abcsmc.42web.io/Dream
```
Kết quả trả về:
```json
{
  "name": "Dream",
  "uuid": "ec70bcaf702f4bb8b48d276fa52a780c",
  "skin": "http://textures.minecraft.net/texture/ca93f6fc40488f1877cda94a830b54e9f6f54ab58a5453bad5c947726dd1f473",
  "cape": null,
  "skin3d": "abcsmc.42web.io/3dskin/Dream"
}
```
### 2. Hiển thị skin 3D dạng embed
```http
GET https://abcsmc.42web.io/api/3dskin?username=<tên-người-chơi>
```
Hoặc:
```http
GET https://abcsmc.42web.io/3dskin/<tên-người-chơi>
```
Ví dụ:
```url
https://abcsmc.42web.io/3dskin?username=Dream
```
## Ví dụ tích hợp trong JavaScript (Fetch API)
```
fetch('https://abcsmc.42web.io/Dream')
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
## Còn thư viện JS thì sao
Tin vui là: có hỗ trợ thư viện
URL CDN:
[https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft\_Infomation\_API@main/dist/abcsmc.min.js](https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft_Infomation_API@main/dist/abcsmc.min.js)
Các phiên bản cũ:
1.0: [https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft\_Infomation\_API@main/dist/abcsmcname.min.js](https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft_Infomation_API@main/dist/abcsmcname.min.js)


---

# Hướng Dẫn Sử Dụng abcsnoobmcnamelib.min.js



---

Các hàm chính:

1. `getMinecraftUserData(username)`

* Mục đích: Lấy thông tin người chơi Minecraft Java Edition theo username.
* Trả về: Promise trả về object chứa:
```json
  {
  "name": "Tên người chơi",
  "uuid": "UUID",
  "skin": "URL skin",
  "cape": "URL cape hoặc null",
  "skin3d": "URL skin 3D embed"
  }
```
* Cách dùng:
```javascript
import { getMinecraftUserData } from "https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft\_Infomation\_API@main/dist/abcsmcname.min.js";

getMinecraftUserData("Dream").then(data => {
console.log("Tên:", data.name);
console.log("UUID:", data.uuid);
console.log("Skin URL:", data.skin);
console.log("Cape URL:", data.cape);
console.log("Skin 3D URL:", data.skin3d);
}).catch(err => {
console.error("Lỗi khi lấy dữ liệu:", err);
});
```
---

2. `getSkin3DUrl(username)`

* Mục đích: Lấy URL embed skin 3D (dạng iframe) cho username.
* Trả về: URL string
* Cách dùng:
```Javascript
import { getSkin3DUrl } from "https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft_Infomation_API@main/dist/abcsmcname.min.js";

const url = getSkin3DUrl("Dream");
console.log(url);  // https://abcsmc.42web.io/api/3dskin/Dream
```
---

3. embedSkin3D(username, elementId, width=320, height=440)

* Mục đích: Tạo iframe và nhúng skin 3D vào phần tử HTML có id = `elementId`
* Tham số:

  * `username`: tên người chơi
  * `elementId`: id của phần tử div hoặc container HTML
  * `width` (tùy chọn): chiều rộng iframe (mặc định 320)
  * `height` (tùy chọn): chiều cao iframe (mặc định 440)
* Cách dùng:
```Javascript
import { embedSkin3D } from "https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft\_Infomation\_API@main/dist/abcsmcname.min.js";

embedSkin3D("Dream", "skinContainer", 320, 440);
```
---

*Lưu ý:*

* Thư viện chạy trên trình duyệt hỗ trợ ES modules.
* Các hàm đều sẽ ném lỗi nếu `username` không hợp lệ hoặc phần tử không tìm thấy.
* URL skin3d trả về có dạng [https://abcsmc.42web.io/3dskin/{username}](https://abcsmc.42web.io/3dskin/{username})

---

Bạn chỉ cần chèn thẻ script `type="module"` trong HTML hoặc dùng bundler phù hợp để import thư viện.

---

## Biện pháp bảo mật
* Khuyến khích sử dụng **HTTPS** để kết nối tới server
* Server của chúng tôi sử dụng **HTTPS**
* Chúng tôi dùng [IFastNet](https://abcsnoob.github.io/redirect?url=https://ifastnet.com/) đảm bảo bảo mật

## Trạng thái:
Gần như luôn ổn định
Trừ khi server IFASTNET có sự cố thôi :)
## Liên hệ

- Tác giả: Abc’s Noob  
- Trang chủ API: [https://abcsmc.42web.io/](https://abcsmc.42web.io/)
- Fanmade API – không thuộc Mojang/Microsoft.  
- Mojang mà chưa làm API dễ dùng thì... mình làm 😎

---

Cảm ơn bạn đã sử dụng Minecraft_Infomation_API!
