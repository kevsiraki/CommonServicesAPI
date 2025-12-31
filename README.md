# Common Services API Documentation

**Base URL:** `https://demo.kevinsiraki.com/CommonServices/`

All protected routes require a valid `key` field in the request body.

---

## `/health` or `/` (root)

* **Method:** `GET` or `POST`
* **Body:** None
* **Response:**

```json
{
  "status": "ok",
  "node_ip": "192.168.1.86:80",
  "node_hostname": "raspberrypi",
  "timestamp": 1751752926
}
```

---

## `POST /email`

Sends an email with optional CC and attachments.

* **Content-Type:** `application/json`
* **Body Example:**

```json
{
  "key": "<your_secret_key>",
  "to": "John Doe <john.doe@example.com>",
  "cc": "Jane Smith <jane.smith@example.com>",
  "subject": "Monthly Report",
  "body": "<p>Hello Team,</p><p>Please find the attached monthly report.</p>",
  "attachments": [
    "https://example.com/images/photo.jpg",
    {
      "url": "https://example.com/files/report.pdf",
      "filename": "report.pdf"
    },
    "data:application/pdf;base64,JVBERi0xLjQKJ...",
    {
      "filename": "summary.pdf",
      "base64": "JVBERi0xLjQKJ..."
    }
  ]
}
```

* **Response:**

```json
{
  "success": true
}
```

---

## `POST /discord`

Sends a message to a Discord webhook.

* **Body Example:**

```json
{
  "url": "https://discord.com/api/webhooks/your_webhook_id",
  "key": "<your_secret_key>",
  "content": "Project Alpha update deployed successfully.",
  "username": "NotificationBot",
  "avatar_url": "https://example.com/avatar.png",
  "tts": false,
  "allowed_mentions": {
    "parse": ["users"]
  },
  "embeds": [
    {
      "title": "Deployment Complete",
      "description": "The latest changes to Project Alpha are now live.",
      "footer": {
        "text": "Deployment Bot",
        "icon_url": "https://example.com/logo.png"
      }
    }
  ]
}
```

* **Response:**

```json
{
  "success": true,
  "status": 204,
  "response": "",
  "payload": {}
}
```

---

## `POST /tweet`

Posts a tweet using the configured Twitter account.

* **Body:**

```json
{
  "key": "<your_secret_key>",
  "message": "New product feature has been launched. Visit our website for more details."
}
```

* **Response:**

```json
{
  "success": true,
  "tweet_id": "1941613922743939236"
}
```

---

## `/easter`

Returns Easter Sunday for the current year and offset from March 21.

* **Method:** `GET` or `POST`
* **Body:** None
* **Response:**

```json
{
  "easter": "Apr-20-2025",
  "daysAfterMarch21stForEasterThisYear": 30
}
```

---

## `POST /nc/nest`

Creates a nested folder structure in Nextcloud.

* **Body:**

```json
{
  "key": "<your_secret_key>",
  "path": "/documents/clients/reports"
}
```

* **Response:**

```json
[
  {
    "folder": "documents/",
    "status": 201,
    "response": ""
  },
  {
    "folder": "documents/clients/",
    "status": 201,
    "response": ""
  },
  {
    "folder": "documents/clients/reports/",
    "status": 201,
    "response": ""
  }
]
```

---

## `POST /eflorm`

Generates a PDF document from submitted data.

* **Body Example:**

```json
{
  "key": "<your_secret_key>",
  "by": "Jane Doe",
  "date": "07/2025",
  "title": "System Performance Analysis",
  "description": "This report outlines system metrics and performance trends for Q2.",
  "associates": "Jane Doe, John Smith",
  "id": 42
}
```

* **Response:**
  PDF file download.

---

## Error Handling

### Unknown Endpoint

```json
{
  "error": "Unknown endpoint"
}
```

* **TODO: Add Docs on osTicket Gateway Endpoints :)**

### Common Status Codes

| Status | Meaning                       |
| ------ | ----------------------------- |
| 200    | OK                            |
| 201    | Created (folder/files)        |
| 204    | No Content (success, no body) |
| 400    | Bad Request                   |
| 403    | Unauthorized (invalid `key`)  |
| 404    | Not Found                     |
| 405    | Method Not Allowed            |

