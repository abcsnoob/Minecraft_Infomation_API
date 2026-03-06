
# Abc's NoobMC Skin Viewer

A lightweight JavaScript library to search and display Minecraft player skins directly on your website.

## Features

- Search Minecraft username
- 3D skin viewer
- Cape support
- Download skin / cape
- Copy curl API command
- Beautiful alerts and loading

## Installation

Include the script:

```html
<script src="https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft_Infomation_API@main/dist/abcsmc.min.js"></script>
````

Create container:

```html
<div id="abcsmc"></div>
```

Initialize:

```javascript
abcsnoobmc.init()
```

## Demo

Open:

```
demo/index.html
```

## API

```
https://mcskin.abcsnoob.workers.dev/api/<username>
```

Example response:

```json
{
  "name": "Notch",
  "uuid": "069a79f444e94726a5befca90e38aaf5",
  "skin": "...",
  "cape": "...",
  "skin3d": "..."
}
```

## License

MIT


---
```html
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>ABCSNoobMC Skin Viewer Demo</title>
</head>

<body>

<h2>Minecraft Skin Viewer Demo</h2>

<div id="abcsmc"></div>

<script src="https://cdn.jsdelivr.net/gh/abcsnoob/Minecraft_Infomation_API@main/dist/abcsmc.min.js"></script>

<script>
abcsnoobmc.init({
  containerId: "abcsmc",
  apiUrl: "https://mcskin.abcsnoob.workers.dev/api/"
});
</script>

</body>
</html>
```
