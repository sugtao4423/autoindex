<?php

/*** config ***/
define('TIMEZONE', 'Asia/Tokyo');
define('TIMEFORMAT', 'Y-m-d H:i');
define('ENABLE_DIRSIZE', true);
/**************/

date_default_timezone_set(TIMEZONE);

if (preg_match('|/$|', $_SERVER['REQUEST_URI']) === 0) {
    $path = preg_replace('|^/*|', '', $_SERVER['REQUEST_URI']);
    http_response_code(301);
    header("Location: /{$path}/");
    exit(1);
}

function getDirectoryPath(): string
{
    return urldecode(preg_replace('|^/+|', '/', $_SERVER['REQUEST_URI']));
}

function getCurrentFiles(): array
{
    $scriptDir = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME']) . '/';
    $ignoreFilesInScriptDir = [
        basename($_SERVER['SCRIPT_NAME']),
        'README.md',
        '.git',
        '.gitignore'
    ];
    $path = $_SERVER['DOCUMENT_ROOT'] . getDirectoryPath();
    $files = [];
    $topDirSort = [];
    foreach (scandir($path) as $fileName) {
        if ($fileName === '.' || ($path === $scriptDir && in_array($fileName, $ignoreFilesInScriptDir))) {
            continue;
        }

        $fullPath = $path . $fileName;

        $isDir = is_dir($fullPath);
        $name = $isDir ? "{$fileName}/" : $fileName;
        $time = date(TIMEFORMAT, filemtime($fullPath));

        if ($isDir && ENABLE_DIRSIZE && $name !== '../') {
            $size = calcFileSize(dirsize($fullPath));
        } else {
            $size = calcFileSize(filesize($fullPath));
        }

        $files[] = [
            'name' => $name,
            'time' => $time,
            'size' => $size,
            'isDir' => $isDir
        ];
        $topDirSort[] = $isDir;
    }

    array_multisort($topDirSort, SORT_DESC, SORT_NATURAL, $files);
    return $files;
}

function dirsize(string $path): int
{
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $item) {
        $size += $item->getSize();
    }
    return $size;
}

function calcFileSize(int $size): string
{
    $b = 1024;
    $mb = pow($b, 2);
    $gb = pow($b, 3);

    switch (true) {
        case $size >= $gb:
            $target = $gb;
            $unit = 'GB';
            break;
        case $size >= $mb:
            $target = $mb;
            $unit = 'MB';
            break;
        default:
            $target = $b;
            $unit = 'KB';
            break;
    }

    $new_size = round($size / $target, 2);
    return number_format($new_size, 2, '.', ',') . $unit;
}

function getFilesTable(): string
{
    $rows = '<table><tbody>';
    $files = getCurrentFiles();
    foreach ($files as $file) {
        $rows .= '<tr>';
        $rows .= "<td><a href=\"{$file['name']}\">{$file['name']}</td>";
        $rows .= "<td>{$file['time']}</td>";
        if ((!$file['isDir'] || ENABLE_DIRSIZE) && $file['name'] !== '../') {
            $rows .= "<td>{$file['size']}</td>";
        } else {
            $rows .= '<td class="center">-</td>';
        }
        $rows .= '</tr>';
    }
    return "{$rows}</tbody></table>";
}
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>Index of <?= getDirectoryPath(); ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body {
            background-color: #fefefe;
        }

        h1 {
            font-size: 2em;
            margin-top: 0;
        }

        @media screen and (max-width: 736px) {
            h1 {
                font-size: 1.5em;
            }
        }

        .scroll {
            overflow: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        table tbody tr td {
            padding: 0.35em 1em;
            white-space: nowrap;
        }

        table tbody tr td:nth-child(1) {
            width: 80%;
        }

        table tbody tr td:nth-child(2) {
            width: 15%;
        }

        table tbody tr td:nth-child(3) {
            width: 5%;
            text-align: right;
        }

        .center {
            text-align: center !important;
        }
    </style>
</head>

<body>
    <h1>Index of <?= getDirectoryPath(); ?></h1>
    <hr>
    <div class="scroll">
        <?= getFilesTable(); ?>
    </div>
    <hr>
</body>

</html>