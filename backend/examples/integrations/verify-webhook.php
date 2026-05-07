<?php

/**
 * Plain-PHP receiver verifying an ePassport webhook delivery.
 *
 * Drop on a public PHP webserver at /webhooks/epassport. Set:
 *   putenv('EPASSPORT_WEBHOOK_SECRET=<64-hex secret>');
 */

declare(strict_types=1);

$secret = getenv('EPASSPORT_WEBHOOK_SECRET') ?: throw new RuntimeException('secret not set');

// Raw body — REQUIRED for signature verification.
$raw = file_get_contents('php://input');

$signature = $_SERVER['HTTP_X_EPASSPORT_SIGNATURE'] ?? '';
$event = $_SERVER['HTTP_X_EPASSPORT_EVENT'] ?? '';
$eventId = $_SERVER['HTTP_X_EPASSPORT_EVENT_ID'] ?? '';

if (! str_starts_with($signature, 'sha256=')) {
    http_response_code(400);
    exit('missing signature');
}

$expected = 'sha256='.hash_hmac('sha256', $raw, $secret);
if (! hash_equals($expected, $signature)) {
    http_response_code(401);
    exit('bad signature');
}

// Idempotency: dedupe by $eventId on your side.
// if (alreadyProcessed($eventId)) { http_response_code(200); exit('ok'); }
// markProcessed($eventId);

$payload = json_decode($raw, true);

// Handle the event
switch ($event) {
    case 'scan.red':
    case 'scan.impersonation_flag':
        // alert oncall
        break;
    case 'permit.approved':
        // sync to ERP
        break;
    case 'hazard_report.submitted':
        // notify HSE team
        break;
}

http_response_code(200);
echo 'ok';
