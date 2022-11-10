<details>
<summary> 目次 </summary>

1. [概要](#概要)
1. [ステータスコード](#ステータスコード)
1. [新規予約登録](#新規予約登録)
   1. [Request](#request)
      1. [Request Body](#request-body)
   1. [Response](#response)
2. [予約詳細取得](#予約詳細取得)
   1. [Request](#request-1)
      1. [Request Body](#request-body-1)
   1. [Response](#response-1)
3. 予約変更
4. [予約削除](#予約削除)
   1. [Request](#request-2)
      1. [Request Body](#request-body-2)
   2. [Response](#response-2)
5. [予約抽出](#予約抽出)
   1. [Request](#request-3)
      1. [Request Body](#request-body-3)
   2. [Response](#response-3)
      1. [room_idを指定した場合](#room_idを指定した場合)
      2. [room_idを指定しなかった場合](#room_idを指定しなかった場合)
6. [繰り返し予約の取得](#繰り返し予約の取得)
   1. [Request](#request-4)
      1. [Request Body](#request-body-4)
   2. [Response](#response-4)

</details>

# 概要

会議室の予約APIについて仕様を説明します。  
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
| 400              | 不正なリクエストパラメータを指定している  |
| 401              | APIアクセストークンが不正、または権限不正 |
| 404              | 存在しないURLにアクセス                   |
| 422              | 処理できないコンテンツ                    |
| 500              | 不明なエラー                              |

# 新規予約登録

新しく、部屋の予約を行うことができます。  
すでにある予約を押しのけて登録することはできません。  

## Request

`POST /api/reserve/`

### Request Body

| フィールド名           | 形式                 | 内容                   | 必須 | 最小値 | 最大値 | 備考                                                                                                         |
| ---------------------- | -------------------- | ---------------------- | ---- | ------ | ------ | ------------------------------------------------------------------------------------------------------------ |
| `reserver_name`        | `string`             | 登録者の名前           |      |        |        | プレースホルダであり、現状なにかしらをしているわけではない                                                   |
| `guest_name`           | `string`             | 利用者・利用団体の名前 | ○    |        |        |                                                                                                              |
| `start_date_time`      | `string(ISO8601)`    | 予約の開始時間         | ○    |        |        | `end_date_time`よりも、日時が前である必要がある。                                                            |
| `end_date_time`        | `string(ISO8601)`    | 予約の終了時間         | ○    |        |        | `start_date_time`よりも、日時が後である必要がある。                                                          |
| `purpose`              | `string`             | 会議室の利用目的       | ○    |        |        |                                                                                                              |
| `guest_detail`         | `string`             | 利用者の詳細。連絡先等 |      |        |        |                                                                                                              |  |
| `room_id`              | `integer`            | 会議室ID               | ○    | 1      | 6      |                                                                                                              |
| `repitation.type`      | `integer`            | 繰り返し予約のタイプ   | ○    | 0      | 2      | 0: 繰り返しなし、1: 毎日、2: 毎週                                                                            |
| `repitation.num`       | `integer`            | 繰り返し予約の回数     | △    | 1      |        | `repitation.type`が0以外かつ、`repitation.finish_at`が設定されていないときに必要。予約開始当日も計数に入れる |
| `repitation.finish_at` | `string(YYYY-MM-DD)` | 繰り返し予約の終了日   | △    |        |        | `repitation.type`が0以外かつ、`repitation.num`が設定されていないときに必要。                                 |

<details>
<summary> Bodyの例 </summary>

```json
{
    "guest_name":"TEST USER",
    "start_date_time":"2022-02-02T08:00:00.000Z",
    "end_date_time":  "2022-02-02T09:00:00.000Z",
    "purpose":"purpose",
    "guest_detail":"User",
    "room": {
        "name":"会議室1",
        "detail":"受入人数: 10人",
        "updated_at":"2022-01-28T02:37:45.000000Z",
        "created_at":"2022-01-28T02:37:45.000000Z",
        "id":1
    },
    "repitation": {
        "type": 1,
        "num": 3
    }
}
```

</details>

## Response

繰り返しなしの予約の登録が成功した場合は下記のようなレスポンスが返ってきます。  
`HTTP/1.1 201 Created`

```json
{
    "guest_name":"TEST NAME",
    "start_date_time":"2022-01-27T8:50:00.000000Z",
    "end_date_time":"2022-01-27T10:20:00.000000Z",
    "purpose":"purpose",
    "guest_detail":"User",
    "room": {
        "name":"会議室1",
        "detail":"受入人数: 10人",
        "updated_at":"2022-01-28T02:37:45.000000Z",
        "created_at":"2022-01-28T02:37:45.000000Z",
        "id":1
    },
    "updated_at":"2022-01-28T02:37:45.000000Z",
    "created_at":"2022-01-28T02:37:45.000000Z",
    "id":1,
    "repitation_id": null
}
```

繰り返しありの予約の登録が成功した場合は下記のようなレスポンスが返ってきます。  
`HTTP/1.1 201 Created`
<details>
<summary> 長いので折りたたみます。 </summary>

```json
[
    {
        "id": 17,
        "guest_name": "TEST USER",
        "start_date_time": "2022-02-02T08:00:00.000000Z",
        "end_date_time": "2022-02-02T09:00:00.000000Z",
        "purpose": "purpose",
        "guest_detail": "User",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 10,
        "created_at": "2022-02-10T07:43:28.000000Z",
        "updated_at": "2022-02-10T07:43:28.000000Z"
    },
    {
        "id": 18,
        "guest_name": "TEST USER",
        "start_date_time": "2022-02-03T08:00:00.000000Z",
        "end_date_time": "2022-02-03T09:00:00.000000Z",
        "purpose": "purpose",
        "guest_detail": "User",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 10,
        "created_at": "2022-02-10T07:43:28.000000Z",
        "updated_at": "2022-02-10T07:43:28.000000Z"
    },
    {
        "id": 19,
        "guest_name": "TEST USER",
        "start_date_time": "2022-02-04T08:00:00.000000Z",
        "end_date_time": "2022-02-04T09:00:00.000000Z",
        "purpose": "purpose",
        "guest_detail": "User",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 10,
        "created_at": "2022-02-10T07:43:28.000000Z",
        "updated_at": "2022-02-10T07:43:28.000000Z"
    }
]
```

</details>

予約登録のさい、形式が間違っている場合に下記のようなレスポンスが返ってきます。  
`HTTP/1.1 422 Unprocessable Content`

```json
{
  "message": "The given data was invalid.",
  "errors":
  {
    "end_date_time":
    [
      "The end date time is not a valid date.",
      "The end date time format is invalid."
    ]
  }
}
```

すでに予定が登録されている場合、下記のようなレスポンスが返ってきます。  
`409 Conflict`
<details>
<summary> 長いので折りたたみます。 </summary>

```json
{
    "message": "Reservation is conflicting",
    "conflictings": [
        {
            "id": 17,
            "guest_name": "TEST USER",
            "start_date_time": "2022-02-02T08:00:00.000000Z",
            "end_date_time": "2022-02-02T09:00:00.000000Z",
            "purpose": "purpose",
            "guest_detail": "User",
            "room": {
                "name":"会議室1",
                "detail":"受入人数: 10人",
                "updated_at":"2022-01-28T02:37:45.000000Z",
                "created_at":"2022-01-28T02:37:45.000000Z",
                "id":1
            },
            "repitation_id": 10,
            "created_at": "2022-02-10T07:43:28.000000Z",
            "updated_at": "2022-02-10T07:43:28.000000Z"
        },
        {
            "id": 18,
            "guest_name": "TEST USER",
            "start_date_time": "2022-02-03T08:00:00.000000Z",
            "end_date_time": "2022-02-03T09:00:00.000000Z",
            "purpose": "purpose",
            "guest_detail": "User",
            "room": {
                "name":"会議室1",
                "detail":"受入人数: 10人",
                "updated_at":"2022-01-28T02:37:45.000000Z",
                "created_at":"2022-01-28T02:37:45.000000Z",
                "id":1
            },
            "repitation_id": 10,
            "created_at": "2022-02-10T07:43:28.000000Z",
            "updated_at": "2022-02-10T07:43:28.000000Z"
        },
        {
            "id": 19,
            "guest_name": "TEST USER",
            "start_date_time": "2022-02-04T08:00:00.000000Z",
            "end_date_time": "2022-02-04T09:00:00.000000Z",
            "purpose": "purpose",
            "guest_detail": "User",
            "room": {
                "name":"会議室1",
                "detail":"受入人数: 10人",
                "updated_at":"2022-01-28T02:37:45.000000Z",
                "created_at":"2022-01-28T02:37:45.000000Z",
                "id":1
            },
            "repitation_id": 10,
            "created_at": "2022-02-10T07:43:28.000000Z",
            "updated_at": "2022-02-10T07:43:28.000000Z"
        }
    ]
}
```

</details>

データベースのトランザクションで失敗した場合に下記のようなレスポンスが返ってきます。  
**すでに登録されていると出てくるはずです。**

# 予約詳細取得

指定された予約について、詳細を取得することができます。  

## Request

`GET /api/reserve/{id}`

### Request Body

なし

## Response

取得に成功した場合は下記のようなステータスとjsonが返ってきます。  
`HTTP/1.1 200 OK`

```json
{
    "guest_name":"TEST NAME",
    "start_date_time":"2022-01-27T8:50:00",
    "end_date_time":"2022-01-27T10:20:00",
    "purpose":"purpose",
    "guest_detail":"User",
    "room": {
        "name":"会議室1",
        "detail":"受入人数: 10人",
        "updated_at":"2022-01-28T02:37:45.000000Z",
        "created_at":"2022-01-28T02:37:45.000000Z",
        "id":1
    },
    "updated_at":"2022-01-28T02:37:45.000000Z",
    "created_at":"2022-01-28T02:37:45.000000Z",
    "id":1,
    "repitation_id": null
}
```

取得に失敗した場合は下記のようなステータスとjsonが返ってきます。  
`HTTP/1.1 404 Not Found`

```json
{"message": "ID not found."}
```

# 予約変更 本アプリケーションでは実装していません

すでに登録された予約について、内容の変更を行うことができます。  

# 予約削除

指定された予約を削除することができます。  
今後、削除可能なユーザ等の制御を行いますが、現状は行われていません。

## Request

`DELETE /api/reserve/{id}`

### Request Body

| フィールド名 | 形式      | 内容                       | 必須 | 最小値 | 最大値 | 備考 |
| ------------ | --------- | -------------------------- | ---- | ------ | ------ | ---- |
| `is_all`     | `boolean` | 関連した予約をすべて消すか |      |        |        |      |

## Response

予約の削除に成功した場合は下記のようなステータスが返ってきます。  
`HTTP/1.1 204 No Content`

予約の削除に失敗した場合は下記のようなステータスとJSONが返ってきます。  
`HTTP/1.1 404 Not Found`

```json
{"message": "削除可能な予約はありません。"}
```

# 予約抽出

指定された期間内における予約の状況を知ることができます。  
`room_id`を指定することで、特定の部屋についての状況に絞り込むこともできます。

## Request

`GET /api/reserve/?start_date_time={YYYY-MM-DDThh:mm:ss.000Z}&end_date_time={YYYY-MM-DDThh:mm:ss.000Z}&room_id={room_id}`

### Request Body

クエリパラメータとして記述します。  
| パラメータ        | 形式              | 内容           | 必須 | 最小値 | 最大値 | 備考                                         |
| ----------------- | ----------------- | -------------- | ---- | ------ | ------ | -------------------------------------------- |
| `start_date_time` | `string(ISO8601)` | 抽出の開始日時 | ○    |        |        |                                              |
| `end_date_time`   | `string(ISO8601)` | 抽出の終了日時 | ○    |        |        |                                              |
| `room_id`         | `integer`         | 会議室ID       |      | 1      | 6      | 省略した場合、全ての部屋の予約が取得できる。 |

## Response

予約の抽出に成功した場合は下記のようなステータスとjsonが返ってきます。  

### room_idを指定した場合

指定された部屋番号の結果が返ってきます。  
`HTTP/1.1  200 OK`

```json
[
    {
        "id": 1,
        "reserver_name": "reserver name",
        "guest_name": "guest name",
        "start_date_time": "2022-01-27T09:50:00",
        "end_date_time": "2022-01-27T11:20:00",
        "purpose": "purpose",
        "guest_detail": "guest detail",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": null
    },
    {
        "id": 2,
        "reserver_name": "reserver name",
        "guest_name": "guest name",
        "start_date_time": "2022-01-28T09:50:00",
        "end_date_time": "2022-01-28T11:20:00",
        "purpose": "purpose",
        "guest_detail": "guest detail",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 1
    }
]
```

### room_idを指定しなかった場合

全ての部屋番号の結果が返ってきます  
`HTTP/1.1  200 OK`

```json
[
    {
        "id": 1,
        "reserver_name": "reserver name",
        "guest_name": "guest name",
        "start_date_time": "2022-01-27T09:50:00",
        "end_date_time": "2022-01-27T11:20:00",
        "purpose": "purpose",
        "guest_detail": "guest detail",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": null
    },
    {
        "id": 2,
        "reserver_name": "reserver name",
        "guest_name": "guest name",
        "start_date_time": "2022-01-28T09:50:00",
        "end_date_time": "2022-01-28T11:20:00",
        "purpose": "purpose",
        "guest_detail": "guest detail",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 2
    }
]
```

予約抽出の際、開始時間、終了時間の入力がなかったり、形式が間違っている場合に下記のようなレスポンスが返ってきます。  
`HTTP/1.1 422 Unprocessable Content`

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "start_date_time": [
            "The end date time does not match the format Y-m-d\\TH:i:s.ve.",
            "The end date time field is required."
        ],
        "end_date_time": [
            "The end date time does not match the format Y-m-d\\TH:i:s.ve.",
            "The end date time field is required."
        ]
    }
}
```

# 繰り返し予約の取得

繰り返し予約を行った場合、`repitation_id`を指定することで、同時に登録した予約を取得することができます。  

## Request

`GEt /api/repitations/{repitation_id}`

### Request Body

なし

## Response

取得に成功した場合は下記のようなステータスとJSONが返ってきます。  
`HTTP/1.1 200 OK`
<details>
<summary> 長いので折りたたみます。 </summary>

```json
[
    {
        "id": 1,
        "guest_name": "TEST USER",
        "start_date_time": "2022-02-02T08:00:00.000000Z",
        "end_date_time": "2022-02-02T09:00:00.000000Z",
        "purpose": "purpose",
        "guest_detail": "User",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 1,
        "created_at": "2022-02-10T07:17:06.000000Z",
        "updated_at": "2022-02-10T07:17:06.000000Z"
    },
    {
        "id": 2,
        "guest_name": "TEST USER",
        "start_date_time": "2022-02-03T08:00:00.000000Z",
        "end_date_time": "2022-02-03T09:00:00.000000Z",
        "purpose": "purpose",
        "guest_detail": "User",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 1,
        "created_at": "2022-02-10T07:17:06.000000Z",
        "updated_at": "2022-02-10T07:17:06.000000Z"
    },
    {
        "id": 3,
        "guest_name": "TEST USER",
        "start_date_time": "2022-02-04T08:00:00.000000Z",
        "end_date_time": "2022-02-04T09:00:00.000000Z",
        "purpose": "purpose",
        "guest_detail": "User",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 1,
        "created_at": "2022-02-10T07:17:06.000000Z",
        "updated_at": "2022-02-10T07:17:06.000000Z"
    },
    {
        "id": 4,
        "guest_name": "TEST USER",
        "start_date_time": "2022-02-05T08:00:00.000000Z",
        "end_date_time": "2022-02-05T09:00:00.000000Z",
        "purpose": "purpose",
        "guest_detail": "User",
        "room": {
            "name":"会議室1",
            "detail":"受入人数: 10人",
            "updated_at":"2022-01-28T02:37:45.000000Z",
            "created_at":"2022-01-28T02:37:45.000000Z",
            "id":1
        },
        "repitation_id": 1,
        "created_at": "2022-02-10T07:17:06.000000Z",
        "updated_at": "2022-02-10T07:17:06.000000Z"
    }
]
```

</details>

取得に失敗した場合は下記のようなステータスとJSONが返ってきます。  
`HTTP/1.1 404 Not Found`

```json
{"message": "ID not found."}
```
