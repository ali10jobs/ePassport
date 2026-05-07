"""
FastAPI receiver verifying an ePassport webhook delivery.

  pip install fastapi uvicorn
  EPASSPORT_WEBHOOK_SECRET=<hex secret> uvicorn verify_webhook:app --port 3000
"""

import hashlib
import hmac
import json
import os

from fastapi import FastAPI, Header, HTTPException, Request

app = FastAPI()
SECRET = os.environ["EPASSPORT_WEBHOOK_SECRET"]  # 64-hex from POST /api/v1/webhooks


@app.post("/webhooks/epassport")
async def receive(
    request: Request,
    x_epassport_event: str = Header(...),
    x_epassport_event_id: str = Header(...),
    x_epassport_signature: str = Header(...),
):
    raw = await request.body()  # raw bytes — required for signature verification

    if not x_epassport_signature.startswith("sha256="):
        raise HTTPException(400, "missing signature")

    expected = "sha256=" + hmac.new(SECRET.encode(), raw, hashlib.sha256).hexdigest()
    if not hmac.compare_digest(expected, x_epassport_signature):
        raise HTTPException(401, "bad signature")

    if already_processed(x_epassport_event_id):
        return {"ok": True}
    mark_processed(x_epassport_event_id)

    payload = json.loads(raw)
    handle(x_epassport_event, payload["data"])
    return {"ok": True}


def already_processed(_):
    return False


def mark_processed(_):
    pass


def handle(event, data):
    print(f"Received {event}", data)
