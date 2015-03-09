<?php
// Base URL of the website
$baseurl = "https://i.3v.fi/";

// Twitter handle for twitter card
$twitter = "";

// Google Analytics tracking code (or some other thing to add on every page)
$analytics = <<<EOD

<script>

</script>

EOD;

if (!array_key_exists('file', $_GET)) {
    die("Error");
}
else if (preg_match('/^[^.]+$/', $_GET['file']) != 1 || (!file_exists($_GET['file'].'.'.$_GET['type']))) {
    header("HTTP/1.1 404 Not Found", true, 404);
    die("Not found");
}


$contentType;
switch($_GET['type']) {
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
    
    $twittercard = <<<EOD
        <meta name="twitter:card" content="photo" />
        <meta name="twitter:site" content="$twitter" />
        <meta name="twitter:title" content="{$_GET['file']}" />
        <meta name="twitter:description" content="Image by $twitter" />
        <meta name="twitter:image" content="{$baseurl}raw/{$_GET['file']}.{$_GET['type']}" />
        <meta name="twitter:url" content="$baseurl{$_GET['file']}.{$_GET['type']}" />
        
EOD;

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
        <style>
        body {
            background-color: #262626;
            margin: 0;
            padding: 0;
            text-align: center;
            color: #fff;
        }
        video {
            background-color: #000;
        }
        video, img {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            display: block;
            margin: auto;
            text-align: center;
            max-height: 90%;
            max-width: 100%;
        }
        </style>
        <?=$twittercard?>
    </head>
    <body>
        <?=$contentHeader?>
        <?=$content?>
        <?=$analytics?>
    </body>
</html>