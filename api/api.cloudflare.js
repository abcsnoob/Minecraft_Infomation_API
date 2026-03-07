export default {
  async fetch(request, env, ctx) {

    const url = new URL(request.url)
    const path = url.pathname

    const newinfo = url.searchParams.get("newinfo") === "true"
    const showCape = url.searchParams.get("cape") === "true"

if (!path.startsWith("/api") && path !== "/favicon.ico") {
    // Chuyển hướng với 302 để tránh trình duyệt lưu cache vĩnh viễn (301)
    return Response.redirect(url.origin + "/api/", 302);
}
    if (path === "/favicon.ico") {
  const referer = request.headers.get("Referer") || "";
  
  // Trích xuất username từ URL Referer (Ví dụ: .../api/3dskin/Dream)
  // Nếu người dùng đang xem trang 3D Skin hoặc trang API JSON
  const match = referer.match(/\/api\/(?:3dskin\/)?([^/?#]+)/);
  const username = match ? match[1] : "Steve"; // Mặc định là Steve nếu không xác định được
  
  // Chuyển hướng đến ảnh đầu của user đó (Minotar)
  return Response.redirect(`https://minotar.net/helm/${encodeURIComponent(username)}/100`, 302);
}
    const parts = path.replace(/^\/+|\/+$/g, "").split("/")

    // ======================
    // API DOCS
    // ======================

    if (parts.length === 1) {
      return new Response(showDocs(url.origin), {
        headers: corsHTML()
      })
    }

    const endpoint = parts[1]

    // ======================
    // 3D SKIN VIEW
    // ======================

    if (endpoint === "3dskin") {

      let username = url.searchParams.get("username") || parts[2] || ""
      username = username.trim()

      if (!username) {
        return new Response("Thiếu username")
      }

      const userData = await getUserData(username, url.origin, ctx, newinfo)

      if (!userData) {
        return new Response("Không tìm thấy người chơi")
      }

      return new Response(skin3DPage(userData, showCape), {
        headers: corsHTML()
      })
    }

    // ======================
    // USER API
    // ======================

    const username = endpoint

    const data = await getUserData(username, url.origin, ctx, newinfo)

    if (!data) {

      return new Response(JSON.stringify({
        error: `Không tìm thấy người chơi Java tên '${username}'.`,
        note: "Có thể người chơi chưa mua Minecraft Java hoặc là tài khoản Bedrock/crack."
      }, null, 2), {
        headers: corsJSON()
      })
    }

    return new Response(JSON.stringify(data, null, 2), {
      headers: corsJSON()
    })
  }
}



// ======================
// CORS HEADERS
// ======================

function corsJSON() {
  return {
    "content-type": "application/json;charset=UTF-8",
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Methods": "GET, HEAD, OPTIONS",
    "Access-Control-Allow-Headers": "*"
  }
}

function corsHTML() {
  return {
    "content-type": "text/html;charset=UTF-8",
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Methods": "GET, HEAD, OPTIONS",
    "Access-Control-Allow-Headers": "*"
  }
}



// ======================
// GET USER DATA + CACHE
// ======================

async function getUserData(username, origin, ctx, newinfo = false) {
  const cache = caches.default;
  const cacheKey = new Request("https://mcskin-cache/" + username.toLowerCase());

  // CHỈ LẤY TỪ CACHE NẾU KHÔNG YÊU CẦU LẤY THÔNG TIN MỚI (newinfo=false)
  if (!newinfo) {
    const cached = await cache.match(cacheKey);
    if (cached) {
      return await cached.json();
    }
  }

  try {
    const profileRes = await fetch(
      "https://api.mojang.com/users/profiles/minecraft/" + encodeURIComponent(username),
      { cf: { cacheTtl: newinfo ? 0 : 86400 } } // Nếu newinfo=true, không cache profile
    );

    if (!profileRes.ok) return null;
    const profile = await profileRes.json();
    const uuid = profile.id;
    const name = profile.name;

    if (!uuid) return null;

    const sessionRes = await fetch(
      `https://sessionserver.mojang.com/session/minecraft/profile/${uuid}`,
      { cf: { cacheTtl: newinfo ? 0 : 86400 } } // Nếu newinfo=true, không cache session
    );

    if (!sessionRes.ok) return null;
    const sessionData = await sessionRes.json();
    const value = sessionData?.properties?.[0]?.value;

    if (!value) return null;

    const decoded = JSON.parse(atob(value));

    // DỮ LIỆU LUÔN TRẢ VỀ ĐẦY ĐỦ (Bao gồm cả các link render)
    const result = {
      name: name,
      uuid: uuid,
      skin: decoded?.textures?.SKIN?.url ?? null,
      cape: decoded?.textures?.CAPE?.url ?? null,
      skin3d: `${origin}/api/3dskin/${encodeURIComponent(name)}`,
      // Luôn có các trường này vì bạn đã yêu cầu mặc định có đủ thông tin
      skin_render: `https://visage.surgeplay.com/full/512/${uuid}`,
      head: `https://minotar.net/helm/${uuid}/100`,
      body: `https://minotar.net/body/${uuid}/300`
    };

    const response = new Response(JSON.stringify(result), {
      headers: {
        "Content-Type": "application/json",
        "Cache-Control": newinfo ? "no-cache" : "public, max-age=86400"
      }
    });

    // Chỉ cache nếu không phải là yêu cầu lấy info mới
    if (!newinfo) {
      ctx.waitUntil(cache.put(cacheKey, response.clone()));
    }

    return result;
  } catch (e) {
    return null;
  }
}



// ======================
// API DOCS PAGE
// ======================

function showDocs(origin) {
  return `
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abc’s Noob | API Documentation</title>
    <style>
        :root { --primary: #58a6ff; --bg: #0d1117; --card: #161b22; --text: #c9d1d9; --accent: #238636; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); padding: 40px 20px; line-height: 1.6; }
        .container { max-width: 900px; margin: auto; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid #30363d; margin: 20px 0; }
        input, select { background: #0d1117; border: 1px solid #30363d; color: white; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        button { background: var(--accent); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        pre { background: #000; padding: 15px; border-radius: 6px; overflow-x: auto; color: #79c0ff; border: 1px solid #30363d; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #30363d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 Abc’s Noob Minecraft API</h1>
        
        

        <h2>🚀 Try It Yourself</h2>
        <div class="card">
            <input type="text" id="username" placeholder="Nhập username (VD: Dream)">
            <select id="endpoint">
                <option value="/api/">/api/ (JSON Data)</option>
                <option value="/api/3dskin/">/api/3dskin/ (3D Render)</option>
            </select>
            <label><input type="checkbox" id="param"> Tham số (newinfo/cape=true)</label>
            <button onclick="runTest()">Gửi yêu cầu</button>
            <div id="result-area" style="display:none;">
                <p>Kết quả:</p>
                <pre id="output">Đang tải...</pre>
            </div>
        </div>

        <h2>📜 Bảng mã trạng thái (Status Codes)</h2>
        <div class="card">
            <table>
                <tr><th>Code</th><th>Mô tả</th></tr>
                <tr><td>200</td><td>Thành công: Dữ liệu được trả về.</td></tr>
                <tr><td>404</td><td>Không tìm thấy: Username không tồn tại hoặc tài khoản Crack.</td></tr>
                <tr><td>500</td><td>Lỗi Server: Mojang API hoặc Worker gặp sự cố.</td></tr>
            </table>
        </div>

        <h2>🛠 Thông số API chi tiết</h2>
        <div class="card">
            <p><strong>?newinfo=true</strong>: Bypass Cache, ép buộc lấy dữ liệu tươi nhất từ Mojang.</p>
            <p><strong>?cape=true</strong>: Chỉ áp dụng cho 3dskin, hiển thị áo choàng của người chơi.</p>
        </div>
    </div>

    <script>
        async function runTest() {
            const user = document.getElementById('username').value;
            const path = document.getElementById('endpoint').value;
            const isParam = document.getElementById('param').checked;
            const query = isParam ? (path.includes('3dskin') ? '?cape=true' : '?newinfo=true') : '';
            const url = '${origin}' + path + user + query;
            
            document.getElementById('result-area').style.display = 'block';
            const output = document.getElementById('output');
            
            try {
                const res = await fetch(url);
                if(path.includes('3dskin')) {
                    output.innerHTML = "Trình duyệt đang tải 3D Skin tại: <a href='" + url + "' target='_blank'>" + url + "</a>";
                } else {
                    const data = await res.json();
                    output.textContent = JSON.stringify(data, null, 2);
                }
            } catch(e) { output.textContent = "Lỗi kết nối!"; }
        }
    </script>
</body>
</html>
  `;
}



// ======================
// 3D SKIN VIEW PAGE (ĐÃ CẬP NHẬT)
// ======================
function skin3DPage(user, showCape=false) {
  // Cơ chế Fallback: Nếu không có user.head, tự tạo từ uuid
  const favicon = user.head || `https://minotar.net/helm/${user.uuid}/100`;

  return `
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Skin 3D: ${user.name || "Người chơi"}</title>
  <link rel="icon" href="${favicon}" type="image/png">
<style>

*{
box-sizing:border-box;
margin:0;
padding:0;
}

body{
background:#121212;
color:#eee;
font-family:Arial, sans-serif;
text-align:center;
padding:20px;
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;
min-height:100vh;
}

#skin_container{
width:100%;
max-width:360px;
aspect-ratio:3/4;
margin:auto;
}

canvas{
width:100% !important;
height:100% !important;
display:block;
}

p{
margin-top:20px;
font-size:14px;
}

a{
color:#7CFC00;
text-decoration:none;
}

</style>

<script src="https://cdn.jsdelivr.net/npm/skinview3d@3.4.1/bundles/skinview3d.bundle.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

<style>

#nprogress .bar{
background:#7CFC00 !important;
height:6px !important;
}

#nprogress .peg{
box-shadow:0 0 10px #7CFC00,0 0 5px #7CFC00;
}

#nprogress .spinner-icon{
width:28px;
height:28px;
border:4px solid #7CFC00 !important;
border-top-color:transparent !important;
border-left-color:transparent !important;
border-radius:50%;
}

</style>

</head>

<body>

<div id="skin_container"></div>

<p>Made with ❤️ by <a href="https://abcsnoob.github.io">Abc's Noob</a></p>

<script>
    // Bắt đầu hiệu ứng loading
    NProgress.start();

    const viewer = new skinview3d.SkinViewer({
      canvas: document.createElement("canvas"),
      width: 360,
      height: 480
    });
    document.getElementById("skin_container").appendChild(viewer.canvas);
    
    // Tạo mảng các promise để theo dõi tiến trình tải
    const loadPromises = [viewer.loadSkin("${user.skin}")];
    
    ${showCape && user.cape ? `loadPromises.push(viewer.loadCape("${user.cape}"))` : ""}

    Promise.all(loadPromises).then(() => {
      // Kết thúc hiệu ứng loading khi tất cả đã tải xong
      NProgress.done();
      
      const username = "${user.name}".toLowerCase();
      viewer.playerObject.rotation.y = Math.PI / 2;

      // Logic xoay Dinnerbone
      if (username === "dinnerbone" || username === "grumm") {
        viewer.playerObject.rotation.z = Math.PI;
        viewer.playerObject.position.y = 2; 
        viewer.playerObject.rotation.y = -Math.PI / 40;
      }
    }).catch(() => {
      // Kết thúc loading nếu có lỗi xảy ra
      NProgress.done();
    });

    viewer.animation = new skinview3d.WalkingAnimation();
</script>
</body>

</html>

`
}
