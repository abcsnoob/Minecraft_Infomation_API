
<?php
// Chuyển hướng vĩnh viễn từ advancedsearch.php sang /api/
$request_uri = $_SERVER['REQUEST_URI'];

// Nếu không phải /api/ thì redirect
if (strpos($request_uri, '/api/') === false) {
    header("Location: /api/", true, 301);
    exit;
}

date_default_timezone_set("Asia/Ho_Chi_Minh");
header('Access-Control-Allow-Origin: *');

function getUserData($username) {
    $mojangURL = "https://api.mojang.com/users/profiles/minecraft/" . urlencode($username);
    $profile = @file_get_contents($mojangURL);
    if (!$profile) return null;

    $data = json_decode($profile, true);
    $uuid = $data['id'] ?? null;
    $name = $data['name'] ?? null;
    if (!$uuid || !$name) return null;

    $sessionURL = "https://sessionserver.mojang.com/session/minecraft/profile/$uuid";
    $session = @file_get_contents($sessionURL);
    if (!$session) return null;

    $sessionData = json_decode($session, true);
    if (!isset($sessionData['properties'][0]['value'])) return null;

    $props = base64_decode($sessionData['properties'][0]['value']);
    $propsDecoded = json_decode($props, true);

    return [
        "name" => $name,
        "uuid" => $uuid,
        "skin" => $propsDecoded['textures']['SKIN']['url'] ?? null,
        "cape" => $propsDecoded['textures']['CAPE']['url'] ?? null,
        "skin3d" => "https://abcsnoobmcname.42web.io/api/3dskin/" . urlencode($name),
    ];
}

// Lấy phần sau /api/ trong URL để phân tích
$path = parse_url($request_uri, PHP_URL_PATH);
$parts = explode('/', trim($path, '/')); // bỏ dấu '/' đầu cuối

// $parts[0] phải là 'api'
if (count($parts) < 2) {
    // Hiển thị trang tài liệu API nếu chỉ /api/ thôi
    showDocs();
    exit;
}

$endpoint = $parts[1] ?? '';

