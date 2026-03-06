export default {
  async fetch(request, env, ctx) {

    const url = new URL(request.url)
    const path = url.pathname

    const newinfo = url.searchParams.get("newinfo") === "true"
    const showCape = url.searchParams.get("cape") === "true"

    if (!path.startsWith("/api")) {
      return Response.redirect(url.origin + "/api/", 301)
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

async function getUserData(username, origin, ctx, newinfo=false) {

  const cache = caches.default
  const cacheKey = new Request("https://mcskin-cache/" + username.toLowerCase())

  const cached = await cache.match(cacheKey)

  if (cached) {
    return await cached.json()
  }

  try {

    const profileRes = await fetch(
      "https://api.mojang.com/users/profiles/minecraft/" + encodeURIComponent(username),
      { cf: { cacheTtl: 86400 } }
    )

    if (!profileRes.ok) return null

    const profile = await profileRes.json()

    const uuid = profile.id
    const name = profile.name

    if (!uuid) return null


    const sessionRes = await fetch(
      `https://sessionserver.mojang.com/session/minecraft/profile/${uuid}`,
      { cf: { cacheTtl: 86400 } }
    )

    if (!sessionRes.ok) return null

    const sessionData = await sessionRes.json()

    const value = sessionData?.properties?.[0]?.value

    if (!value) return null

    const decoded = JSON.parse(atob(value))


    const result = {

      name: name,
      uuid: uuid,
      skin: decoded?.textures?.SKIN?.url ?? null,
      cape: decoded?.textures?.CAPE?.url ?? null,
      skin3d: `${origin}/api/3dskin/${encodeURIComponent(name)}`

    }


    // ======================
    // NEW INFO
    // ======================

    if (newinfo) {

      result.skin_render = `https://visage.surgeplay.com/full/512/${uuid}`
      result.head = `https://minotar.net/helm/${uuid}/100`
      result.body = `https://minotar.net/body/${uuid}/300`

    }



    const response = new Response(JSON.stringify(result), {
      headers: {
        "Content-Type": "application/json",
        "Cache-Control": "public, max-age=86400"
      }
    })


    ctx.waitUntil(
      cache.put(cacheKey, response.clone())
    )

    return result

  }
  catch (e) {
    return null
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

<title>Abc’s Noob Minecraft API</title>

<style>

body{
background:#0d1117;
color:#c9d1d9;
font-family:Consolas,monospace;
padding:40px;
line-height:1.6
}

pre{
background:#161b22;
padding:15px;
border-left:4px solid #58a6ff;
border-radius:6px
}

h1,h2{
color:#58a6ff
}

</style>

</head>

<body>

<h1>🎮 Abc’s Noob Minecraft User API</h1>

<p>API tra cứu thông tin Minecraft Java.</p>

<h2>Lấy thông tin người chơi</h2>

<pre>
GET ${origin}/api/Dream
</pre>

<h2>Lấy info mới nhất</h2>

<pre>
GET ${origin}/api/Dream?newinfo=true
</pre>

<h2>Skin 3D</h2>

<pre>
${origin}/api/3dskin/Dream
</pre>

<h2>Skin 3D + Cape</h2>

<pre>
${origin}/api/3dskin/Dream?cape=true
</pre>

<footer style="margin-top:40px;color:#555">

© 2024-${new Date().getFullYear()} Abc’s Noob API<br>
Fanmade API – không thuộc Mojang/Microsoft.

</footer>

</body>

</html>
`
}



// ======================
// 3D SKIN VIEW PAGE
// ======================

function skin3DPage(user, showCape=false) {

return `

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Skin 3D của ${user.name}</title>

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

NProgress.start()

const container = document.getElementById("skin_container")

const canvas = document.createElement("canvas")
container.appendChild(canvas)

function getSize(){
return {
width: container.clientWidth,
height: container.clientHeight
}
}

const size = getSize()

const viewer = new skinview3d.SkinViewer({
canvas: canvas,
width: size.width,
height: size.height
})

viewer.loadSkin("${user.skin}").then(()=>{

${showCape && user.cape ? `viewer.loadCape("${user.cape}")` : ""}

NProgress.done()

}).catch(()=>{
NProgress.done()
})

viewer.controls.enableZoom = true
viewer.animation = new skinview3d.WalkingAnimation()
viewer.animation.speed = 1

function resize(){
const size = getSize()
viewer.setSize(size.width, size.height)
}

window.addEventListener("resize", resize)

</script>

</body>

</html>

`
}
