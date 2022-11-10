# MR-UNRED-BACK
## 会議室予約システムのバックエンドサーバー
MR-UNRED-BACKは会議室予約システムのバックエンドサーバーを提供します。  
フロントエンドには[こちら](https://github.com/Hansyo/MR-UNRED-FRONT)をご利用ください。

## 提供中API
[会議室予約API](documents/reserve-api.md)  
[会議室情報API](documents/room-api.md)  

## 環境構築
1. [Sailを使ってコンテナを起動](#Sailを使ってコンテナを起動-1)
1. [データベースの構築](#データベースの構築-1)

### aliasの登録
オプションですが、sailコマンドを簡単に使えるようにaliasを設定しておくと良いです。  
以降では、alias設定がされている前提で記述します。  
**以前追加したaliasがある場合、削除してから行ってください**  
使用している`shell`毎に保存先は変更してください。(Ubuntuのデフォルトは`bash`です)
```bash
echo "alias sail='[ -f sail ] && bash sail'" >> ~/.bashrc
```
使っているshellの設定ファイルを再読込します。
```bash
source ~/.bashrc
```


### Sailを使ってコンテナを起動
#### `src/.env`を作成
<details>
<summary> `.env`のサンプル </summary>

```
APP_NAME=MR-UNRED
APP_SERVICE="mr-unred"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://mr-unred
CLIENT_BASE_URL=http://localhost:3000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=mr_unred
DB_USERNAME=mr_unred
DB_PASSWORD='mr_unred'

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=memcached

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=

AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
```

</details>

サンプル内のパスワード等は変更することをおすすめします。

### ビルド
Sailコマンドを使ってDockerイメージを作成します。  
```bash
sail build --no-cache
```

#### コンテナの起動
Sailコマンドを使ってDockerコンテナを起動します。  
途中で`Error laravel.test`みたいに出てきますが、問題ないので無視して下さい。
```bash
sail up -d
```

#### laravelのインストール
Sail + Composer を使ってlaravelをインストールします・
```bash
sail composer install
```

#### `APP_KEY`の生成
Sail + Artisan を使用して`.env`内の`APP_KEY`を生成します。
```bash
sail artisan key:generate
```

#### コンテナの再起動
Sailを使用して、コンテナを再起動してください。
```bash
sail restart
```

### データベースの構築
sailコマンドを使ってデータベースを構築します。  
コンテナが起動している状態で、
```bash
sail artisan migrate
sail artisan db:seed
```
を行ってください。

#### データベースの再構築
sailコマンドを使ってデータベースを**再構築**します。  
コンテナが起動している状態で、
```bash
sail artisan migrate:fresh
sail artisan db:seed
```
を行ってください。
