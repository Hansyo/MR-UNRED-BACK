<details>
<summary> 目次 </summary>

1. [概要](#概要)
1. [ステータスコード](#ステータスコード)
1. [新規登録](#新規登録)
   1. [Request](#request)
      1. [Request Body](#request-body)
   1. [Response](#response)
2. [一覧取得](#一覧取得)
   1. [Request](#request-1)
      1. [Request Body](#request-body-1)
   2. [Response](#response-1)
3. [詳細情報取得](#詳細情報取得)
   1. [Request](#request-2)
      1. [Request Body](#request-body-2)
   2. [Response](#response-2)
4. [詳細情報の更新](#詳細情報の更新)
   1. [Request](#request-3)
      1. [Request Body](#request-body-3)
   2. [Response](#response-3)
5. [削除](#削除)
   1. [Request](#request-4)
      1. [Request Body](#request-body-4)
   2. [Response](#response-4)

</details>

# 概要

会議室情報管理APIについて仕様を説明します。  
|        ホスト         | プロトコル | データ形式 |
| :-------------------: | :--------: | :--------: |
| localhost(開発中のみ) |    http    |    JSON    |

# ステータスコード

変更される可能性があります。  
| ステータスコード | 説明                                      |
| ---------------- | ----------------------------------------- |
| 200              | リクエスト成功                            |
| 201              | 作成成功                                  |
| 204              | No content                                |
| 404              | 存在しないURLにアクセス                   |
| 422              | 処理できないコンテンツ                    |
| 500              | 不明なエラー                              |

# 新規登録

新しく、会議室を登録することができます。  

## Request

`POST /api/rooms/`

### Request Body

| フィールド名 | 形式     | 内容                         | 必須 | 備考 |
| ------------ | -------- | ---------------------------- | ---- | ---- |
| `name`       | `string` | 会議室の名前                 | ○    |      |
| `detail`     | `string` | 会議室の詳細。利用可能人数等 | ○    |      |

<details>
<summary> Bodyの例 </summary>

```json
{
  "name":"会議室1",
  "detail":"受入人数: 10人"
}
```

</details>

## Response

会議室の登録が成功した場合は下記のようなレスポンスが返ってきます。  
`HTTP/1.1 201 Created`

```json
{
  "name":"会議室1",
  "detail":"受入人数: 10人",
  "updated_at":"2022-01-28T02:37:45.000000Z",
  "created_at":"2022-01-28T02:37:45.000000Z",
  "id":11
}
```

登録のさい、形式が間違っている場合に下記のようなレスポンスが返ってきます。  
`HTTP/1.1 422 Unprocessable Content`

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required."
        ]
    }
}
```

# 一覧取得

全ての会議室の情報を取得することができます。

## Request

`GET /api/rooms`

### Request Body

なし

## Response

取得に成功した場合は下記のようなステータスとjsonが返ってきます。  
`HTTP/1.1 200 OK`

```json
[
    {
        "id": 1,
        "name": "会議室1",
        "detail": "詳細はありません。",
        "created_at": "2022-02-16T07:03:52.000000Z",
        "updated_at": "2022-02-16T07:03:52.000000Z"
    },
    {
        "id": 2,
        "name": "会議室2",
        "detail": "詳細はありません。",
        "created_at": "2022-02-16T07:03:52.000000Z",
        "updated_at": "2022-02-16T07:03:52.000000Z"
    }
]
```

# 詳細情報の取得

指定された会議室について、詳細情報を取得することができます。  

## Request

`GET /api/rooms/{id}`

### Request Body

なし

## Response

取得に成功した場合は下記のようなステータスとjsonが返ってきます。  
`HTTP/1.1 200 OK`

```json
{
    "id": 2,
    "name": "会議室2",
    "detail": "詳細はありません。",
    "created_at": "2022-02-16T07:03:52.000000Z",
    "updated_at": "2022-02-16T07:03:52.000000Z"
}
```

取得に失敗した場合は下記のようなステータスとjsonが返ってきます。  
**JSONが返ってくるようにする予定ですが、現状はhtmlが飛んできます。**  
`HTTP/1.1 404 Not Found`

```json
{"message": "ID not found."}
```

# 詳細情報の更新

すでに登録された会議室の詳細情報について、内容の更新を行うことができます。 　

## Request

`PUT /api/rooms/{id}`

### Request Body

| フィールド名 | 形式     | 内容                         | 必須 | 備考 |
| ------------ | -------- | ---------------------------- | ---- | ---- |
| `name`       | `string` | 会議室の名前                 | ○    |      |
| `detail`     | `string` | 会議室の詳細。利用可能人数等 | ○    |      |

## Response

更新が成功した場合は下記のようなレスポンスが返ってきます。  
`HTTP/1.1 200 OK`

```json
{
    "id":1,
    "name":"会議室1",
    "detail":"受入人数: 10人",
    "created_at":"2022-01-28T02:37:45.000000Z",
    "updated_at":"2022-01-29T02:37:45.000000Z"
}
```

更新のさい、形式が間違っている場合に下記のようなレスポンスが返ってきます。  
`HTTP/1.1 422 Unprocessable Content`

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required."
        ]
    }
}
```

# 会議室削除

指定された会議室を削除することができます。  
今後、削除可能なユーザ等の制御を行いますが、現状は行われていません。

## Request

`DELETE /api/rooms/{id}`

### Request Body

なし

## Response

削除に成功した場合は下記のようなステータスが返ってきます。  
`HTTP/1.1 204 No Content`

削除に失敗した場合は下記のようなステータスとJSONが返ってきます。  
**JSONが返ってくるようにする予定ですが、現状はhtmlが飛んできます。**  
`HTTP/1.1 404 Not Found`

```json
{"message": "削除可能な会議室はありません。"}
```