// Xử lý endpoint 3dskin hoặc username
if ($endpoint === '3dskin') {
    // Lấy username từ query hoặc path tiếp theo
    $username = $_GET['username'] ?? ($parts[2] ?? '');
    $username = trim($username);

    if ($username === '') {
        echo "Thiếu tên người chơi (username) để hiển thị skin 3D.";
        exit;
    }

    // Lấy thông tin user (chỉ cần uuid)
    $userData = getUserData($username);
    if (!$userData) {
        echo "Không tìm thấy người chơi Java tên '$username'.";
        exit;
    }

    // Trả về HTML render skin 3D embed
    echo skin3DPage($userData);
    exit;
} else {
    // Xử lý trường hợp /api/{username}
    $username = $endpoint;

    if ($username === '') {
        echo json_encode(['error' => 'Thiếu tên người chơi.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
    $data = getUserData($username);

    if (!$data) {
        echo json_encode([
            'error' => "Không tìm thấy người chơi Java tên '$username'.",
            'note' => "Có thể người chơi chưa mua Minecraft Java hoặc là tài khoản Bedrock/crack."
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Hàm in trang tài liệu API mặc định
function showDocs() {
    ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>Abc’s Noob Minecraft API</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      background-color: #0d1117;
      color: #c9d1d9;
      font-family: Consolas, monospace;
      padding: 40px;
      line-height: 1.6;
    }

    .section {
      margin-bottom: 30px; /* khoảng cách lớn giữa các section */
      padding: 15px;
      background-color: #161b22; /* nền tối nhẹ để nổi bật */
      border-radius: 8px;
      border: 1px solid #30363d;
      overflow: auto; /* đảm bảo nội dung không tràn */
      position: relative;
    }

    input, button {
      vertical-align: middle; /* canh dòng đẹp hơn */
    }

    pre, code {
      background-color: #161b22;
      padding: 10px 40px 10px 10px;
      border-left: 4px solid #58a6ff;
      border-radius: 6px;
      overflow-x: auto;
      max-height: 300px;
      display: block;
      white-space: pre-wrap;
      word-wrap: break-word;
      position: relative;
    }

    h1, h2 { color: #58a6ff; }
    a { color: #58a6ff; text-decoration: none; }

    /* Copy button styles */
    .copy-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #21262d;
      border: none;
      color: #58a6ff;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 5px;
      font-size: 13px;
      transition: background-color 0.3s;
      display: flex;
      align-items: center;
      gap: 5px;
      user-select: none;
    }
    .copy-btn:hover {
      background-color: #58a6ff;
      color: #0d1117;
    }
    .copy-btn svg {
      width: 16px;
      height: 16px;
      fill: currentColor;
    }

    /* ChatGPT help icon */
    .help-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 18px;
      height: 18px;
      background-color: #58a6ff;
      color: #0d1117;
      font-weight: bold;
      border-radius: 50%;
      font-size: 14px;
      cursor: pointer;
      margin-left: 6px;
      user-select: none;
      position: relative;
      transition: background-color 0.3s;
      text-decoration: none;
    }
    .help-icon:hover {
      background-color: #1f6feb;
    }
    .help-icon::after {
      content: attr(data-tooltip);
      position: absolute;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      background-color: #21262d;
      color: #c9d1d9;
      padding: 6px 10px;
      border-radius: 5px;
      white-space: nowrap;
      font-size: 12px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s;
      z-index: 10;
    }
    .help-icon:hover::after {
      opacity: 1;
      pointer-events: auto;
    }
  </style>
</head>
<body>
  <h1>🎮 Abc’s Noob Minecraft User API 
    <a href="https://chat.openai.com/" target="_blank" rel="noopener" class="help-icon" data-tooltip="Hỏi ChatGPT về API này">?</a>
  </h1>

  <div class="section">
    <h2>🔎 Chức năng chính</h2>
    <ul>
      <li>Tra cứu thông tin người chơi Minecraft Java Edition (name, UUID, skin, cape, skin3d)</li>
      <li>Tích hợp dễ dàng vào website hoặc ứng dụng khác bằng <code>fetch</code>, <code>axios</code>, hoặc HTTP client bất kỳ</li>
      <li>Hiển thị skin 3D dạng embed theo username</li>
    </ul>
  </div>

  <div class="section">
    <h2>📌 Cách sử dụng cơ bản</h2>

    <h3>Bước 1: Lấy thông tin người chơi</h3>
    <p>Gửi yêu cầu <code>GET</code> tới API với username của người chơi:</p>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>GET https://abcsnoobmcname.42web.io/api/&lt;tên-người-chơi&gt;</code>
    </pre>
    <p>Ví dụ:</p>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>https://abcsnoobmcname.42web.io/api/Dream</code>
    </pre>
    <p>Kết quả trả về sẽ là JSON chứa thông tin:</p>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>{
  "name": "Dream",
  "uuid": "ec70bcaf702f4bb8b48d276fa52a780c",
  "skin": "http://textures.minecraft.net/texture/ca93f6fc40488f1877cda94a830b54e9f6f54ab58a5453bad5c947726dd1f473",
  "cape": null,
  "skin3d": "https://abcsnoobmcname.42web.io/api/3dskin/Dream"
}</code>
    </pre>

    <h3>Bước 2: Lấy skin 3D dạng embed</h3>
    <p>Gửi yêu cầu <code>GET</code> để lấy link embed skin 3D:</p>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>GET https://abcsnoobmcname.42web.io/api/3dskin?username=&lt;tên-người-chơi&gt;</code>
    </pre>
    <p>Hoặc:</p>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>https://abcsnoobmcname.42web.io/api/3dskin/&lt;tên-người-chơi&gt;</code>
    </pre>
    <p>Ví dụ:</p>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>https://abcsnoobmcname.42web.io/api/3dskin/Dream</code>
    </pre>
  </div>

  <div class="section">
    <h2>💻 Ví dụ sử dụng API với nhiều ngôn ngữ lập trình</h2>

    <h3>1. JavaScript (Fetch API)
      <a href="https://chat.openai.com/" target="_blank" rel="noopener" class="help-icon" data-tooltip="Hỏi ChatGPT về JavaScript Fetch">?</a>
    </h3>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>fetch('https://abcsnoobmcname.42web.io/api/Dream')
  .then(res =&gt; res.json())
  .then(data =&gt; {
    console.log('Tên:', data.name);
    console.log('UUID:', data.uuid);
    console.log('Skin:', data.skin);
    console.log('Cape:', data.cape);
    console.log('Skin 3D:', data.skin3d);
  })
  .catch(err =&gt; console.error('Lỗi:', err));
</code>
    </pre>

    <h3>2. JavaScript (Axios)
      <a href="https://chat.openai.com/" target="_blank" rel="noopener" class="help-icon" data-tooltip="Hỏi ChatGPT về Axios">?</a>
    </h3>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>axios.get('https://abcsnoobmcname.42web.io/api/Dream')
  .then(response =&gt; {
    const data = response.data;
    console.log('Tên:', data.name);
    console.log('UUID:', data.uuid);
    console.log('Skin:', data.skin);
    console.log('Cape:', data.cape);
    console.log('Skin 3D:', data.skin3d);
  })
  .catch(error =&gt; console.error('Lỗi:', error));
</code>
    </pre>

    <h3>3. Python (requests)
      <a href="https://chat.openai.com/" target="_blank" rel="noopener" class="help-icon" data-tooltip="Hỏi ChatGPT về Python requests">?</a>
    </h3>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>import requests

url = 'https://abcsnoobmcname.42web.io/api/Dream'
try:
    response = requests.get(url)
    response.raise_for_status()
    data = response.json()
    print('Tên:', data['name'])
    print('UUID:', data['uuid'])
    print('Skin:', data['skin'])
    print('Cape:', data['cape'])
    print('Skin 3D:', data['skin3d'])
except requests.RequestException as e:
    print('Lỗi:', e)
</code>
    </pre>

    <h3>4. PHP (curl)
      <a href="https://chat.openai.com/" target="_blank" rel="noopener" class="help-icon" data-tooltip="Hỏi ChatGPT về PHP curl">?</a>
    </h3>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>&lt;?php
$url = 'https://abcsnoobmcname.42web.io/api/Dream';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if ($data) {
    echo "Tên: " . $data['name'] . "\n";
    echo "UUID: " . $data['uuid'] . "\n";
    echo "Skin: " . $data['skin'] . "\n";
    echo "Cape: " . $data['cape'] . "\n";
    echo "Skin 3D: " . $data['skin3d'] . "\n";
} else {
    echo "Lỗi khi lấy dữ liệu";
}
?&gt;
</code>
    </pre>

    <h3>5. C# (.NET HttpClient)
      <a href="https://chat.openai.com/" target="_blank" rel="noopener" class="help-icon" data-tooltip="Hỏi ChatGPT về C# HttpClient">?</a>
    </h3>
    <pre>
      <button class="copy-btn" title="Copy đoạn này" aria-label="Copy đoạn này">
        <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy
      </button>
      <code>using System;
using System.Net.Http;
using System.Threading.Tasks;
using Newtonsoft.Json.Linq;

class Program
{
    static async Task Main()
    {
        var url = "https://abcsnoobmcname.42web.io/api/Dream";
        using HttpClient client = new HttpClient();

        try
        {
            string response = await client.GetStringAsync(url);
            var data = JObject.Parse(response);

            Console.WriteLine("Tên: " + data["name"]);
            Console.WriteLine("UUID: " + data["uuid"]);
            Console.WriteLine("Skin: " + data["skin"]);
            Console.WriteLine("Cape: " + data["cape"]);
            Console.WriteLine("Skin 3D: " + data["skin3d"]);
        }
        catch (Exception ex)
        {
            Console.WriteLine("Lỗi: " + ex.Message);
        }
    }
}
</code>
    </pre>

  </div>

  <footer style="margin-top:40px; font-size:13px; color:#555;">
    © 2024–<?= date('Y') ?> Abc’s Noob API • Fanmade API – không thuộc Mojang/Microsoft.<br>
    Mojang mà chưa làm API dễ dùng thì... mình làm 😎
  </footer>

  <script>
    // Xử lý nút copy code
    document.querySelectorAll('.copy-btn').forEach(button => {
      button.addEventListener('click', () => {
        const codeBlock = button.nextElementSibling;
        if (!codeBlock) return;
        const text = codeBlock.innerText || codeBlock.textContent;
        navigator.clipboard.writeText(text).then(() => {
          button.textContent = 'Đã copy ✓';
          setTimeout(() => {
            button.innerHTML = `<svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy`;
          }, 2000);
        }).catch(() => {
          button.textContent = 'Lỗi copy ❌';
          setTimeout(() => {
            button.innerHTML = `<svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> Copy`;
          }, 2000);
        });
      });
    });
  </script>
</body>
</html>

<?php
}

// Hàm trả về HTML trang 3d skin
function skin3DPage(array $userData) {
    $name = htmlspecialchars($userData['name']);
    $uuid = htmlspecialchars($userData['uuid']);
    return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>Skin 3D của {$name}</title>
  <style>
    body {
      background-color: #121212;
      color: #eee;
      font-family: Arial, sans-serif;
      text-align: center;
      padding: 20px;
    }
    #skin_container {
      width: 300px;
      height: 420px;
      margin: auto;
    }
    a { color: #40c4ff; text-decoration: none; }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/skinview3d@3.4.1/bundles/skinview3d.bundle.js"></script>
</head>
<body>

  <div id="skin_container"></div>


  <script>
    const container = document.getElementById("skin_container");
    const canvas = document.createElement("canvas");
    container.appendChild(canvas);

    const viewer = new skinview3d.SkinViewer({
      canvas: canvas,
      width: container.clientWidth,
      height: container.clientHeight,
      skin: "https://crafatar.com/skins/{$uuid}"
    });

    viewer.controls.enableZoom = true;
    viewer.animation = new skinview3d.WalkingAnimation();
    viewer.animation.speed = 1;
  </script>
</body>
</html>
HTML;
}
?>
