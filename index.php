<?php
// Base URL of the website
$baseurl = "";

// Twitter handle for twitter card
$twitter = "";

// Google Analytics tracking code (or some other thing to add on every page)
$analytics = <<<EOD

EOD;

if (!array_key_exists('file', $_GET) || !array_key_exists('type', $_GET)) {
    die("Missing file $analytics");
}
else if (preg_match('/^[^.]+$/', $_GET['file']) != 1 || (!file_exists($_GET['file'].'.'.$_GET['type']))) {
    header("HTTP/1.1 404 Not Found", true, 404);
    die("Not found $analytics");
}


$contentType;
switch($_GET['type'])
{
    case "txt": $contentType = "text/plain"; break;
    case "json": $contentType = "application/json"; break;
    default: $contentType = getMimeFromType($_GET['file'] . '.' . $_GET['type']);
}

header("X-Content-Type: " . $contentType);

$twittercard = "";
$contentHeader = "";
$content = "";

if (isset($_GET['raw']))
{
    sendRawFile($contentType);
}
else if (startsWith($contentType, "video/"))
{
    if (startsWith($contentType, "video/mp4"))
        $contentHeader = 'mp4 [' . getFileSizeInMB($_GET['file'] . '.mp4') . ' MB] | <a href="' . $_GET['file'] . '.gif" style="color:#aaf">gif [' . getFileSizeInMB($_GET['file'] . '.gif') . ' MB]</a>';
    
    $content = <<<EOD
        <video loop="loop" autoplay="autoplay">
            <source src="/raw/{$_GET['file']}.{$_GET['type']}" type="$contentType"></source>
            Your browser does not support the <code>video</code> element.
        </video>
    
EOD;

}
else if (startsWith($contentType, "image/"))
{
    if (startsWith($contentType, "image/gif"))
        $contentHeader = '<a href="' . $_GET['file'] . '.mp4" style="color:#aaf">mp4 [' . getFileSizeInMB($_GET['file'] . '.mp4') . ' MB]</a> | gif [' . getFileSizeInMB($_GET['file'] . '.gif') . ' MB]';
    else
    {
        list($width, $height, $type, $attr) = getimagesize($_GET['file'] . '.' . $_GET['type']);
        $contentHeader = '<a href="/raw/' . $_GET['file'] . '.' . $_GET['type'] . '" style="color:#aaf">View raw ' . $_GET['type'] . ' [' . $width . 'x' . $height . ']</a>';
    }
    
    $content = <<<EOD
        <img id="image" src="/raw/{$_GET['file']}.{$_GET['type']}" alt="{$_GET['file']}.{$_GET['type']}"/>
        <script>
        var img = document.getElementById("image");
        function resize()
        {
            img.style.marginTop = (window.innerHeight - img.height) / 2;
        }
        window.onresize = resize;
        window.onload = resize;
        </script>
    
EOD;
    $contentTypeShort = explode('/', $contentType)[0];
    $contentTypePlain = explode(';', $contentType)[0];
    $twittercard = <<<EOD
        <meta property="og:site_name" content="3v.fi" />
        <meta property="og:url" content="$baseurl{$_GET['file']}.{$_GET['type']}" />
        <meta property="og:title" content="3v.fi image {$_GET['file']}" />
        <meta property="og:type" content="$contentTypeShort" />
        <meta property="og:description" content="Image by $twitter" />
        <meta property="og:$contentTypeShort" content="{$baseurl}raw/{$_GET['file']}.{$_GET['type']}" />
        <meta property="og:$contentTypeShort:width" content="$width" />
        <meta property="og:$contentTypeShort:height" content="$height" />
        <meta property="og:$contentTypeShort:type" content="$contentTypePlain" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="$twitter" />
        <meta name="twitter:creator" content="$twitter" />
        
EOD;

}
else if (in_array($contentType, ["text/plain", "application/json"]) || startsWith($contentType, "text/"))
{
    // Code highlight
    $c = file_get_contents($_GET['file'] . '.' . $_GET['type']);
    $content = "";
    if (startsWith($c, "# ")) $content .= "<button id=\"toggle-source\">Toggle markdown source</button>";
    $content .= "<div id=\"source\" style=\"display:block\"><pre><code id=\"source-code\">" . htmlspecialchars($c). "</code></pre></div>";
    if (startsWith($c, "# ")) $content .= "<div id=\"parsed\" style=\"display:none\"></div>";
}
else
{
    sendRawFile($contentType);
}


function getMimeFromType($file)
{
    $fileinfo = new finfo(FILEINFO_MIME);
    $mime = $fileinfo->file($file);
    return $mime;
}


function startsWith($haystack, $needle)
{
    return substr($haystack, 0, strlen($needle)) === $needle;
}


function getFileSizeInMB($file)
{
    return round(filesize($file) / 1048576*10)/10;
}


function sendRawFile($contentType)
{
    header("Content-Type: " . $contentType);
    readfile($_GET['file'].'.'.$_GET['type']);
    die;
}


?><!DOCTYPE html>
<html>
    <head>
        <title><?=$_GET['file'].'.'.$_GET['type']?></title>
        <meta name=viewport content="width=device-width, initial-scale=1">
        <style>
        body{background-color:#262626;margin:0;padding:0;text-align:center;color:#fff;tab-size:4}video{background-color:#000}img,video{position:absolute;top:0;bottom:0;left:0;right:0;display:block;margin:auto;text-align:center;max-height:90%;max-width:100%}pre{text-align:left}
        #parsed{text-align:left;font-family:Georgia,Cambria,serif;padding:1em;color:#ccc}#parsed h1,#parsed h2,#parsed h3{margin-top:3em}a{color:#93c763}p code,ul code{color:#F2BBA5}
        </style>
        <?=$twittercard?>
    </head>
    <body>
        <?=$contentHeader?>
        <?=$content?>
        <?=$analytics?>
        <script src="/highlightjs/highlight.pack.js"></script>
        <script src="/static/markdown.min.js"></script>
        <script type="text/javascript">
        var p = document.getElementById('parsed');
        var s = document.getElementById('source');
        var state = true;
        if (p && s)
        {
            var e = document.getElementById('source-code');
            p.innerHTML = markdown.toHTML(e.innerText || e.textContent);
        }
        var b=document.getElementById('toggle-source');
        if (b) md();
        b.onclick = md;
        function md()
        {
            if (state)
            {
                p.style.display = "block";
                s.style.display = "none";
            }
            else
            {
                p.style.display = "none";
                s.style.display = "block";
            }
            state = !state;
        }
        if (hljs) {
            hljs.initHighlightingOnLoad();
            var hlstyle = document.createElement("link");
            hlstyle.setAttribute("rel", "stylesheet");
            hlstyle.setAttribute("type", "text/css");
            hlstyle.setAttribute("href", "/highlightjs/styles/obsidian.min.css");
            document.getElementsByTagName("head")[0].appendChild(hlstyle);
        }
        </script>
    </body>
</html>
