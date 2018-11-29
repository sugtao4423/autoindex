# autoindex
nginxのautoindexが見づらすぎた

特にスマホでの表示が見づらかったのでそれに対応させた

## Nginx Configuration
```
location / {
    index index.php;
    try_files $uri /index.php?query_string;
}
```
