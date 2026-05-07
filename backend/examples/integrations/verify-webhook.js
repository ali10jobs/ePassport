// Express receiver verifying an ePassport webhook delivery.
// npm i express body-parser

const express = require('express');
const bodyParser = require('body-parser');
const crypto = require('crypto');

const app = express();
const SECRET = process.env.EPASSPORT_WEBHOOK_SECRET; // 64-hex from /api/v1/webhooks creation

// Use the *raw* body for signature verification — JSON.stringify will not match.
app.post(
  '/webhooks/epassport',
  bodyParser.raw({ type: 'application/json' }),
  (req, res) => {
    const signature = req.headers['x-epassport-signature']; // 'sha256=<hex>'
    const event = req.headers['x-epassport-event'];        // e.g. 'scan.red'
    const eventId = req.headers['x-epassport-event-id'];    // UUID

    if (!signature || !signature.startsWith('sha256=')) {
      return res.status(400).send('missing signature');
    }

    const expected = 'sha256=' + crypto
      .createHmac('sha256', SECRET)
      .update(req.body) // raw bytes
      .digest('hex');

    // Constant-time compare
    const a = Buffer.from(signature);
    const b = Buffer.from(expected);
    if (a.length !== b.length || !crypto.timingSafeEqual(a, b)) {
      return res.status(401).send('bad signature');
    }

    // De-dupe by event_id (consumer-side responsibility)
    if (alreadyProcessed(eventId)) return res.status(200).send('ok');
    markProcessed(eventId);

    const payload = JSON.parse(req.body.toString('utf8'));
    handle(event, payload.data);

    return res.status(200).send('ok');
  }
);

function alreadyProcessed(_) { return false; }
function markProcessed(_) {}
function handle(event, data) {
  console.log('Received', event, data);
}

app.listen(3000);
