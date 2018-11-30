<?php
/*** config ***/
define('TIMEZONE', 'Asia/Tokyo');
define('TIMEFORMAT', 'Y-m-d H:i');
/**************/

date_default_timezone_set(TIMEZONE);

function getDirectoryPath(): string{
    return urldecode(preg_replace('|^/+|', '/', $_SERVER['REQUEST_URI']));
}

function getCurrentFiles(): array{
    $scriptDir = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME']) . '/';
    $ignoreFilesInScriptDir = [
        basename($_SERVER['SCRIPT_NAME']),
        'README.md',
        '.git'
    ];
    $path = $_SERVER['DOCUMENT_ROOT'] . getDirectoryPath();
    $files = [];
    $topDirSort = [];
    foreach(scandir($path) as $fileName){
        if(($fileName === '.') OR ($path === $scriptDir AND in_array($fileName, $ignoreFilesInScriptDir))){
            continue;
        }

        $fullPath = $path . $fileName;
        $files[] = [
            'name' => is_dir($fullPath) ? "${fileName}/" : $fileName,
            'time' => date(TIMEFORMAT, filemtime($fullPath)),
            'size' => calcFileSize(filesize($fullPath)),
            'isDir' => is_dir($fullPath)
        ];
        $topDirSort[] = is_dir($fullPath);
    }

    array_multisort($topDirSort, SORT_DESC, SORT_NATURAL, $files);
    return $files;
}

function calcFileSize(int $size): string{
    $b = 1024;
    $mb = pow($b, 2);
    $gb = pow($b, 3);

    switch(true){
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
    return  number_format($new_size, 2, '.', ',') . $unit;
}

function getFilesTable(): string{
    $rows = '<table><tbody>';
    $files = getCurrentFiles();
    foreach($files as $file){
        $rows .= '<tr>';
        $rows .= "<td><a href=\"{$file['name']}\">{$file['name']}</td>";
        $rows .= "<td>{$file['time']}</td>";
        if($file['isDir']){
            $rows .= '<td class="center">-</td>';
        }else{
            $rows .= "<td>{$file['size']}</td>";
        }
        $rows .= '</tr>';
    }
    return "${rows}</tbody></table>";
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Index of <?= getDirectoryPath(); ?></title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <style>
            body { background-color: #fefefe; }
            h1 { font-size: 2em; margin-top: 0; }
            @media screen and (max-width: 736px){ h1 { font-size: 1.5em; } }
            .scroll { overflow: auto; }
            table { width: 100%; border-collapse: collapse; }
            table tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,0.05); }
            table tbody tr td { padding: 0.35em 1em; white-space: nowrap; }
            table tbody tr td:nth-child(1) { width: 80%; }
            table tbody tr td:nth-child(2) { width: 15%; }
            table tbody tr td:nth-child(3) { width: 5%; text-align: right; }
            .center { text-align: center !important; }
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
