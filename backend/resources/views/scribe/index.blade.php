<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>ePassport API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.9.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.9.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-api-keys" class="tocify-header">
                <li class="tocify-item level-1" data-unique="api-keys">
                    <a href="#api-keys">API Keys</a>
                </li>
                                    <ul id="tocify-subheader-api-keys" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="api-keys-GETapi-v1-auth-api-keys">
                                <a href="#api-keys-GETapi-v1-auth-api-keys">GET api/v1/auth/api-keys</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="api-keys-GETapi-v1-auth-api-keys-abilities">
                                <a href="#api-keys-GETapi-v1-auth-api-keys-abilities">Available abilities catalog. UI for issuing keys uses this to render
the ability checklist.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="api-keys-POSTapi-v1-auth-api-keys">
                                <a href="#api-keys-POSTapi-v1-auth-api-keys">Create a new API key. The plaintext token is returned ONCE; store it now.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="api-keys-DELETEapi-v1-auth-api-keys--tokenId-">
                                <a href="#api-keys-DELETEapi-v1-auth-api-keys--tokenId-">Revoke an API key.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-authentication" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authentication">
                    <a href="#authentication">Authentication</a>
                </li>
                                    <ul id="tocify-subheader-authentication" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="authentication-POSTapi-v1-auth-login">
                                <a href="#authentication-POSTapi-v1-auth-login">Log in.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-v1-auth-logout">
                                <a href="#authentication-POSTapi-v1-auth-logout">Log out the current user.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-dashboards" class="tocify-header">
                <li class="tocify-item level-1" data-unique="dashboards">
                    <a href="#dashboards">Dashboards</a>
                </li>
                                    <ul id="tocify-subheader-dashboards" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="dashboards-GETapi-v1-dashboards-client-summary">
                                <a href="#dashboards-GETapi-v1-dashboards-client-summary">Client dashboard: cross-project metrics across all owned projects.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="dashboards-GETapi-v1-dashboards-main-contractor-summary">
                                <a href="#dashboards-GETapi-v1-dashboards-main-contractor-summary">Main contractor dashboard.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="dashboards-GETapi-v1-dashboards-consultant-summary">
                                <a href="#dashboards-GETapi-v1-dashboards-consultant-summary">Consultant dashboard.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="dashboards-GETapi-v1-dashboards-subcontractor-summary">
                                <a href="#dashboards-GETapi-v1-dashboards-subcontractor-summary">Subcontractor dashboard.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-equipment" class="tocify-header">
                <li class="tocify-item level-1" data-unique="equipment">
                    <a href="#equipment">Equipment</a>
                </li>
                                    <ul id="tocify-subheader-equipment" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="equipment-POSTapi-v1-equipment-bulk">
                                <a href="#equipment-POSTapi-v1-equipment-bulk">Bulk-import equipment. Same per-record-success/failure semantics as
the worker bulk endpoint.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="equipment-GETapi-v1-equipment">
                                <a href="#equipment-GETapi-v1-equipment">List equipment.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="equipment-POSTapi-v1-equipment">
                                <a href="#equipment-POSTapi-v1-equipment">POST api/v1/equipment</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="equipment-GETapi-v1-equipment--id-">
                                <a href="#equipment-GETapi-v1-equipment--id-">GET api/v1/equipment/{id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="equipment-PATCHapi-v1-equipment--id-">
                                <a href="#equipment-PATCHapi-v1-equipment--id-">PATCH api/v1/equipment/{id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="equipment-DELETEapi-v1-equipment--id-">
                                <a href="#equipment-DELETEapi-v1-equipment--id-">DELETE api/v1/equipment/{id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="equipment-GETapi-v1-equipment--equipment_id--qr">
                                <a href="#equipment-GETapi-v1-equipment--equipment_id--qr">Equipment QR PNG (image/png). Used at print time for adhesive labels.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="equipment-POSTapi-v1-equipment--equipment_id--certifications">
                                <a href="#equipment-POSTapi-v1-equipment--equipment_id--certifications">Attach a TPI inspection certificate.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="equipment-POSTapi-v1-equipment--equipment_id--operators">
                                <a href="#equipment-POSTapi-v1-equipment--equipment_id--operators">Pair an authorized operator with this equipment. Validates that the
worker holds at least one operator-relevant certification before
allowing the pairing. (For week 1 we accept any cert; per-equipment-type
required certs come later when we wire the equipment-type catalog.)</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-hazard-reports-anonymous-public" class="tocify-header">
                <li class="tocify-item level-1" data-unique="hazard-reports-anonymous-public">
                    <a href="#hazard-reports-anonymous-public">Hazard Reports — Anonymous (public)</a>
                </li>
                                    <ul id="tocify-subheader-hazard-reports-anonymous-public" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="hazard-reports-anonymous-public-POSTapi-v1-hazard-reports-anonymous">
                                <a href="#hazard-reports-anonymous-public-POSTapi-v1-hazard-reports-anonymous">Submit an anonymous hazard report. Photo EXIF stripped server-side
before storage.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="hazard-reports-anonymous-public-GETapi-v1-hazard-reports-anonymous--anonymousReportId-">
                                <a href="#hazard-reports-anonymous-public-GETapi-v1-hazard-reports-anonymous--anonymousReportId-">Public status check. Returns ONLY non-PII fields and PUBLIC notes.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-hazard-reports-authenticated" class="tocify-header">
                <li class="tocify-item level-1" data-unique="hazard-reports-authenticated">
                    <a href="#hazard-reports-authenticated">Hazard Reports — Authenticated</a>
                </li>
                                    <ul id="tocify-subheader-hazard-reports-authenticated" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="hazard-reports-authenticated-GETapi-v1-hazard-reports">
                                <a href="#hazard-reports-authenticated-GETapi-v1-hazard-reports">GET api/v1/hazard-reports</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="hazard-reports-authenticated-GETapi-v1-hazard-reports--hazardReport_id-">
                                <a href="#hazard-reports-authenticated-GETapi-v1-hazard-reports--hazardReport_id-">GET api/v1/hazard-reports/{hazardReport_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="hazard-reports-authenticated-PATCHapi-v1-hazard-reports--hazardReport_id-">
                                <a href="#hazard-reports-authenticated-PATCHapi-v1-hazard-reports--hazardReport_id-">Update status / assignment / resolution of a hazard report.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="hazard-reports-authenticated-POSTapi-v1-hazard-reports--hazardReport_id--notes">
                                <a href="#hazard-reports-authenticated-POSTapi-v1-hazard-reports--hazardReport_id--notes">Add an internal or public note. Internal notes are visible only via
authenticated GET; public notes appear on the anonymous status check.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-health" class="tocify-header">
                <li class="tocify-item level-1" data-unique="health">
                    <a href="#health">Health</a>
                </li>
                                    <ul id="tocify-subheader-health" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="health-GETapi-v1-health">
                                <a href="#health-GETapi-v1-health">Health check.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="health-GETapi-v1-me">
                                <a href="#health-GETapi-v1-me">Authenticated identity.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-permits" class="tocify-header">
                <li class="tocify-item level-1" data-unique="permits">
                    <a href="#permits">Permits</a>
                </li>
                                    <ul id="tocify-subheader-permits" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="permits-GETapi-v1-permits">
                                <a href="#permits-GETapi-v1-permits">GET api/v1/permits</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="permits-POSTapi-v1-permits">
                                <a href="#permits-POSTapi-v1-permits">Create a draft permit.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="permits-GETapi-v1-permits--id-">
                                <a href="#permits-GETapi-v1-permits--id-">GET api/v1/permits/{id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="permits-POSTapi-v1-permits--permit_id--workers">
                                <a href="#permits-POSTapi-v1-permits--permit_id--workers">Attach workers to a draft permit. Either by ID list or by helmet/coverall
QR tokens (or both). Workers can be attached only while the permit is
in draft state.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="permits-POSTapi-v1-permits--permit_id--equipment">
                                <a href="#permits-POSTapi-v1-permits--permit_id--equipment">Attach equipment to a draft permit. Either by ID list or QR tokens.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-permits-lifecycle" class="tocify-header">
                <li class="tocify-item level-1" data-unique="permits-lifecycle">
                    <a href="#permits-lifecycle">Permits — Lifecycle</a>
                </li>
                                    <ul id="tocify-subheader-permits-lifecycle" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="permits-lifecycle-POSTapi-v1-permits--permit_id--submit">
                                <a href="#permits-lifecycle-POSTapi-v1-permits--permit_id--submit">Submit a draft permit. Re-runs all validation against named workers and
equipment. On failure returns 422 PERMIT_VALIDATION_FAILED with a full
per-worker / per-equipment breakdown so the UI can surface exactly which
worker has which expired cert.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="permits-lifecycle-POSTapi-v1-permits--permit_id--approve">
                                <a href="#permits-lifecycle-POSTapi-v1-permits--permit_id--approve">Approve a submitted permit (consultant role).</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="permits-lifecycle-POSTapi-v1-permits--permit_id--reject">
                                <a href="#permits-lifecycle-POSTapi-v1-permits--permit_id--reject">Reject a submitted permit with mandatory reason.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="permits-lifecycle-POSTapi-v1-permits--permit_id--close">
                                <a href="#permits-lifecycle-POSTapi-v1-permits--permit_id--close">Close an approved permit once work is complete.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="permits-lifecycle-GETapi-v1-permits--permit_id--events">
                                <a href="#permits-lifecycle-GETapi-v1-permits--permit_id--events">Lifecycle history.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-scan-verification" class="tocify-header">
                <li class="tocify-item level-1" data-unique="scan-verification">
                    <a href="#scan-verification">Scan Verification</a>
                </li>
                                    <ul id="tocify-subheader-scan-verification" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="scan-verification-GETapi-v1-scans">
                                <a href="#scan-verification-GETapi-v1-scans">List scan events.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="scan-verification-POSTapi-v1-scans-verify">
                                <a href="#scan-verification-POSTapi-v1-scans-verify">Verify a single scan (helmet, coverall, equipment, or manual employee_id).</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="scan-verification-POSTapi-v1-scans-verify-pair">
                                <a href="#scan-verification-POSTapi-v1-scans-verify-pair">Helmet + coverall cross-check. Two QRs in sequence; if they don't
resolve to the same worker the result is IMPERSONATION_FLAG.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="scan-verification-POSTapi-v1-scans-verify-equipment-operator">
                                <a href="#scan-verification-POSTapi-v1-scans-verify-equipment-operator">Equipment + paired operator. Verifies equipment TPI AND that the worker
is on the equipment's authorized operators list.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-webhooks" class="tocify-header">
                <li class="tocify-item level-1" data-unique="webhooks">
                    <a href="#webhooks">Webhooks</a>
                </li>
                                    <ul id="tocify-subheader-webhooks" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="webhooks-GETapi-v1-webhooks">
                                <a href="#webhooks-GETapi-v1-webhooks">GET api/v1/webhooks</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="webhooks-POSTapi-v1-webhooks">
                                <a href="#webhooks-POSTapi-v1-webhooks">Create a webhook subscription. Response includes the secret ONCE; store
it client-side immediately, the secret is never returned again.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="webhooks-GETapi-v1-webhooks--webhookSubscription_id-">
                                <a href="#webhooks-GETapi-v1-webhooks--webhookSubscription_id-">GET api/v1/webhooks/{webhookSubscription_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="webhooks-PATCHapi-v1-webhooks--webhookSubscription_id-">
                                <a href="#webhooks-PATCHapi-v1-webhooks--webhookSubscription_id-">PATCH api/v1/webhooks/{webhookSubscription_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="webhooks-DELETEapi-v1-webhooks--webhookSubscription_id-">
                                <a href="#webhooks-DELETEapi-v1-webhooks--webhookSubscription_id-">DELETE api/v1/webhooks/{webhookSubscription_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="webhooks-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret">
                                <a href="#webhooks-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret">Rotate the HMAC secret. New secret returned ONCE in this response.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-worker-certifications" class="tocify-header">
                <li class="tocify-item level-1" data-unique="worker-certifications">
                    <a href="#worker-certifications">Worker Certifications</a>
                </li>
                                    <ul id="tocify-subheader-worker-certifications" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="worker-certifications-GETapi-v1-workers--worker_id--certifications">
                                <a href="#worker-certifications-GETapi-v1-workers--worker_id--certifications">GET api/v1/workers/{worker_id}/certifications</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="worker-certifications-POSTapi-v1-workers--worker_id--certifications">
                                <a href="#worker-certifications-POSTapi-v1-workers--worker_id--certifications">Attach a certification to a worker.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="worker-certifications-DELETEapi-v1-workers--worker_id--certifications--certificationId-">
                                <a href="#worker-certifications-DELETEapi-v1-workers--worker_id--certifications--certificationId-">DELETE api/v1/workers/{worker_id}/certifications/{certificationId}</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-worker-medical-records" class="tocify-header">
                <li class="tocify-item level-1" data-unique="worker-medical-records">
                    <a href="#worker-medical-records">Worker Medical Records</a>
                </li>
                                    <ul id="tocify-subheader-worker-medical-records" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="worker-medical-records-GETapi-v1-workers--worker_id--medical-records">
                                <a href="#worker-medical-records-GETapi-v1-workers--worker_id--medical-records">GET api/v1/workers/{worker_id}/medical-records</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="worker-medical-records-POSTapi-v1-workers--worker_id--medical-records">
                                <a href="#worker-medical-records-POSTapi-v1-workers--worker_id--medical-records">Add a new medical record. Most recent (by exam_date) becomes the active one.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-workers" class="tocify-header">
                <li class="tocify-item level-1" data-unique="workers">
                    <a href="#workers">Workers</a>
                </li>
                                    <ul id="tocify-subheader-workers" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="workers-POSTapi-v1-workers-bulk">
                                <a href="#workers-POSTapi-v1-workers-bulk">Bulk-import workers.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="workers-GETapi-v1-workers">
                                <a href="#workers-GETapi-v1-workers">List workers.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="workers-POSTapi-v1-workers">
                                <a href="#workers-POSTapi-v1-workers">Create a worker.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="workers-GETapi-v1-workers--id-">
                                <a href="#workers-GETapi-v1-workers--id-">Get a single worker.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="workers-PATCHapi-v1-workers--id-">
                                <a href="#workers-PATCHapi-v1-workers--id-">Update a worker.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="workers-DELETEapi-v1-workers--id-">
                                <a href="#workers-DELETEapi-v1-workers--id-">Soft-delete a worker.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="workers-GETapi-v1-workers--worker_id--passport">
                                <a href="#workers-GETapi-v1-workers--worker_id--passport">Get the consolidated e-Passport view: identity, employer, all valid certs,
medical fitness, induction status. Drives the worker detail screen and
is the read model the gate-scan flow projects from.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="workers-GETapi-v1-workers--worker_id--qr-helmet">
                                <a href="#workers-GETapi-v1-workers--worker_id--qr-helmet">Generate the helmet QR PNG for a worker.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="workers-GETapi-v1-workers--worker_id--qr-coverall">
                                <a href="#workers-GETapi-v1-workers--worker_id--qr-coverall">Generate the coverall QR PNG for a worker.</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: May 7, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>REST API for the ePassport construction safety platform. Authentication: Sanctum cookie session (web SPA), Sanctum personal access tokens (mobile, ERPs). All write endpoints accept Idempotency-Key header. Errors follow {error: {code, message, details, request_id}} with stable codes.</p>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include an <strong><code>Authorization</code></strong> header with the value <strong><code>"Bearer {YOUR_AUTH_KEY}"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<p>You can retrieve your token by visiting your dashboard and clicking <b>Generate API token</b>.</p>

        <h1 id="api-keys">API Keys</h1>

    <p>Long-lived API keys for ERP / machine-to-machine integrations. Built on
Sanctum personal access tokens with scoped abilities. The full token is
returned ONCE at creation; subsequent reads only return the prefix and
metadata.</p>

                                <h2 id="api-keys-GETapi-v1-auth-api-keys">GET api/v1/auth/api-keys</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-auth-api-keys">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/auth/api-keys" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/api-keys"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-auth-api-keys">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: b6eacc2e-e580-4687-8659-75d3e03555c7
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;b6eacc2e-e580-4687-8659-75d3e03555c7&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-auth-api-keys" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-auth-api-keys"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-auth-api-keys"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-auth-api-keys" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-auth-api-keys">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-auth-api-keys" data-method="GET"
      data-path="api/v1/auth/api-keys"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-auth-api-keys', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-auth-api-keys"
                    onclick="tryItOut('GETapi-v1-auth-api-keys');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-auth-api-keys"
                    onclick="cancelTryOut('GETapi-v1-auth-api-keys');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-auth-api-keys"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/auth/api-keys</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-auth-api-keys"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-auth-api-keys"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-auth-api-keys"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="api-keys-GETapi-v1-auth-api-keys-abilities">Available abilities catalog. UI for issuing keys uses this to render
the ability checklist.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-auth-api-keys-abilities">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/auth/api-keys/abilities" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/api-keys/abilities"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-auth-api-keys-abilities">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: f0eb85b7-4a90-46a3-aa6e-e25f4c0ca478
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;f0eb85b7-4a90-46a3-aa6e-e25f4c0ca478&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-auth-api-keys-abilities" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-auth-api-keys-abilities"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-auth-api-keys-abilities"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-auth-api-keys-abilities" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-auth-api-keys-abilities">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-auth-api-keys-abilities" data-method="GET"
      data-path="api/v1/auth/api-keys/abilities"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-auth-api-keys-abilities', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-auth-api-keys-abilities"
                    onclick="tryItOut('GETapi-v1-auth-api-keys-abilities');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-auth-api-keys-abilities"
                    onclick="cancelTryOut('GETapi-v1-auth-api-keys-abilities');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-auth-api-keys-abilities"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/auth/api-keys/abilities</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-auth-api-keys-abilities"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-auth-api-keys-abilities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-auth-api-keys-abilities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="api-keys-POSTapi-v1-auth-api-keys">Create a new API key. The plaintext token is returned ONCE; store it now.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-auth-api-keys">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/api-keys" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"b\",
    \"abilities\": [
        \"workers.write\"
    ],
    \"expires_at\": \"2052-05-30\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/api-keys"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "b",
    "abilities": [
        "workers.write"
    ],
    "expires_at": "2052-05-30"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-api-keys">
</span>
<span id="execution-results-POSTapi-v1-auth-api-keys" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-api-keys"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-api-keys"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-api-keys" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-api-keys">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-api-keys" data-method="POST"
      data-path="api/v1/auth/api-keys"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-api-keys', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-api-keys"
                    onclick="tryItOut('POSTapi-v1-auth-api-keys');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-api-keys"
                    onclick="cancelTryOut('POSTapi-v1-auth-api-keys');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-api-keys"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/api-keys</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-auth-api-keys"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-api-keys"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-api-keys"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-auth-api-keys"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 64 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>abilities</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="abilities[0]"                data-endpoint="POSTapi-v1-auth-api-keys"
               data-component="body">
        <input type="text" style="display: none"
               name="abilities[1]"                data-endpoint="POSTapi-v1-auth-api-keys"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>workers.read</code></li> <li><code>workers.write</code></li> <li><code>equipment.read</code></li> <li><code>equipment.write</code></li> <li><code>permits.read</code></li> <li><code>permits.write</code></li> <li><code>scans.read</code></li> <li><code>scans.create</code></li> <li><code>hazards.read</code></li> <li><code>hazards.write</code></li> <li><code>webhooks.manage</code></li> <li><code>dashboards.read</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>expires_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="expires_at"                data-endpoint="POSTapi-v1-auth-api-keys"
               value="2052-05-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after <code>now</code>. Example: <code>2052-05-30</code></p>
        </div>
        </form>

                    <h2 id="api-keys-DELETEapi-v1-auth-api-keys--tokenId-">Revoke an API key.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-auth-api-keys--tokenId-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/auth/api-keys/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/api-keys/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-auth-api-keys--tokenId-">
</span>
<span id="execution-results-DELETEapi-v1-auth-api-keys--tokenId-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-auth-api-keys--tokenId-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-auth-api-keys--tokenId-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-auth-api-keys--tokenId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-auth-api-keys--tokenId-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-auth-api-keys--tokenId-" data-method="DELETE"
      data-path="api/v1/auth/api-keys/{tokenId}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-auth-api-keys--tokenId-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-auth-api-keys--tokenId-"
                    onclick="tryItOut('DELETEapi-v1-auth-api-keys--tokenId-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-auth-api-keys--tokenId-"
                    onclick="cancelTryOut('DELETEapi-v1-auth-api-keys--tokenId-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-auth-api-keys--tokenId-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/auth/api-keys/{tokenId}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-v1-auth-api-keys--tokenId-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-auth-api-keys--tokenId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-auth-api-keys--tokenId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>tokenId</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tokenId"                data-endpoint="DELETEapi-v1-auth-api-keys--tokenId-"
               value="architecto"
               data-component="url">
    <br>
<p>Example: <code>architecto</code></p>
            </div>
                    </form>

                <h1 id="authentication">Authentication</h1>

    <p>Login flow supports two modes:</p>
<p>mode=cookie (default): web SPA path. Caller must hit /sanctum/csrf-cookie
first, then POST /auth/login with the XSRF-TOKEN header. Sanctum
creates a session cookie; subsequent requests authenticate via cookie.</p>
<p>mode=token: mobile + ERP path. Returns a fresh Sanctum personal access
token. Caller stores the token (Keychain/Keystore for mobile) and
sends it as Authorization: Bearer <token> on every request.</p>

                                <h2 id="authentication-POSTapi-v1-auth-login">Log in.</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-login">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"gbailey@example.net\",
    \"password\": \"|]|{+-\",
    \"mode\": \"token\",
    \"device_name\": \"v\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "gbailey@example.net",
    "password": "|]|{+-",
    "mode": "token",
    "device_name": "v"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-login">
</span>
<span id="execution-results-POSTapi-v1-auth-login" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-login"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-login"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-login" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-login" data-method="POST"
      data-path="api/v1/auth/login"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-login', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-login"
                    onclick="tryItOut('POSTapi-v1-auth-login');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-login"
                    onclick="cancelTryOut('POSTapi-v1-auth-login');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-login"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/login</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-login"
               value="gbailey@example.net"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>gbailey@example.net</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-login"
               value="|]|{+-"
               data-component="body">
    <br>
<p>Example: <code>|]|{+-</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mode</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="mode"                data-endpoint="POSTapi-v1-auth-login"
               value="token"
               data-component="body">
    <br>
<p>Example: <code>token</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>cookie</code></li> <li><code>token</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-v1-auth-login"
               value="v"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>v</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-v1-auth-logout">Log out the current user.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>For cookie sessions: invalidates and regenerates the session.
For token auth: revokes the bearer token used for this request.</p>

<span id="example-requests-POSTapi-v1-auth-logout">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/logout" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/logout"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-logout">
</span>
<span id="execution-results-POSTapi-v1-auth-logout" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-logout"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-logout"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-logout" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-logout" data-method="POST"
      data-path="api/v1/auth/logout"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-logout', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-logout"
                    onclick="tryItOut('POSTapi-v1-auth-logout');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-logout"
                    onclick="cancelTryOut('POSTapi-v1-auth-logout');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-logout"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/logout</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-auth-logout"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="dashboards">Dashboards</h1>

    <p>Role-based dashboard summaries. Each route picks the user's active org
(first matching the requested role) and computes the metrics.</p>

                                <h2 id="dashboards-GETapi-v1-dashboards-client-summary">Client dashboard: cross-project metrics across all owned projects.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-dashboards-client-summary">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboards/client/summary" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboards/client/summary"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-dashboards-client-summary">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: e8e8ac63-5820-406d-8e06-b4a72b00f20a
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;e8e8ac63-5820-406d-8e06-b4a72b00f20a&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-dashboards-client-summary" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-dashboards-client-summary"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-dashboards-client-summary"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-dashboards-client-summary" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-dashboards-client-summary">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-dashboards-client-summary" data-method="GET"
      data-path="api/v1/dashboards/client/summary"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboards-client-summary', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-dashboards-client-summary"
                    onclick="tryItOut('GETapi-v1-dashboards-client-summary');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-dashboards-client-summary"
                    onclick="cancelTryOut('GETapi-v1-dashboards-client-summary');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-dashboards-client-summary"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/dashboards/client/summary</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-dashboards-client-summary"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-dashboards-client-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-dashboards-client-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="dashboards-GETapi-v1-dashboards-main-contractor-summary">Main contractor dashboard.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-dashboards-main-contractor-summary">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboards/main-contractor/summary" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboards/main-contractor/summary"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-dashboards-main-contractor-summary">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: b668904c-056f-46ef-a2f0-cd9f37206c49
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;b668904c-056f-46ef-a2f0-cd9f37206c49&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-dashboards-main-contractor-summary" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-dashboards-main-contractor-summary"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-dashboards-main-contractor-summary"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-dashboards-main-contractor-summary" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-dashboards-main-contractor-summary">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-dashboards-main-contractor-summary" data-method="GET"
      data-path="api/v1/dashboards/main-contractor/summary"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboards-main-contractor-summary', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-dashboards-main-contractor-summary"
                    onclick="tryItOut('GETapi-v1-dashboards-main-contractor-summary');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-dashboards-main-contractor-summary"
                    onclick="cancelTryOut('GETapi-v1-dashboards-main-contractor-summary');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-dashboards-main-contractor-summary"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/dashboards/main-contractor/summary</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-dashboards-main-contractor-summary"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-dashboards-main-contractor-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-dashboards-main-contractor-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="dashboards-GETapi-v1-dashboards-consultant-summary">Consultant dashboard.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-dashboards-consultant-summary">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboards/consultant/summary" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboards/consultant/summary"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-dashboards-consultant-summary">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 17d81c0f-6800-48ff-823f-1b9639c606c6
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;17d81c0f-6800-48ff-823f-1b9639c606c6&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-dashboards-consultant-summary" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-dashboards-consultant-summary"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-dashboards-consultant-summary"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-dashboards-consultant-summary" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-dashboards-consultant-summary">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-dashboards-consultant-summary" data-method="GET"
      data-path="api/v1/dashboards/consultant/summary"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboards-consultant-summary', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-dashboards-consultant-summary"
                    onclick="tryItOut('GETapi-v1-dashboards-consultant-summary');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-dashboards-consultant-summary"
                    onclick="cancelTryOut('GETapi-v1-dashboards-consultant-summary');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-dashboards-consultant-summary"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/dashboards/consultant/summary</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-dashboards-consultant-summary"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-dashboards-consultant-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-dashboards-consultant-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="dashboards-GETapi-v1-dashboards-subcontractor-summary">Subcontractor dashboard.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-dashboards-subcontractor-summary">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboards/subcontractor/summary" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboards/subcontractor/summary"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-dashboards-subcontractor-summary">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 20e2e09e-d9b9-4d4c-bb87-94eb70f77267
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;20e2e09e-d9b9-4d4c-bb87-94eb70f77267&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-dashboards-subcontractor-summary" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-dashboards-subcontractor-summary"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-dashboards-subcontractor-summary"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-dashboards-subcontractor-summary" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-dashboards-subcontractor-summary">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-dashboards-subcontractor-summary" data-method="GET"
      data-path="api/v1/dashboards/subcontractor/summary"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboards-subcontractor-summary', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-dashboards-subcontractor-summary"
                    onclick="tryItOut('GETapi-v1-dashboards-subcontractor-summary');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-dashboards-subcontractor-summary"
                    onclick="cancelTryOut('GETapi-v1-dashboards-subcontractor-summary');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-dashboards-subcontractor-summary"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/dashboards/subcontractor/summary</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-dashboards-subcontractor-summary"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-dashboards-subcontractor-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-dashboards-subcontractor-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="equipment">Equipment</h1>

    

                                <h2 id="equipment-POSTapi-v1-equipment-bulk">Bulk-import equipment. Same per-record-success/failure semantics as
the worker bulk endpoint.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-equipment-bulk">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/equipment/bulk" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"equipment\": [
        []
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment/bulk"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "equipment": [
        []
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-equipment-bulk">
</span>
<span id="execution-results-POSTapi-v1-equipment-bulk" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-equipment-bulk"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-equipment-bulk"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-equipment-bulk" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-equipment-bulk">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-equipment-bulk" data-method="POST"
      data-path="api/v1/equipment/bulk"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-equipment-bulk', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-equipment-bulk"
                    onclick="tryItOut('POSTapi-v1-equipment-bulk');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-equipment-bulk"
                    onclick="cancelTryOut('POSTapi-v1-equipment-bulk');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-equipment-bulk"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/equipment/bulk</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-equipment-bulk"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-equipment-bulk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-equipment-bulk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>equipment</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="equipment[0]"                data-endpoint="POSTapi-v1-equipment-bulk"
               data-component="body">
        <input type="text" style="display: none"
               name="equipment[1]"                data-endpoint="POSTapi-v1-equipment-bulk"
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="equipment-GETapi-v1-equipment">List equipment.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-equipment">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/equipment" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-equipment">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 6cc3abe6-7be3-4a8d-bc5c-c2342918c5b5
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;6cc3abe6-7be3-4a8d-bc5c-c2342918c5b5&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-equipment" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-equipment"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-equipment"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-equipment" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-equipment">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-equipment" data-method="GET"
      data-path="api/v1/equipment"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-equipment', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-equipment"
                    onclick="tryItOut('GETapi-v1-equipment');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-equipment"
                    onclick="cancelTryOut('GETapi-v1-equipment');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-equipment"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/equipment</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-equipment"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-equipment"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-equipment"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="equipment-POSTapi-v1-equipment">POST api/v1/equipment</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-equipment">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/equipment" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"owner_organization_id\": \"6ff8f7f6-1eb3-3525-be4a-3932c805afed\",
    \"asset_tag\": \"g\",
    \"serial_number\": \"z\",
    \"manufacturer\": \"m\",
    \"model\": \"i\",
    \"type\": \"y\",
    \"category\": \"v\",
    \"manufacture_date\": \"2022-05-31\",
    \"safe_working_load_kg\": 39
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "owner_organization_id": "6ff8f7f6-1eb3-3525-be4a-3932c805afed",
    "asset_tag": "g",
    "serial_number": "z",
    "manufacturer": "m",
    "model": "i",
    "type": "y",
    "category": "v",
    "manufacture_date": "2022-05-31",
    "safe_working_load_kg": 39
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-equipment">
</span>
<span id="execution-results-POSTapi-v1-equipment" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-equipment"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-equipment"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-equipment" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-equipment">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-equipment" data-method="POST"
      data-path="api/v1/equipment"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-equipment', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-equipment"
                    onclick="tryItOut('POSTapi-v1-equipment');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-equipment"
                    onclick="cancelTryOut('POSTapi-v1-equipment');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-equipment"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/equipment</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-equipment"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-equipment"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-equipment"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>owner_organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="owner_organization_id"                data-endpoint="POSTapi-v1-equipment"
               value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the organizations table. Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>asset_tag</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="asset_tag"                data-endpoint="POSTapi-v1-equipment"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 64 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>serial_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="serial_number"                data-endpoint="POSTapi-v1-equipment"
               value="z"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>z</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>manufacturer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="manufacturer"                data-endpoint="POSTapi-v1-equipment"
               value="m"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>m</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>model</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="model"                data-endpoint="POSTapi-v1-equipment"
               value="i"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>i</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="POSTapi-v1-equipment"
               value="y"
               data-component="body">
    <br>
<p>Must not be greater than 64 characters. Example: <code>y</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="category"                data-endpoint="POSTapi-v1-equipment"
               value="v"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>v</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>manufacture_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="manufacture_date"                data-endpoint="POSTapi-v1-equipment"
               value="2022-05-31"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date before or equal to <code>today</code>. Example: <code>2022-05-31</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>safe_working_load_kg</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="safe_working_load_kg"                data-endpoint="POSTapi-v1-equipment"
               value="39"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>39</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>specs</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="specs"                data-endpoint="POSTapi-v1-equipment"
               value=""
               data-component="body">
    <br>

        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>metadata</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="metadata"                data-endpoint="POSTapi-v1-equipment"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="equipment-GETapi-v1-equipment--id-">GET api/v1/equipment/{id}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-equipment--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/equipment/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-equipment--id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 245e99f3-8077-4568-8c58-39ecba8618e9
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;245e99f3-8077-4568-8c58-39ecba8618e9&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-equipment--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-equipment--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-equipment--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-equipment--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-equipment--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-equipment--id-" data-method="GET"
      data-path="api/v1/equipment/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-equipment--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-equipment--id-"
                    onclick="tryItOut('GETapi-v1-equipment--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-equipment--id-"
                    onclick="cancelTryOut('GETapi-v1-equipment--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-equipment--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/equipment/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-equipment--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-equipment--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-equipment--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-v1-equipment--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the equipment. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="equipment-PATCHapi-v1-equipment--id-">PATCH api/v1/equipment/{id}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PATCHapi-v1-equipment--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/equipment/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"asset_tag\": \"b\",
    \"serial_number\": \"n\",
    \"manufacturer\": \"g\",
    \"model\": \"z\",
    \"type\": \"m\",
    \"category\": \"i\",
    \"manufacture_date\": \"2026-05-07T09:16:52\",
    \"safe_working_load_kg\": 76
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "asset_tag": "b",
    "serial_number": "n",
    "manufacturer": "g",
    "model": "z",
    "type": "m",
    "category": "i",
    "manufacture_date": "2026-05-07T09:16:52",
    "safe_working_load_kg": 76
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-equipment--id-">
</span>
<span id="execution-results-PATCHapi-v1-equipment--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-equipment--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-equipment--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-equipment--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-equipment--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-equipment--id-" data-method="PATCH"
      data-path="api/v1/equipment/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-equipment--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-equipment--id-"
                    onclick="tryItOut('PATCHapi-v1-equipment--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-equipment--id-"
                    onclick="cancelTryOut('PATCHapi-v1-equipment--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-equipment--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/equipment/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PATCHapi-v1-equipment--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the equipment. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>asset_tag</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="asset_tag"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 64 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>serial_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="serial_number"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>manufacturer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="manufacturer"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>model</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="model"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="z"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>z</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="m"
               data-component="body">
    <br>
<p>Must not be greater than 64 characters. Example: <code>m</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="category"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="i"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>i</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>manufacture_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="manufacture_date"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="2026-05-07T09:16:52"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-07T09:16:52</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>safe_working_load_kg</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="safe_working_load_kg"                data-endpoint="PATCHapi-v1-equipment--id-"
               value="76"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>76</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>specs</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="specs"                data-endpoint="PATCHapi-v1-equipment--id-"
               value=""
               data-component="body">
    <br>

        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>metadata</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="metadata"                data-endpoint="PATCHapi-v1-equipment--id-"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="equipment-DELETEapi-v1-equipment--id-">DELETE api/v1/equipment/{id}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-equipment--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/equipment/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-equipment--id-">
</span>
<span id="execution-results-DELETEapi-v1-equipment--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-equipment--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-equipment--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-equipment--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-equipment--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-equipment--id-" data-method="DELETE"
      data-path="api/v1/equipment/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-equipment--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-equipment--id-"
                    onclick="tryItOut('DELETEapi-v1-equipment--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-equipment--id-"
                    onclick="cancelTryOut('DELETEapi-v1-equipment--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-equipment--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/equipment/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-v1-equipment--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-equipment--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-equipment--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="DELETEapi-v1-equipment--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the equipment. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="equipment-GETapi-v1-equipment--equipment_id--qr">Equipment QR PNG (image/png). Used at print time for adhesive labels.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-equipment--equipment_id--qr">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/equipment/architecto/qr" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment/architecto/qr"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-equipment--equipment_id--qr">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 51fe5095-4e43-42e9-a6f5-2b834d359b7c
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;51fe5095-4e43-42e9-a6f5-2b834d359b7c&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-equipment--equipment_id--qr" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-equipment--equipment_id--qr"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-equipment--equipment_id--qr"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-equipment--equipment_id--qr" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-equipment--equipment_id--qr">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-equipment--equipment_id--qr" data-method="GET"
      data-path="api/v1/equipment/{equipment_id}/qr"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-equipment--equipment_id--qr', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-equipment--equipment_id--qr"
                    onclick="tryItOut('GETapi-v1-equipment--equipment_id--qr');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-equipment--equipment_id--qr"
                    onclick="cancelTryOut('GETapi-v1-equipment--equipment_id--qr');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-equipment--equipment_id--qr"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/equipment/{equipment_id}/qr</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-equipment--equipment_id--qr"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-equipment--equipment_id--qr"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-equipment--equipment_id--qr"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>equipment_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="equipment_id"                data-endpoint="GETapi-v1-equipment--equipment_id--qr"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the equipment. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="equipment-POSTapi-v1-equipment--equipment_id--certifications">Attach a TPI inspection certificate.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-equipment--equipment_id--certifications">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/equipment/architecto/certifications" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"certificate_number\": \"b\",
    \"inspection_type\": \"periodic\",
    \"tpi_body_en\": \"n\",
    \"tpi_body_ar\": \"g\",
    \"inspector_name\": \"z\",
    \"inspection_date\": \"2022-05-31\",
    \"expiry_date\": \"2052-05-30\",
    \"result\": \"pass_with_conditions\",
    \"conditions_en\": \"n\",
    \"conditions_ar\": \"g\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment/architecto/certifications"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "certificate_number": "b",
    "inspection_type": "periodic",
    "tpi_body_en": "n",
    "tpi_body_ar": "g",
    "inspector_name": "z",
    "inspection_date": "2022-05-31",
    "expiry_date": "2052-05-30",
    "result": "pass_with_conditions",
    "conditions_en": "n",
    "conditions_ar": "g"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-equipment--equipment_id--certifications">
</span>
<span id="execution-results-POSTapi-v1-equipment--equipment_id--certifications" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-equipment--equipment_id--certifications"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-equipment--equipment_id--certifications"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-equipment--equipment_id--certifications" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-equipment--equipment_id--certifications">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-equipment--equipment_id--certifications" data-method="POST"
      data-path="api/v1/equipment/{equipment_id}/certifications"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-equipment--equipment_id--certifications', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-equipment--equipment_id--certifications"
                    onclick="tryItOut('POSTapi-v1-equipment--equipment_id--certifications');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-equipment--equipment_id--certifications"
                    onclick="cancelTryOut('POSTapi-v1-equipment--equipment_id--certifications');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-equipment--equipment_id--certifications"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/equipment/{equipment_id}/certifications</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>equipment_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="equipment_id"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the equipment. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>certificate_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="certificate_number"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>inspection_type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="inspection_type"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="periodic"
               data-component="body">
    <br>
<p>Example: <code>periodic</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>periodic</code></li> <li><code>major</code></li> <li><code>post_repair</code></li> <li><code>initial</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>tpi_body_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tpi_body_en"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>tpi_body_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tpi_body_ar"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>inspector_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="inspector_name"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="z"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>z</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>inspection_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="inspection_date"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="2022-05-31"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date before or equal to <code>today</code>. Example: <code>2022-05-31</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>expiry_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="expiry_date"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="2052-05-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after <code>inspection_date</code>. Example: <code>2052-05-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>result</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="result"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="pass_with_conditions"
               data-component="body">
    <br>
<p>Example: <code>pass_with_conditions</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>pass</code></li> <li><code>pass_with_conditions</code></li> <li><code>fail</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>conditions_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="conditions_en"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>conditions_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="conditions_ar"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>metadata</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="metadata"                data-endpoint="POSTapi-v1-equipment--equipment_id--certifications"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="equipment-POSTapi-v1-equipment--equipment_id--operators">Pair an authorized operator with this equipment. Validates that the
worker holds at least one operator-relevant certification before
allowing the pairing. (For week 1 we accept any cert; per-equipment-type
required certs come later when we wire the equipment-type catalog.)</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-equipment--equipment_id--operators">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/equipment/architecto/operators" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"worker_id\": \"6ff8f7f6-1eb3-3525-be4a-3932c805afed\",
    \"valid_from\": \"2026-05-07T09:16:53\",
    \"valid_until\": \"2052-05-30\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/equipment/architecto/operators"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "worker_id": "6ff8f7f6-1eb3-3525-be4a-3932c805afed",
    "valid_from": "2026-05-07T09:16:53",
    "valid_until": "2052-05-30"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-equipment--equipment_id--operators">
</span>
<span id="execution-results-POSTapi-v1-equipment--equipment_id--operators" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-equipment--equipment_id--operators"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-equipment--equipment_id--operators"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-equipment--equipment_id--operators" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-equipment--equipment_id--operators">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-equipment--equipment_id--operators" data-method="POST"
      data-path="api/v1/equipment/{equipment_id}/operators"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-equipment--equipment_id--operators', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-equipment--equipment_id--operators"
                    onclick="tryItOut('POSTapi-v1-equipment--equipment_id--operators');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-equipment--equipment_id--operators"
                    onclick="cancelTryOut('POSTapi-v1-equipment--equipment_id--operators');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-equipment--equipment_id--operators"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/equipment/{equipment_id}/operators</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-equipment--equipment_id--operators"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-equipment--equipment_id--operators"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-equipment--equipment_id--operators"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>equipment_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="equipment_id"                data-endpoint="POSTapi-v1-equipment--equipment_id--operators"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the equipment. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="POSTapi-v1-equipment--equipment_id--operators"
               value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the workers table. Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>valid_from</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="valid_from"                data-endpoint="POSTapi-v1-equipment--equipment_id--operators"
               value="2026-05-07T09:16:53"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-07T09:16:53</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>valid_until</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="valid_until"                data-endpoint="POSTapi-v1-equipment--equipment_id--operators"
               value="2052-05-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after <code>valid_from</code>. Example: <code>2052-05-30</code></p>
        </div>
        </form>

                <h1 id="hazard-reports-anonymous-public">Hazard Reports — Anonymous (public)</h1>

    <p>Public endpoints for the anonymous reporter flow. No PII captured at the
application layer:</p>
<ul>
<li>no IP stored</li>
<li>no User-Agent / device fingerprint stored</li>
<li>no submitter_id column at the schema level (see migration)</li>
</ul>
<p>The handle returned is anonymous_report_id (random UUID); the submitter
uses it to check status without authenticating.</p>

                                <h2 id="hazard-reports-anonymous-public-POSTapi-v1-hazard-reports-anonymous">Submit an anonymous hazard report. Photo EXIF stripped server-side
before storage.</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-hazard-reports-anonymous">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/hazard-reports/anonymous" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "category=fire"\
    --form "severity=high"\
    --form "description=Eius et animi quos velit et."\
    --form "description_lang=ar"\
    --form "project_id=21c4122b-d554-3723-966c-6d723ea5293f"\
    --form "site_id=add3503c-ebff-3875-93af-b8c6a695762b"\
    --form "latitude=-89"\
    --form "longitude=-180"\
    --form "photo=@/tmp/php1bo9hvdr83p64GnvSbh" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/hazard-reports/anonymous"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('category', 'fire');
body.append('severity', 'high');
body.append('description', 'Eius et animi quos velit et.');
body.append('description_lang', 'ar');
body.append('project_id', '21c4122b-d554-3723-966c-6d723ea5293f');
body.append('site_id', 'add3503c-ebff-3875-93af-b8c6a695762b');
body.append('latitude', '-89');
body.append('longitude', '-180');
body.append('photo', document.querySelector('input[name="photo"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-hazard-reports-anonymous">
</span>
<span id="execution-results-POSTapi-v1-hazard-reports-anonymous" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-hazard-reports-anonymous"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-hazard-reports-anonymous"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-hazard-reports-anonymous" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-hazard-reports-anonymous">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-hazard-reports-anonymous" data-method="POST"
      data-path="api/v1/hazard-reports/anonymous"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-hazard-reports-anonymous', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-hazard-reports-anonymous"
                    onclick="tryItOut('POSTapi-v1-hazard-reports-anonymous');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-hazard-reports-anonymous"
                    onclick="cancelTryOut('POSTapi-v1-hazard-reports-anonymous');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-hazard-reports-anonymous"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/hazard-reports/anonymous</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>photo</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="photo"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value=""
               data-component="body">
    <br>
<p>Must be a file. Must be an image. Must not be greater than 10240 kilobytes. Example: <code>/tmp/php1bo9hvdr83p64GnvSbh</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="category"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="fire"
               data-component="body">
    <br>
<p>Example: <code>fire</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>fall</code></li> <li><code>electrical</code></li> <li><code>fire</code></li> <li><code>working_at_heights</code></li> <li><code>lifting</code></li> <li><code>housekeeping</code></li> <li><code>ppe</code></li> <li><code>environmental</code></li> <li><code>other</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>severity</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="severity"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="high"
               data-component="body">
    <br>
<p>Example: <code>high</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>low</code></li> <li><code>medium</code></li> <li><code>high</code></li> <li><code>critical</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="Eius et animi quos velit et."
               data-component="body">
    <br>
<p>Must not be greater than 2000 characters. Example: <code>Eius et animi quos velit et.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description_lang</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description_lang"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="ar"
               data-component="body">
    <br>
<p>Example: <code>ar</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>en</code></li> <li><code>ar</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="project_id"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="21c4122b-d554-3723-966c-6d723ea5293f"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the projects table. Example: <code>21c4122b-d554-3723-966c-6d723ea5293f</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>site_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="site_id"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="add3503c-ebff-3875-93af-b8c6a695762b"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the sites table. Example: <code>add3503c-ebff-3875-93af-b8c6a695762b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>latitude</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="latitude"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="-89"
               data-component="body">
    <br>
<p>Must be between -90 and 90. Example: <code>-89</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>longitude</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="longitude"                data-endpoint="POSTapi-v1-hazard-reports-anonymous"
               value="-180"
               data-component="body">
    <br>
<p>Must be between -180 and 180. Example: <code>-180</code></p>
        </div>
        </form>

                    <h2 id="hazard-reports-anonymous-public-GETapi-v1-hazard-reports-anonymous--anonymousReportId-">Public status check. Returns ONLY non-PII fields and PUBLIC notes.</h2>

<p>
</p>

<p>Internal contractor notes are never exposed here.</p>

<span id="example-requests-GETapi-v1-hazard-reports-anonymous--anonymousReportId-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/hazard-reports/anonymous/architecto" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/hazard-reports/anonymous/architecto"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-hazard-reports-anonymous--anonymousReportId-">
            <blockquote>
            <p>Example response (500):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-ratelimit-limit: 10
x-ratelimit-remaining: 9
x-request-id: 84dddc6e-8f6b-4d7e-82ce-f0bf52120b22
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Server Error&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-hazard-reports-anonymous--anonymousReportId-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-hazard-reports-anonymous--anonymousReportId-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-hazard-reports-anonymous--anonymousReportId-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-hazard-reports-anonymous--anonymousReportId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-hazard-reports-anonymous--anonymousReportId-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-hazard-reports-anonymous--anonymousReportId-" data-method="GET"
      data-path="api/v1/hazard-reports/anonymous/{anonymousReportId}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-hazard-reports-anonymous--anonymousReportId-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-hazard-reports-anonymous--anonymousReportId-"
                    onclick="tryItOut('GETapi-v1-hazard-reports-anonymous--anonymousReportId-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-hazard-reports-anonymous--anonymousReportId-"
                    onclick="cancelTryOut('GETapi-v1-hazard-reports-anonymous--anonymousReportId-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-hazard-reports-anonymous--anonymousReportId-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/hazard-reports/anonymous/{anonymousReportId}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-hazard-reports-anonymous--anonymousReportId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-hazard-reports-anonymous--anonymousReportId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>anonymousReportId</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="anonymousReportId"                data-endpoint="GETapi-v1-hazard-reports-anonymous--anonymousReportId-"
               value="architecto"
               data-component="url">
    <br>
<p>Example: <code>architecto</code></p>
            </div>
                    </form>

                <h1 id="hazard-reports-authenticated">Hazard Reports — Authenticated</h1>

    <p>Authenticated views: list, single read, status updates, internal/public notes.</p>
<p>Authorization scoping is handled at the policy layer (added in Phase 3+).
For week-1 the controller is auth-only; per-user/per-org filtering will be
layered in via policy + query scope.</p>

                                <h2 id="hazard-reports-authenticated-GETapi-v1-hazard-reports">GET api/v1/hazard-reports</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-hazard-reports">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/hazard-reports" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/hazard-reports"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-hazard-reports">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 6f1a51f1-b39e-4901-8096-1c073ec8bcdd
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;6f1a51f1-b39e-4901-8096-1c073ec8bcdd&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-hazard-reports" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-hazard-reports"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-hazard-reports"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-hazard-reports" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-hazard-reports">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-hazard-reports" data-method="GET"
      data-path="api/v1/hazard-reports"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-hazard-reports', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-hazard-reports"
                    onclick="tryItOut('GETapi-v1-hazard-reports');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-hazard-reports"
                    onclick="cancelTryOut('GETapi-v1-hazard-reports');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-hazard-reports"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/hazard-reports</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-hazard-reports"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-hazard-reports"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-hazard-reports"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="hazard-reports-authenticated-GETapi-v1-hazard-reports--hazardReport_id-">GET api/v1/hazard-reports/{hazardReport_id}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-hazard-reports--hazardReport_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/hazard-reports/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/hazard-reports/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-hazard-reports--hazardReport_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 77902502-fbed-4961-bde0-a119e345f28e
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;77902502-fbed-4961-bde0-a119e345f28e&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-hazard-reports--hazardReport_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-hazard-reports--hazardReport_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-hazard-reports--hazardReport_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-hazard-reports--hazardReport_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-hazard-reports--hazardReport_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-hazard-reports--hazardReport_id-" data-method="GET"
      data-path="api/v1/hazard-reports/{hazardReport_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-hazard-reports--hazardReport_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-hazard-reports--hazardReport_id-"
                    onclick="tryItOut('GETapi-v1-hazard-reports--hazardReport_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-hazard-reports--hazardReport_id-"
                    onclick="cancelTryOut('GETapi-v1-hazard-reports--hazardReport_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-hazard-reports--hazardReport_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/hazard-reports/{hazardReport_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-hazard-reports--hazardReport_id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-hazard-reports--hazardReport_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-hazard-reports--hazardReport_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>hazardReport_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="hazardReport_id"                data-endpoint="GETapi-v1-hazard-reports--hazardReport_id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the hazardReport. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="hazard-reports-authenticated-PATCHapi-v1-hazard-reports--hazardReport_id-">Update status / assignment / resolution of a hazard report.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PATCHapi-v1-hazard-reports--hazardReport_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/hazard-reports/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"status\": \"action_issued\",
    \"resolution_summary\": \"b\",
    \"assigned_to_user_id\": 16,
    \"assigned_to_organization_id\": \"a4855dc5-0acb-33c3-b921-f4291f719ca0\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/hazard-reports/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": "action_issued",
    "resolution_summary": "b",
    "assigned_to_user_id": 16,
    "assigned_to_organization_id": "a4855dc5-0acb-33c3-b921-f4291f719ca0"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-hazard-reports--hazardReport_id-">
</span>
<span id="execution-results-PATCHapi-v1-hazard-reports--hazardReport_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-hazard-reports--hazardReport_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-hazard-reports--hazardReport_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-hazard-reports--hazardReport_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-hazard-reports--hazardReport_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-hazard-reports--hazardReport_id-" data-method="PATCH"
      data-path="api/v1/hazard-reports/{hazardReport_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-hazard-reports--hazardReport_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-hazard-reports--hazardReport_id-"
                    onclick="tryItOut('PATCHapi-v1-hazard-reports--hazardReport_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-hazard-reports--hazardReport_id-"
                    onclick="cancelTryOut('PATCHapi-v1-hazard-reports--hazardReport_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-hazard-reports--hazardReport_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/hazard-reports/{hazardReport_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PATCHapi-v1-hazard-reports--hazardReport_id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-hazard-reports--hazardReport_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-hazard-reports--hazardReport_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>hazardReport_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="hazardReport_id"                data-endpoint="PATCHapi-v1-hazard-reports--hazardReport_id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the hazardReport. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PATCHapi-v1-hazard-reports--hazardReport_id-"
               value="action_issued"
               data-component="body">
    <br>
<p>Example: <code>action_issued</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>under_review</code></li> <li><code>action_issued</code></li> <li><code>resolved</code></li> <li><code>dismissed</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>resolution_summary</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="resolution_summary"                data-endpoint="PATCHapi-v1-hazard-reports--hazardReport_id-"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 2000 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>assigned_to_user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="assigned_to_user_id"                data-endpoint="PATCHapi-v1-hazard-reports--hazardReport_id-"
               value="16"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table. Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>assigned_to_organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="assigned_to_organization_id"                data-endpoint="PATCHapi-v1-hazard-reports--hazardReport_id-"
               value="a4855dc5-0acb-33c3-b921-f4291f719ca0"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the organizations table. Example: <code>a4855dc5-0acb-33c3-b921-f4291f719ca0</code></p>
        </div>
        </form>

                    <h2 id="hazard-reports-authenticated-POSTapi-v1-hazard-reports--hazardReport_id--notes">Add an internal or public note. Internal notes are visible only via
authenticated GET; public notes appear on the anonymous status check.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-hazard-reports--hazardReport_id--notes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/hazard-reports/architecto/notes" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"note_type\": \"public\",
    \"body\": \"b\",
    \"body_lang\": \"ar\",
    \"author_organization_id\": \"a4855dc5-0acb-33c3-b921-f4291f719ca0\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/hazard-reports/architecto/notes"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note_type": "public",
    "body": "b",
    "body_lang": "ar",
    "author_organization_id": "a4855dc5-0acb-33c3-b921-f4291f719ca0"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-hazard-reports--hazardReport_id--notes">
</span>
<span id="execution-results-POSTapi-v1-hazard-reports--hazardReport_id--notes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-hazard-reports--hazardReport_id--notes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-hazard-reports--hazardReport_id--notes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-hazard-reports--hazardReport_id--notes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-hazard-reports--hazardReport_id--notes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-hazard-reports--hazardReport_id--notes" data-method="POST"
      data-path="api/v1/hazard-reports/{hazardReport_id}/notes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-hazard-reports--hazardReport_id--notes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-hazard-reports--hazardReport_id--notes"
                    onclick="tryItOut('POSTapi-v1-hazard-reports--hazardReport_id--notes');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-hazard-reports--hazardReport_id--notes"
                    onclick="cancelTryOut('POSTapi-v1-hazard-reports--hazardReport_id--notes');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-hazard-reports--hazardReport_id--notes"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/hazard-reports/{hazardReport_id}/notes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-hazard-reports--hazardReport_id--notes"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-hazard-reports--hazardReport_id--notes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-hazard-reports--hazardReport_id--notes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>hazardReport_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="hazardReport_id"                data-endpoint="POSTapi-v1-hazard-reports--hazardReport_id--notes"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the hazardReport. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>note_type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="note_type"                data-endpoint="POSTapi-v1-hazard-reports--hazardReport_id--notes"
               value="public"
               data-component="body">
    <br>
<p>Example: <code>public</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>internal</code></li> <li><code>public</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>body</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="body"                data-endpoint="POSTapi-v1-hazard-reports--hazardReport_id--notes"
               value="b"
               data-component="body">
    <br>
<p>Must be at least 1 character. Must not be greater than 4000 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>body_lang</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="body_lang"                data-endpoint="POSTapi-v1-hazard-reports--hazardReport_id--notes"
               value="ar"
               data-component="body">
    <br>
<p>Example: <code>ar</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>en</code></li> <li><code>ar</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>author_organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="author_organization_id"                data-endpoint="POSTapi-v1-hazard-reports--hazardReport_id--notes"
               value="a4855dc5-0acb-33c3-b921-f4291f719ca0"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the organizations table. Example: <code>a4855dc5-0acb-33c3-b921-f4291f719ca0</code></p>
        </div>
        </form>

                <h1 id="health">Health</h1>

    <p>Connectivity and identity probes used for monitoring and the OpenAPI
"Try it out" sanity check.</p>

                                <h2 id="health-GETapi-v1-health">Health check.</h2>

<p>
</p>

<p>Returns service status plus dependency probe results. Used by uptime
monitors and the OpenAPI docs landing page.</p>

<span id="example-requests-GETapi-v1-health">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/health" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/health"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-health">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 27d9a748-ee1c-438b-a946-74ea7728d625
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;ok&quot;,
    &quot;service&quot;: &quot;ePassport API&quot;,
    &quot;version&quot;: &quot;v1&quot;,
    &quot;time&quot;: &quot;2026-05-07T09:16:52+00:00&quot;,
    &quot;checks&quot;: {
        &quot;database&quot;: {
            &quot;ok&quot;: true
        },
        &quot;redis&quot;: {
            &quot;ok&quot;: true
        }
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-health" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-health"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-health"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-health" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-health">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-health" data-method="GET"
      data-path="api/v1/health"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-health', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-health"
                    onclick="tryItOut('GETapi-v1-health');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-health"
                    onclick="cancelTryOut('GETapi-v1-health');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-health"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/health</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-health"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-health"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="health-GETapi-v1-me">Authenticated identity.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns the authenticated user with their organization memberships and
roles. Used by both web (cookie session) and mobile (PAT) clients to
bootstrap the app shell after login.</p>

<span id="example-requests-GETapi-v1-me">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/me" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/me"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 447edd23-1736-4ba1-8217-8754d5092ac8
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;447edd23-1736-4ba1-8217-8754d5092ac8&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-me" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-me"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-me"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-me">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me" data-method="GET"
      data-path="api/v1/me"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-me"
                    onclick="tryItOut('GETapi-v1-me');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-me"
                    onclick="cancelTryOut('GETapi-v1-me');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-me"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/me</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-me"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="permits">Permits</h1>

    <p>Permit-to-Work create + attach. Lifecycle transitions (submit, approve,
reject, close) are handled by PermitLifecycleController in Phase 3.4-3.5.</p>

                                <h2 id="permits-GETapi-v1-permits">GET api/v1/permits</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-permits">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/permits" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-permits">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 95c56c0f-6c5c-49de-90a8-f89f346c59af
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;95c56c0f-6c5c-49de-90a8-f89f346c59af&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-permits" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-permits"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-permits"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-permits" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-permits">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-permits" data-method="GET"
      data-path="api/v1/permits"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-permits', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-permits"
                    onclick="tryItOut('GETapi-v1-permits');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-permits"
                    onclick="cancelTryOut('GETapi-v1-permits');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-permits"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/permits</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-permits"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-permits"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-permits"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="permits-POSTapi-v1-permits">Create a draft permit.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-permits">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/permits" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"project_id\": \"6ff8f7f6-1eb3-3525-be4a-3932c805afed\",
    \"site_id\": \"6b72fe4a-5b40-307c-bc24-f79acf9a1bb9\",
    \"issuing_organization_id\": \"977e5426-8d13-3824-86aa-b092f8ae52c5\",
    \"permit_type_id\": \"d6fa562b-acd5-35ff-babb-d11194d3737b\",
    \"scope_en\": \"d\",
    \"scope_ar\": \"l\",
    \"location_description_en\": \"j\",
    \"location_description_ar\": \"n\",
    \"valid_from\": \"2026-05-07T09:16:53\",
    \"valid_until\": \"2052-05-30\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "project_id": "6ff8f7f6-1eb3-3525-be4a-3932c805afed",
    "site_id": "6b72fe4a-5b40-307c-bc24-f79acf9a1bb9",
    "issuing_organization_id": "977e5426-8d13-3824-86aa-b092f8ae52c5",
    "permit_type_id": "d6fa562b-acd5-35ff-babb-d11194d3737b",
    "scope_en": "d",
    "scope_ar": "l",
    "location_description_en": "j",
    "location_description_ar": "n",
    "valid_from": "2026-05-07T09:16:53",
    "valid_until": "2052-05-30"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-permits">
</span>
<span id="execution-results-POSTapi-v1-permits" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-permits"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-permits"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-permits" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-permits">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-permits" data-method="POST"
      data-path="api/v1/permits"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-permits', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-permits"
                    onclick="tryItOut('POSTapi-v1-permits');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-permits"
                    onclick="cancelTryOut('POSTapi-v1-permits');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-permits"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/permits</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-permits"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-permits"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-permits"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="project_id"                data-endpoint="POSTapi-v1-permits"
               value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the projects table. Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>site_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="site_id"                data-endpoint="POSTapi-v1-permits"
               value="6b72fe4a-5b40-307c-bc24-f79acf9a1bb9"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the sites table. Example: <code>6b72fe4a-5b40-307c-bc24-f79acf9a1bb9</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>issuing_organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="issuing_organization_id"                data-endpoint="POSTapi-v1-permits"
               value="977e5426-8d13-3824-86aa-b092f8ae52c5"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the organizations table. Example: <code>977e5426-8d13-3824-86aa-b092f8ae52c5</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>permit_type_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permit_type_id"                data-endpoint="POSTapi-v1-permits"
               value="d6fa562b-acd5-35ff-babb-d11194d3737b"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the permit_types table. Example: <code>d6fa562b-acd5-35ff-babb-d11194d3737b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>scope_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="scope_en"                data-endpoint="POSTapi-v1-permits"
               value="d"
               data-component="body">
    <br>
<p>Must not be greater than 2000 characters. Example: <code>d</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>scope_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="scope_ar"                data-endpoint="POSTapi-v1-permits"
               value="l"
               data-component="body">
    <br>
<p>Must not be greater than 2000 characters. Example: <code>l</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>location_description_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="location_description_en"                data-endpoint="POSTapi-v1-permits"
               value="j"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>j</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>location_description_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="location_description_ar"                data-endpoint="POSTapi-v1-permits"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>valid_from</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="valid_from"                data-endpoint="POSTapi-v1-permits"
               value="2026-05-07T09:16:53"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-07T09:16:53</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>valid_until</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="valid_until"                data-endpoint="POSTapi-v1-permits"
               value="2052-05-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after <code>valid_from</code>. Example: <code>2052-05-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>metadata</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="metadata"                data-endpoint="POSTapi-v1-permits"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="permits-GETapi-v1-permits--id-">GET api/v1/permits/{id}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-permits--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/permits/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-permits--id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: e3276fec-2e61-4f8a-bf31-0c3bca6dd405
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;e3276fec-2e61-4f8a-bf31-0c3bca6dd405&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-permits--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-permits--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-permits--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-permits--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-permits--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-permits--id-" data-method="GET"
      data-path="api/v1/permits/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-permits--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-permits--id-"
                    onclick="tryItOut('GETapi-v1-permits--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-permits--id-"
                    onclick="cancelTryOut('GETapi-v1-permits--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-permits--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/permits/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-permits--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-permits--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-permits--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-v1-permits--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the permit. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="permits-POSTapi-v1-permits--permit_id--workers">Attach workers to a draft permit. Either by ID list or by helmet/coverall
QR tokens (or both). Workers can be attached only while the permit is
in draft state.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-permits--permit_id--workers">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/permits/architecto/workers" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"tokens\": [
        \"b\"
    ],
    \"workers\": [
        {
            \"id\": \"a4855dc5-0acb-33c3-b921-f4291f719ca0\",
            \"role_on_permit\": \"worker\"
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits/architecto/workers"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "tokens": [
        "b"
    ],
    "workers": [
        {
            "id": "a4855dc5-0acb-33c3-b921-f4291f719ca0",
            "role_on_permit": "worker"
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-permits--permit_id--workers">
</span>
<span id="execution-results-POSTapi-v1-permits--permit_id--workers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-permits--permit_id--workers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-permits--permit_id--workers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-permits--permit_id--workers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-permits--permit_id--workers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-permits--permit_id--workers" data-method="POST"
      data-path="api/v1/permits/{permit_id}/workers"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-permits--permit_id--workers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-permits--permit_id--workers"
                    onclick="tryItOut('POSTapi-v1-permits--permit_id--workers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-permits--permit_id--workers"
                    onclick="cancelTryOut('POSTapi-v1-permits--permit_id--workers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-permits--permit_id--workers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/permits/{permit_id}/workers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-permits--permit_id--workers"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-permits--permit_id--workers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-permits--permit_id--workers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>permit_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permit_id"                data-endpoint="POSTapi-v1-permits--permit_id--workers"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the permit. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>workers</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
<br>
<p>This field is required when <code>tokens</code> is not present. Must have at least 1 items.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="workers.0.id"                data-endpoint="POSTapi-v1-permits--permit_id--workers"
               value="a4855dc5-0acb-33c3-b921-f4291f719ca0"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the workers table. Example: <code>a4855dc5-0acb-33c3-b921-f4291f719ca0</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>role_on_permit</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="workers.0.role_on_permit"                data-endpoint="POSTapi-v1-permits--permit_id--workers"
               value="worker"
               data-component="body">
    <br>
<p>Example: <code>worker</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>worker</code></li> <li><code>supervisor</code></li> <li><code>gas_tester</code></li> <li><code>fire_watch</code></li></ul>
                    </div>
                                    </details>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>tokens</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tokens[0]"                data-endpoint="POSTapi-v1-permits--permit_id--workers"
               data-component="body">
        <input type="text" style="display: none"
               name="tokens[1]"                data-endpoint="POSTapi-v1-permits--permit_id--workers"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters.</p>
        </div>
        </form>

                    <h2 id="permits-POSTapi-v1-permits--permit_id--equipment">Attach equipment to a draft permit. Either by ID list or QR tokens.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-permits--permit_id--equipment">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/permits/architecto/equipment" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"equipment_ids\": [
        \"6ff8f7f6-1eb3-3525-be4a-3932c805afed\"
    ],
    \"tokens\": [
        \"g\"
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits/architecto/equipment"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "equipment_ids": [
        "6ff8f7f6-1eb3-3525-be4a-3932c805afed"
    ],
    "tokens": [
        "g"
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-permits--permit_id--equipment">
</span>
<span id="execution-results-POSTapi-v1-permits--permit_id--equipment" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-permits--permit_id--equipment"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-permits--permit_id--equipment"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-permits--permit_id--equipment" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-permits--permit_id--equipment">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-permits--permit_id--equipment" data-method="POST"
      data-path="api/v1/permits/{permit_id}/equipment"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-permits--permit_id--equipment', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-permits--permit_id--equipment"
                    onclick="tryItOut('POSTapi-v1-permits--permit_id--equipment');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-permits--permit_id--equipment"
                    onclick="cancelTryOut('POSTapi-v1-permits--permit_id--equipment');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-permits--permit_id--equipment"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/permits/{permit_id}/equipment</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-permits--permit_id--equipment"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-permits--permit_id--equipment"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-permits--permit_id--equipment"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>permit_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permit_id"                data-endpoint="POSTapi-v1-permits--permit_id--equipment"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the permit. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>equipment_ids</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="equipment_ids[0]"                data-endpoint="POSTapi-v1-permits--permit_id--equipment"
               data-component="body">
        <input type="text" style="display: none"
               name="equipment_ids[1]"                data-endpoint="POSTapi-v1-permits--permit_id--equipment"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the equipment table.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>tokens</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tokens[0]"                data-endpoint="POSTapi-v1-permits--permit_id--equipment"
               data-component="body">
        <input type="text" style="display: none"
               name="tokens[1]"                data-endpoint="POSTapi-v1-permits--permit_id--equipment"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters.</p>
        </div>
        </form>

                <h1 id="permits-lifecycle">Permits — Lifecycle</h1>

    <p>Submit / approve / reject / close transitions, plus lifecycle history.
Each transition validates current status and writes an immutable PermitEvent.</p>

                                <h2 id="permits-lifecycle-POSTapi-v1-permits--permit_id--submit">Submit a draft permit. Re-runs all validation against named workers and
equipment. On failure returns 422 PERMIT_VALIDATION_FAILED with a full
per-worker / per-equipment breakdown so the UI can surface exactly which
worker has which expired cert.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-permits--permit_id--submit">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/permits/architecto/submit" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits/architecto/submit"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-permits--permit_id--submit">
</span>
<span id="execution-results-POSTapi-v1-permits--permit_id--submit" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-permits--permit_id--submit"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-permits--permit_id--submit"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-permits--permit_id--submit" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-permits--permit_id--submit">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-permits--permit_id--submit" data-method="POST"
      data-path="api/v1/permits/{permit_id}/submit"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-permits--permit_id--submit', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-permits--permit_id--submit"
                    onclick="tryItOut('POSTapi-v1-permits--permit_id--submit');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-permits--permit_id--submit"
                    onclick="cancelTryOut('POSTapi-v1-permits--permit_id--submit');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-permits--permit_id--submit"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/permits/{permit_id}/submit</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-permits--permit_id--submit"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-permits--permit_id--submit"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-permits--permit_id--submit"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>permit_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permit_id"                data-endpoint="POSTapi-v1-permits--permit_id--submit"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the permit. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="permits-lifecycle-POSTapi-v1-permits--permit_id--approve">Approve a submitted permit (consultant role).</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-permits--permit_id--approve">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/permits/architecto/approve" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"comment\": \"b\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits/architecto/approve"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "comment": "b"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-permits--permit_id--approve">
</span>
<span id="execution-results-POSTapi-v1-permits--permit_id--approve" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-permits--permit_id--approve"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-permits--permit_id--approve"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-permits--permit_id--approve" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-permits--permit_id--approve">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-permits--permit_id--approve" data-method="POST"
      data-path="api/v1/permits/{permit_id}/approve"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-permits--permit_id--approve', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-permits--permit_id--approve"
                    onclick="tryItOut('POSTapi-v1-permits--permit_id--approve');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-permits--permit_id--approve"
                    onclick="cancelTryOut('POSTapi-v1-permits--permit_id--approve');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-permits--permit_id--approve"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/permits/{permit_id}/approve</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-permits--permit_id--approve"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-permits--permit_id--approve"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-permits--permit_id--approve"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>permit_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permit_id"                data-endpoint="POSTapi-v1-permits--permit_id--approve"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the permit. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>comment</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="comment"                data-endpoint="POSTapi-v1-permits--permit_id--approve"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 2000 characters. Example: <code>b</code></p>
        </div>
        </form>

                    <h2 id="permits-lifecycle-POSTapi-v1-permits--permit_id--reject">Reject a submitted permit with mandatory reason.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-permits--permit_id--reject">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/permits/architecto/reject" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"reason\": \"b\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits/architecto/reject"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "reason": "b"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-permits--permit_id--reject">
</span>
<span id="execution-results-POSTapi-v1-permits--permit_id--reject" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-permits--permit_id--reject"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-permits--permit_id--reject"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-permits--permit_id--reject" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-permits--permit_id--reject">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-permits--permit_id--reject" data-method="POST"
      data-path="api/v1/permits/{permit_id}/reject"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-permits--permit_id--reject', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-permits--permit_id--reject"
                    onclick="tryItOut('POSTapi-v1-permits--permit_id--reject');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-permits--permit_id--reject"
                    onclick="cancelTryOut('POSTapi-v1-permits--permit_id--reject');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-permits--permit_id--reject"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/permits/{permit_id}/reject</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-permits--permit_id--reject"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-permits--permit_id--reject"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-permits--permit_id--reject"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>permit_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permit_id"                data-endpoint="POSTapi-v1-permits--permit_id--reject"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the permit. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="reason"                data-endpoint="POSTapi-v1-permits--permit_id--reject"
               value="b"
               data-component="body">
    <br>
<p>Must be at least 5 characters. Must not be greater than 2000 characters. Example: <code>b</code></p>
        </div>
        </form>

                    <h2 id="permits-lifecycle-POSTapi-v1-permits--permit_id--close">Close an approved permit once work is complete.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-permits--permit_id--close">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/permits/architecto/close" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"closure_notes\": \"b\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits/architecto/close"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "closure_notes": "b"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-permits--permit_id--close">
</span>
<span id="execution-results-POSTapi-v1-permits--permit_id--close" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-permits--permit_id--close"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-permits--permit_id--close"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-permits--permit_id--close" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-permits--permit_id--close">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-permits--permit_id--close" data-method="POST"
      data-path="api/v1/permits/{permit_id}/close"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-permits--permit_id--close', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-permits--permit_id--close"
                    onclick="tryItOut('POSTapi-v1-permits--permit_id--close');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-permits--permit_id--close"
                    onclick="cancelTryOut('POSTapi-v1-permits--permit_id--close');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-permits--permit_id--close"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/permits/{permit_id}/close</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-permits--permit_id--close"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-permits--permit_id--close"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-permits--permit_id--close"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>permit_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permit_id"                data-endpoint="POSTapi-v1-permits--permit_id--close"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the permit. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>closure_notes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="closure_notes"                data-endpoint="POSTapi-v1-permits--permit_id--close"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 2000 characters. Example: <code>b</code></p>
        </div>
        </form>

                    <h2 id="permits-lifecycle-GETapi-v1-permits--permit_id--events">Lifecycle history.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-permits--permit_id--events">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/permits/architecto/events" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/permits/architecto/events"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-permits--permit_id--events">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: d1cb7aa2-ef84-4243-89a8-74e48d935853
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;d1cb7aa2-ef84-4243-89a8-74e48d935853&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-permits--permit_id--events" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-permits--permit_id--events"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-permits--permit_id--events"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-permits--permit_id--events" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-permits--permit_id--events">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-permits--permit_id--events" data-method="GET"
      data-path="api/v1/permits/{permit_id}/events"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-permits--permit_id--events', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-permits--permit_id--events"
                    onclick="tryItOut('GETapi-v1-permits--permit_id--events');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-permits--permit_id--events"
                    onclick="cancelTryOut('GETapi-v1-permits--permit_id--events');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-permits--permit_id--events"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/permits/{permit_id}/events</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-permits--permit_id--events"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-permits--permit_id--events"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-permits--permit_id--events"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>permit_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permit_id"                data-endpoint="GETapi-v1-permits--permit_id--events"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the permit. Example: <code>architecto</code></p>
            </div>
                    </form>

                <h1 id="scan-verification">Scan Verification</h1>

    <p>The gate-scan flow. Each verify call writes one row to scan_events for the
audit trail; the response payload is what the client UI renders directly.</p>

                                <h2 id="scan-verification-GETapi-v1-scans">List scan events.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-scans">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/scans" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/scans"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-scans">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 1023911d-31e5-44c3-845b-a751a3ee2f55
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;1023911d-31e5-44c3-845b-a751a3ee2f55&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-scans" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-scans"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-scans"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-scans" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-scans">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-scans" data-method="GET"
      data-path="api/v1/scans"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-scans', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-scans"
                    onclick="tryItOut('GETapi-v1-scans');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-scans"
                    onclick="cancelTryOut('GETapi-v1-scans');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-scans"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/scans</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-scans"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-scans"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-scans"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="scan-verification-POSTapi-v1-scans-verify">Verify a single scan (helmet, coverall, equipment, or manual employee_id).</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-scans-verify">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/scans/verify" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"token\": \"b\",
    \"employee_id\": \"n\",
    \"project_id\": \"6b72fe4a-5b40-307c-bc24-f79acf9a1bb9\",
    \"site_id\": \"977e5426-8d13-3824-86aa-b092f8ae52c5\",
    \"client_app\": \"mobile_android\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/scans/verify"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "token": "b",
    "employee_id": "n",
    "project_id": "6b72fe4a-5b40-307c-bc24-f79acf9a1bb9",
    "site_id": "977e5426-8d13-3824-86aa-b092f8ae52c5",
    "client_app": "mobile_android"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-scans-verify">
</span>
<span id="execution-results-POSTapi-v1-scans-verify" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-scans-verify"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-scans-verify"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-scans-verify" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-scans-verify">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-scans-verify" data-method="POST"
      data-path="api/v1/scans/verify"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-scans-verify', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-scans-verify"
                    onclick="tryItOut('POSTapi-v1-scans-verify');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-scans-verify"
                    onclick="cancelTryOut('POSTapi-v1-scans-verify');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-scans-verify"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/scans/verify</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-scans-verify"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-scans-verify"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-scans-verify"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTapi-v1-scans-verify"
               value="b"
               data-component="body">
    <br>
<p>This field is required when <code>employee_id</code> is not present. Must not be greater than 255 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>employee_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="employee_id"                data-endpoint="POSTapi-v1-scans-verify"
               value="n"
               data-component="body">
    <br>
<p>This field is required when <code>token</code> is not present. Must not be greater than 64 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="project_id"                data-endpoint="POSTapi-v1-scans-verify"
               value="6b72fe4a-5b40-307c-bc24-f79acf9a1bb9"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the projects table. Example: <code>6b72fe4a-5b40-307c-bc24-f79acf9a1bb9</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>site_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="site_id"                data-endpoint="POSTapi-v1-scans-verify"
               value="977e5426-8d13-3824-86aa-b092f8ae52c5"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the sites table. Example: <code>977e5426-8d13-3824-86aa-b092f8ae52c5</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>client_app</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="client_app"                data-endpoint="POSTapi-v1-scans-verify"
               value="mobile_android"
               data-component="body">
    <br>
<p>Example: <code>mobile_android</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>web</code></li> <li><code>mobile_ios</code></li> <li><code>mobile_android</code></li> <li><code>api</code></li></ul>
        </div>
        </form>

                    <h2 id="scan-verification-POSTapi-v1-scans-verify-pair">Helmet + coverall cross-check. Two QRs in sequence; if they don&#039;t
resolve to the same worker the result is IMPERSONATION_FLAG.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-scans-verify-pair">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/scans/verify-pair" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"helmet_token\": \"b\",
    \"coverall_token\": \"n\",
    \"project_id\": \"6b72fe4a-5b40-307c-bc24-f79acf9a1bb9\",
    \"site_id\": \"977e5426-8d13-3824-86aa-b092f8ae52c5\",
    \"client_app\": \"api\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/scans/verify-pair"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "helmet_token": "b",
    "coverall_token": "n",
    "project_id": "6b72fe4a-5b40-307c-bc24-f79acf9a1bb9",
    "site_id": "977e5426-8d13-3824-86aa-b092f8ae52c5",
    "client_app": "api"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-scans-verify-pair">
</span>
<span id="execution-results-POSTapi-v1-scans-verify-pair" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-scans-verify-pair"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-scans-verify-pair"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-scans-verify-pair" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-scans-verify-pair">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-scans-verify-pair" data-method="POST"
      data-path="api/v1/scans/verify-pair"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-scans-verify-pair', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-scans-verify-pair"
                    onclick="tryItOut('POSTapi-v1-scans-verify-pair');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-scans-verify-pair"
                    onclick="cancelTryOut('POSTapi-v1-scans-verify-pair');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-scans-verify-pair"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/scans/verify-pair</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-scans-verify-pair"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-scans-verify-pair"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-scans-verify-pair"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>helmet_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="helmet_token"                data-endpoint="POSTapi-v1-scans-verify-pair"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>coverall_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="coverall_token"                data-endpoint="POSTapi-v1-scans-verify-pair"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="project_id"                data-endpoint="POSTapi-v1-scans-verify-pair"
               value="6b72fe4a-5b40-307c-bc24-f79acf9a1bb9"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the projects table. Example: <code>6b72fe4a-5b40-307c-bc24-f79acf9a1bb9</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>site_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="site_id"                data-endpoint="POSTapi-v1-scans-verify-pair"
               value="977e5426-8d13-3824-86aa-b092f8ae52c5"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the sites table. Example: <code>977e5426-8d13-3824-86aa-b092f8ae52c5</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>client_app</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="client_app"                data-endpoint="POSTapi-v1-scans-verify-pair"
               value="api"
               data-component="body">
    <br>
<p>Example: <code>api</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>web</code></li> <li><code>mobile_ios</code></li> <li><code>mobile_android</code></li> <li><code>api</code></li></ul>
        </div>
        </form>

                    <h2 id="scan-verification-POSTapi-v1-scans-verify-equipment-operator">Equipment + paired operator. Verifies equipment TPI AND that the worker
is on the equipment&#039;s authorized operators list.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-scans-verify-equipment-operator">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/scans/verify-equipment-operator" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"equipment_token\": \"b\",
    \"worker_token\": \"n\",
    \"project_id\": \"6b72fe4a-5b40-307c-bc24-f79acf9a1bb9\",
    \"site_id\": \"977e5426-8d13-3824-86aa-b092f8ae52c5\",
    \"client_app\": \"mobile_android\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/scans/verify-equipment-operator"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "equipment_token": "b",
    "worker_token": "n",
    "project_id": "6b72fe4a-5b40-307c-bc24-f79acf9a1bb9",
    "site_id": "977e5426-8d13-3824-86aa-b092f8ae52c5",
    "client_app": "mobile_android"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-scans-verify-equipment-operator">
</span>
<span id="execution-results-POSTapi-v1-scans-verify-equipment-operator" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-scans-verify-equipment-operator"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-scans-verify-equipment-operator"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-scans-verify-equipment-operator" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-scans-verify-equipment-operator">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-scans-verify-equipment-operator" data-method="POST"
      data-path="api/v1/scans/verify-equipment-operator"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-scans-verify-equipment-operator', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-scans-verify-equipment-operator"
                    onclick="tryItOut('POSTapi-v1-scans-verify-equipment-operator');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-scans-verify-equipment-operator"
                    onclick="cancelTryOut('POSTapi-v1-scans-verify-equipment-operator');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-scans-verify-equipment-operator"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/scans/verify-equipment-operator</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-scans-verify-equipment-operator"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-scans-verify-equipment-operator"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-scans-verify-equipment-operator"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>equipment_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="equipment_token"                data-endpoint="POSTapi-v1-scans-verify-equipment-operator"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>worker_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_token"                data-endpoint="POSTapi-v1-scans-verify-equipment-operator"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="project_id"                data-endpoint="POSTapi-v1-scans-verify-equipment-operator"
               value="6b72fe4a-5b40-307c-bc24-f79acf9a1bb9"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the projects table. Example: <code>6b72fe4a-5b40-307c-bc24-f79acf9a1bb9</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>site_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="site_id"                data-endpoint="POSTapi-v1-scans-verify-equipment-operator"
               value="977e5426-8d13-3824-86aa-b092f8ae52c5"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the sites table. Example: <code>977e5426-8d13-3824-86aa-b092f8ae52c5</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>client_app</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="client_app"                data-endpoint="POSTapi-v1-scans-verify-equipment-operator"
               value="mobile_android"
               data-component="body">
    <br>
<p>Example: <code>mobile_android</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>web</code></li> <li><code>mobile_ios</code></li> <li><code>mobile_android</code></li> <li><code>api</code></li></ul>
        </div>
        </form>

                <h1 id="webhooks">Webhooks</h1>

    <p>Webhook subscriptions for event delivery to ERP integrations / customer
systems. The HMAC secret is returned ONCE at creation time; never echoed
again.</p>

                                <h2 id="webhooks-GETapi-v1-webhooks">GET api/v1/webhooks</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-webhooks">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/webhooks" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/webhooks"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-webhooks">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 1cff4a86-f07e-4131-9d0b-01baa750c5ac
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;1cff4a86-f07e-4131-9d0b-01baa750c5ac&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-webhooks" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-webhooks"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-webhooks"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-webhooks" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-webhooks">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-webhooks" data-method="GET"
      data-path="api/v1/webhooks"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-webhooks', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-webhooks"
                    onclick="tryItOut('GETapi-v1-webhooks');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-webhooks"
                    onclick="cancelTryOut('GETapi-v1-webhooks');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-webhooks"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/webhooks</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-webhooks"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-webhooks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-webhooks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="webhooks-POSTapi-v1-webhooks">Create a webhook subscription. Response includes the secret ONCE; store
it client-side immediately, the secret is never returned again.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-webhooks">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/webhooks" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"owner_organization_id\": \"6ff8f7f6-1eb3-3525-be4a-3932c805afed\",
    \"label\": \"g\",
    \"url\": \"http:\\/\\/www.okuneva.com\\/fugiat-sunt-nihil-accusantium-harum-mollitia.html\",
    \"events\": [
        \"permit.validated\"
    ],
    \"headers\": [
        \"k\"
    ],
    \"active\": true
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/webhooks"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "owner_organization_id": "6ff8f7f6-1eb3-3525-be4a-3932c805afed",
    "label": "g",
    "url": "http:\/\/www.okuneva.com\/fugiat-sunt-nihil-accusantium-harum-mollitia.html",
    "events": [
        "permit.validated"
    ],
    "headers": [
        "k"
    ],
    "active": true
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-webhooks">
</span>
<span id="execution-results-POSTapi-v1-webhooks" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-webhooks"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-webhooks"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-webhooks" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-webhooks">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-webhooks" data-method="POST"
      data-path="api/v1/webhooks"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-webhooks', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-webhooks"
                    onclick="tryItOut('POSTapi-v1-webhooks');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-webhooks"
                    onclick="cancelTryOut('POSTapi-v1-webhooks');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-webhooks"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/webhooks</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-webhooks"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-webhooks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-webhooks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>owner_organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="owner_organization_id"                data-endpoint="POSTapi-v1-webhooks"
               value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the organizations table. Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>label</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="label"                data-endpoint="POSTapi-v1-webhooks"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>url</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="url"                data-endpoint="POSTapi-v1-webhooks"
               value="http://www.okuneva.com/fugiat-sunt-nihil-accusantium-harum-mollitia.html"
               data-component="body">
    <br>
<p>Must be a valid URL. Must not be greater than 512 characters. Example: <code>http://www.okuneva.com/fugiat-sunt-nihil-accusantium-harum-mollitia.html</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>events</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="events[0]"                data-endpoint="POSTapi-v1-webhooks"
               data-component="body">
        <input type="text" style="display: none"
               name="events[1]"                data-endpoint="POSTapi-v1-webhooks"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>scan.green</code></li> <li><code>scan.red</code></li> <li><code>scan.impersonation_flag</code></li> <li><code>permit.created</code></li> <li><code>permit.submitted</code></li> <li><code>permit.validated</code></li> <li><code>permit.approved</code></li> <li><code>permit.rejected</code></li> <li><code>permit.closed</code></li> <li><code>hazard_report.submitted</code></li> <li><code>hazard_report.status_changed</code></li> <li><code>hazard_report.resolved</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>headers</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="headers[0]"                data-endpoint="POSTapi-v1-webhooks"
               data-component="body">
        <input type="text" style="display: none"
               name="headers[1]"                data-endpoint="POSTapi-v1-webhooks"
               data-component="body">
    <br>
<p>Must not be greater than 512 characters.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>active</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-webhooks" style="display: none">
            <input type="radio" name="active"
                   value="true"
                   data-endpoint="POSTapi-v1-webhooks"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-webhooks" style="display: none">
            <input type="radio" name="active"
                   value="false"
                   data-endpoint="POSTapi-v1-webhooks"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>true</code></p>
        </div>
        </form>

                    <h2 id="webhooks-GETapi-v1-webhooks--webhookSubscription_id-">GET api/v1/webhooks/{webhookSubscription_id}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-webhooks--webhookSubscription_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/webhooks/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/webhooks/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-webhooks--webhookSubscription_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 1038b9ac-2474-425f-ae74-ec807d8ab9ee
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;1038b9ac-2474-425f-ae74-ec807d8ab9ee&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-webhooks--webhookSubscription_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-webhooks--webhookSubscription_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-webhooks--webhookSubscription_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-webhooks--webhookSubscription_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-webhooks--webhookSubscription_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-webhooks--webhookSubscription_id-" data-method="GET"
      data-path="api/v1/webhooks/{webhookSubscription_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-webhooks--webhookSubscription_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-webhooks--webhookSubscription_id-"
                    onclick="tryItOut('GETapi-v1-webhooks--webhookSubscription_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-webhooks--webhookSubscription_id-"
                    onclick="cancelTryOut('GETapi-v1-webhooks--webhookSubscription_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-webhooks--webhookSubscription_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/webhooks/{webhookSubscription_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-webhooks--webhookSubscription_id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-webhooks--webhookSubscription_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-webhooks--webhookSubscription_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>webhookSubscription_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="webhookSubscription_id"                data-endpoint="GETapi-v1-webhooks--webhookSubscription_id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the webhookSubscription. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="webhooks-PATCHapi-v1-webhooks--webhookSubscription_id-">PATCH api/v1/webhooks/{webhookSubscription_id}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PATCHapi-v1-webhooks--webhookSubscription_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/webhooks/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"label\": \"b\",
    \"url\": \"http:\\/\\/bailey.com\\/\",
    \"events\": [
        \"permit.created\"
    ],
    \"active\": false
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/webhooks/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "label": "b",
    "url": "http:\/\/bailey.com\/",
    "events": [
        "permit.created"
    ],
    "active": false
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-webhooks--webhookSubscription_id-">
</span>
<span id="execution-results-PATCHapi-v1-webhooks--webhookSubscription_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-webhooks--webhookSubscription_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-webhooks--webhookSubscription_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-webhooks--webhookSubscription_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-webhooks--webhookSubscription_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-webhooks--webhookSubscription_id-" data-method="PATCH"
      data-path="api/v1/webhooks/{webhookSubscription_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-webhooks--webhookSubscription_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-webhooks--webhookSubscription_id-"
                    onclick="tryItOut('PATCHapi-v1-webhooks--webhookSubscription_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-webhooks--webhookSubscription_id-"
                    onclick="cancelTryOut('PATCHapi-v1-webhooks--webhookSubscription_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-webhooks--webhookSubscription_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/webhooks/{webhookSubscription_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>webhookSubscription_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="webhookSubscription_id"                data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the webhookSubscription. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>label</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="label"                data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>url</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="url"                data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               value="http://bailey.com/"
               data-component="body">
    <br>
<p>Must be a valid URL. Must not be greater than 512 characters. Example: <code>http://bailey.com/</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>events</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="events[0]"                data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               data-component="body">
        <input type="text" style="display: none"
               name="events[1]"                data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>scan.green</code></li> <li><code>scan.red</code></li> <li><code>scan.impersonation_flag</code></li> <li><code>permit.created</code></li> <li><code>permit.submitted</code></li> <li><code>permit.validated</code></li> <li><code>permit.approved</code></li> <li><code>permit.rejected</code></li> <li><code>permit.closed</code></li> <li><code>hazard_report.submitted</code></li> <li><code>hazard_report.status_changed</code></li> <li><code>hazard_report.resolved</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>headers</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="headers"                data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
               value=""
               data-component="body">
    <br>
<p>Must not have more than 10 items.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>active</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-" style="display: none">
            <input type="radio" name="active"
                   value="true"
                   data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-" style="display: none">
            <input type="radio" name="active"
                   value="false"
                   data-endpoint="PATCHapi-v1-webhooks--webhookSubscription_id-"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>false</code></p>
        </div>
        </form>

                    <h2 id="webhooks-DELETEapi-v1-webhooks--webhookSubscription_id-">DELETE api/v1/webhooks/{webhookSubscription_id}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-webhooks--webhookSubscription_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/webhooks/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/webhooks/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-webhooks--webhookSubscription_id-">
</span>
<span id="execution-results-DELETEapi-v1-webhooks--webhookSubscription_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-webhooks--webhookSubscription_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-webhooks--webhookSubscription_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-webhooks--webhookSubscription_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-webhooks--webhookSubscription_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-webhooks--webhookSubscription_id-" data-method="DELETE"
      data-path="api/v1/webhooks/{webhookSubscription_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-webhooks--webhookSubscription_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-webhooks--webhookSubscription_id-"
                    onclick="tryItOut('DELETEapi-v1-webhooks--webhookSubscription_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-webhooks--webhookSubscription_id-"
                    onclick="cancelTryOut('DELETEapi-v1-webhooks--webhookSubscription_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-webhooks--webhookSubscription_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/webhooks/{webhookSubscription_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-v1-webhooks--webhookSubscription_id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-webhooks--webhookSubscription_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-webhooks--webhookSubscription_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>webhookSubscription_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="webhookSubscription_id"                data-endpoint="DELETEapi-v1-webhooks--webhookSubscription_id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the webhookSubscription. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="webhooks-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret">Rotate the HMAC secret. New secret returned ONCE in this response.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/webhooks/architecto/rotate-secret" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/webhooks/architecto/rotate-secret"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret">
</span>
<span id="execution-results-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret" data-method="POST"
      data-path="api/v1/webhooks/{webhookSubscription_id}/rotate-secret"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"
                    onclick="tryItOut('POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"
                    onclick="cancelTryOut('POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/webhooks/{webhookSubscription_id}/rotate-secret</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>webhookSubscription_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="webhookSubscription_id"                data-endpoint="POSTapi-v1-webhooks--webhookSubscription_id--rotate-secret"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the webhookSubscription. Example: <code>architecto</code></p>
            </div>
                    </form>

                <h1 id="worker-certifications">Worker Certifications</h1>

    <p>Manage a worker's certifications (NEBOSH, IOSH, scaffolding, etc.). The
status (valid / expiring_soon / expired) is computed dynamically from
expiry_date relative to today.</p>

                                <h2 id="worker-certifications-GETapi-v1-workers--worker_id--certifications">GET api/v1/workers/{worker_id}/certifications</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-workers--worker_id--certifications">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/workers/architecto/certifications" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto/certifications"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-workers--worker_id--certifications">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 12784297-b20e-4bda-a821-cce4941c8452
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;12784297-b20e-4bda-a821-cce4941c8452&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-workers--worker_id--certifications" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-workers--worker_id--certifications"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-workers--worker_id--certifications"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-workers--worker_id--certifications" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-workers--worker_id--certifications">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-workers--worker_id--certifications" data-method="GET"
      data-path="api/v1/workers/{worker_id}/certifications"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-workers--worker_id--certifications', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-workers--worker_id--certifications"
                    onclick="tryItOut('GETapi-v1-workers--worker_id--certifications');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-workers--worker_id--certifications"
                    onclick="cancelTryOut('GETapi-v1-workers--worker_id--certifications');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-workers--worker_id--certifications"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/workers/{worker_id}/certifications</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-workers--worker_id--certifications"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-workers--worker_id--certifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-workers--worker_id--certifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="GETapi-v1-workers--worker_id--certifications"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="worker-certifications-POSTapi-v1-workers--worker_id--certifications">Attach a certification to a worker.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-workers--worker_id--certifications">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/workers/architecto/certifications" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"certification_type_id\": \"6ff8f7f6-1eb3-3525-be4a-3932c805afed\",
    \"certificate_number\": \"g\",
    \"issuing_body_en\": \"z\",
    \"issuing_body_ar\": \"m\",
    \"issue_date\": \"2022-05-31\",
    \"expiry_date\": \"2052-05-30\",
    \"verified\": false
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto/certifications"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "certification_type_id": "6ff8f7f6-1eb3-3525-be4a-3932c805afed",
    "certificate_number": "g",
    "issuing_body_en": "z",
    "issuing_body_ar": "m",
    "issue_date": "2022-05-31",
    "expiry_date": "2052-05-30",
    "verified": false
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-workers--worker_id--certifications">
</span>
<span id="execution-results-POSTapi-v1-workers--worker_id--certifications" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-workers--worker_id--certifications"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-workers--worker_id--certifications"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-workers--worker_id--certifications" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-workers--worker_id--certifications">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-workers--worker_id--certifications" data-method="POST"
      data-path="api/v1/workers/{worker_id}/certifications"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-workers--worker_id--certifications', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-workers--worker_id--certifications"
                    onclick="tryItOut('POSTapi-v1-workers--worker_id--certifications');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-workers--worker_id--certifications"
                    onclick="cancelTryOut('POSTapi-v1-workers--worker_id--certifications');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-workers--worker_id--certifications"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/workers/{worker_id}/certifications</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>certification_type_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="certification_type_id"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the certification_types table. Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>certificate_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="certificate_number"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>issuing_body_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="issuing_body_en"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="z"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>z</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>issuing_body_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="issuing_body_ar"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="m"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>m</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>issue_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="issue_date"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="2022-05-31"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date before or equal to <code>today</code>. Example: <code>2022-05-31</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>expiry_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="expiry_date"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value="2052-05-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after <code>issue_date</code>. Example: <code>2052-05-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>verified</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-workers--worker_id--certifications" style="display: none">
            <input type="radio" name="verified"
                   value="true"
                   data-endpoint="POSTapi-v1-workers--worker_id--certifications"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-workers--worker_id--certifications" style="display: none">
            <input type="radio" name="verified"
                   value="false"
                   data-endpoint="POSTapi-v1-workers--worker_id--certifications"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>false</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>metadata</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="metadata"                data-endpoint="POSTapi-v1-workers--worker_id--certifications"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="worker-certifications-DELETEapi-v1-workers--worker_id--certifications--certificationId-">DELETE api/v1/workers/{worker_id}/certifications/{certificationId}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-workers--worker_id--certifications--certificationId-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/workers/architecto/certifications/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto/certifications/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-workers--worker_id--certifications--certificationId-">
</span>
<span id="execution-results-DELETEapi-v1-workers--worker_id--certifications--certificationId-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-workers--worker_id--certifications--certificationId-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-workers--worker_id--certifications--certificationId-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-workers--worker_id--certifications--certificationId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-workers--worker_id--certifications--certificationId-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-workers--worker_id--certifications--certificationId-" data-method="DELETE"
      data-path="api/v1/workers/{worker_id}/certifications/{certificationId}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-workers--worker_id--certifications--certificationId-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-workers--worker_id--certifications--certificationId-"
                    onclick="tryItOut('DELETEapi-v1-workers--worker_id--certifications--certificationId-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-workers--worker_id--certifications--certificationId-"
                    onclick="cancelTryOut('DELETEapi-v1-workers--worker_id--certifications--certificationId-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-workers--worker_id--certifications--certificationId-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/workers/{worker_id}/certifications/{certificationId}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-v1-workers--worker_id--certifications--certificationId-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-workers--worker_id--certifications--certificationId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-workers--worker_id--certifications--certificationId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="DELETEapi-v1-workers--worker_id--certifications--certificationId-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>certificationId</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="certificationId"                data-endpoint="DELETEapi-v1-workers--worker_id--certifications--certificationId-"
               value="architecto"
               data-component="url">
    <br>
<p>Example: <code>architecto</code></p>
            </div>
                    </form>

                <h1 id="worker-medical-records">Worker Medical Records</h1>

    <p>Medical fitness history. The most recent record (by exam_date) drives the
MEDICAL_FAIL reason at gate scan. Historical records are retained for audit.</p>

                                <h2 id="worker-medical-records-GETapi-v1-workers--worker_id--medical-records">GET api/v1/workers/{worker_id}/medical-records</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-workers--worker_id--medical-records">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/workers/architecto/medical-records" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto/medical-records"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-workers--worker_id--medical-records">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: a4cfd4ba-acca-4c10-b611-8ae350859b1a
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;a4cfd4ba-acca-4c10-b611-8ae350859b1a&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-workers--worker_id--medical-records" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-workers--worker_id--medical-records"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-workers--worker_id--medical-records"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-workers--worker_id--medical-records" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-workers--worker_id--medical-records">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-workers--worker_id--medical-records" data-method="GET"
      data-path="api/v1/workers/{worker_id}/medical-records"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-workers--worker_id--medical-records', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-workers--worker_id--medical-records"
                    onclick="tryItOut('GETapi-v1-workers--worker_id--medical-records');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-workers--worker_id--medical-records"
                    onclick="cancelTryOut('GETapi-v1-workers--worker_id--medical-records');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-workers--worker_id--medical-records"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/workers/{worker_id}/medical-records</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-workers--worker_id--medical-records"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-workers--worker_id--medical-records"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-workers--worker_id--medical-records"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="GETapi-v1-workers--worker_id--medical-records"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="worker-medical-records-POSTapi-v1-workers--worker_id--medical-records">Add a new medical record. Most recent (by exam_date) becomes the active one.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-workers--worker_id--medical-records">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/workers/architecto/medical-records" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"exam_date\": \"2022-05-31\",
    \"valid_until\": \"2052-05-30\",
    \"status\": \"fit_with_restrictions\",
    \"examining_clinic_en\": \"n\",
    \"examining_clinic_ar\": \"g\",
    \"restrictions_en\": \"z\",
    \"restrictions_ar\": \"m\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto/medical-records"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "exam_date": "2022-05-31",
    "valid_until": "2052-05-30",
    "status": "fit_with_restrictions",
    "examining_clinic_en": "n",
    "examining_clinic_ar": "g",
    "restrictions_en": "z",
    "restrictions_ar": "m"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-workers--worker_id--medical-records">
</span>
<span id="execution-results-POSTapi-v1-workers--worker_id--medical-records" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-workers--worker_id--medical-records"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-workers--worker_id--medical-records"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-workers--worker_id--medical-records" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-workers--worker_id--medical-records">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-workers--worker_id--medical-records" data-method="POST"
      data-path="api/v1/workers/{worker_id}/medical-records"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-workers--worker_id--medical-records', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-workers--worker_id--medical-records"
                    onclick="tryItOut('POSTapi-v1-workers--worker_id--medical-records');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-workers--worker_id--medical-records"
                    onclick="cancelTryOut('POSTapi-v1-workers--worker_id--medical-records');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-workers--worker_id--medical-records"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/workers/{worker_id}/medical-records</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>exam_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="exam_date"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="2022-05-31"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date before or equal to <code>today</code>. Example: <code>2022-05-31</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>valid_until</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="valid_until"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="2052-05-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after <code>exam_date</code>. Example: <code>2052-05-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="fit_with_restrictions"
               data-component="body">
    <br>
<p>Example: <code>fit_with_restrictions</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>fit</code></li> <li><code>fit_with_restrictions</code></li> <li><code>unfit</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>examining_clinic_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="examining_clinic_en"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>examining_clinic_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="examining_clinic_ar"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 128 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>restrictions_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="restrictions_en"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="z"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>z</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>restrictions_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="restrictions_ar"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value="m"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>m</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>metadata</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="metadata"                data-endpoint="POSTapi-v1-workers--worker_id--medical-records"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                <h1 id="workers">Workers</h1>

    <p>CRUD plus the e-Passport read model and helmet/coverall QR PNG generation.</p>

                                <h2 id="workers-POSTapi-v1-workers-bulk">Bulk-import workers.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Accepts {"workers": [{...}, ...]}. Per-record success/failure: a single
record's validation failure does NOT fail the rest of the batch. Each
record is validated against the same rules as the single-record POST.</p>
<p>Idempotency-Key supported globally; replays return the same response.</p>

<span id="example-requests-POSTapi-v1-workers-bulk">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/workers/bulk" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"workers\": [
        []
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/bulk"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "workers": [
        []
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-workers-bulk">
</span>
<span id="execution-results-POSTapi-v1-workers-bulk" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-workers-bulk"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-workers-bulk"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-workers-bulk" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-workers-bulk">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-workers-bulk" data-method="POST"
      data-path="api/v1/workers/bulk"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-workers-bulk', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-workers-bulk"
                    onclick="tryItOut('POSTapi-v1-workers-bulk');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-workers-bulk"
                    onclick="cancelTryOut('POSTapi-v1-workers-bulk');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-workers-bulk"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/workers/bulk</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-workers-bulk"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-workers-bulk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-workers-bulk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>workers</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="workers[0]"                data-endpoint="POSTapi-v1-workers-bulk"
               data-component="body">
        <input type="text" style="display: none"
               name="workers[1]"                data-endpoint="POSTapi-v1-workers-bulk"
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="workers-GETapi-v1-workers">List workers.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Supports filter[employer_organization_id], filter[induction_status],
filter[search] (matches name + employee_id + national_id), and
filter[expiring_within] (days; matches workers with any cert expiring
within N days).</p>

<span id="example-requests-GETapi-v1-workers">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/workers" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-workers">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 8f5eb455-fdc6-4493-abf7-0b017afd4c17
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;8f5eb455-fdc6-4493-abf7-0b017afd4c17&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-workers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-workers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-workers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-workers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-workers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-workers" data-method="GET"
      data-path="api/v1/workers"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-workers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-workers"
                    onclick="tryItOut('GETapi-v1-workers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-workers"
                    onclick="cancelTryOut('GETapi-v1-workers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-workers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/workers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-workers"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-workers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-workers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="workers-POSTapi-v1-workers">Create a worker.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-workers">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/workers" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"employer_organization_id\": \"6ff8f7f6-1eb3-3525-be4a-3932c805afed\",
    \"employee_id\": \"g\",
    \"national_id\": \"z\",
    \"iqama_number\": \"m\",
    \"passport_number\": \"i\",
    \"first_name_en\": \"y\",
    \"last_name_en\": \"v\",
    \"first_name_ar\": \"d\",
    \"last_name_ar\": \"l\",
    \"nationality\": \"jni\",
    \"date_of_birth\": \"2022-05-31\",
    \"phone\": \"n\",
    \"email\": \"ashly64@example.com\",
    \"trade\": \"v\",
    \"induction_status\": \"not_inducted\",
    \"induction_date\": \"2026-05-07T09:16:52\",
    \"induction_valid_until\": \"2052-05-30\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "employer_organization_id": "6ff8f7f6-1eb3-3525-be4a-3932c805afed",
    "employee_id": "g",
    "national_id": "z",
    "iqama_number": "m",
    "passport_number": "i",
    "first_name_en": "y",
    "last_name_en": "v",
    "first_name_ar": "d",
    "last_name_ar": "l",
    "nationality": "jni",
    "date_of_birth": "2022-05-31",
    "phone": "n",
    "email": "ashly64@example.com",
    "trade": "v",
    "induction_status": "not_inducted",
    "induction_date": "2026-05-07T09:16:52",
    "induction_valid_until": "2052-05-30"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-workers">
</span>
<span id="execution-results-POSTapi-v1-workers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-workers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-workers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-workers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-workers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-workers" data-method="POST"
      data-path="api/v1/workers"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-workers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-workers"
                    onclick="tryItOut('POSTapi-v1-workers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-workers"
                    onclick="cancelTryOut('POSTapi-v1-workers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-workers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/workers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-workers"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-workers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-workers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>employer_organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="employer_organization_id"                data-endpoint="POSTapi-v1-workers"
               value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
               data-component="body">
    <br>
<p>Must be a valid UUID. The <code>id</code> of an existing record in the organizations table. Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>employee_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="employee_id"                data-endpoint="POSTapi-v1-workers"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 64 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>national_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="national_id"                data-endpoint="POSTapi-v1-workers"
               value="z"
               data-component="body">
    <br>
<p>Must not be greater than 32 characters. Example: <code>z</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>iqama_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="iqama_number"                data-endpoint="POSTapi-v1-workers"
               value="m"
               data-component="body">
    <br>
<p>Must not be greater than 32 characters. Example: <code>m</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>passport_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="passport_number"                data-endpoint="POSTapi-v1-workers"
               value="i"
               data-component="body">
    <br>
<p>Must not be greater than 32 characters. Example: <code>i</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="first_name_en"                data-endpoint="POSTapi-v1-workers"
               value="y"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>y</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="last_name_en"                data-endpoint="POSTapi-v1-workers"
               value="v"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>v</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="first_name_ar"                data-endpoint="POSTapi-v1-workers"
               value="d"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>d</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="last_name_ar"                data-endpoint="POSTapi-v1-workers"
               value="l"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>l</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>nationality</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="nationality"                data-endpoint="POSTapi-v1-workers"
               value="jni"
               data-component="body">
    <br>
<p>Must be 3 characters. Example: <code>jni</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>date_of_birth</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="date_of_birth"                data-endpoint="POSTapi-v1-workers"
               value="2022-05-31"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date before <code>today</code>. Example: <code>2022-05-31</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-v1-workers"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 32 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-workers"
               value="ashly64@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Must not be greater than 255 characters. Example: <code>ashly64@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>trade</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="trade"                data-endpoint="POSTapi-v1-workers"
               value="v"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>v</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>induction_status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="induction_status"                data-endpoint="POSTapi-v1-workers"
               value="not_inducted"
               data-component="body">
    <br>
<p>Example: <code>not_inducted</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>not_inducted</code></li> <li><code>inducted</code></li> <li><code>expired</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>induction_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="induction_date"                data-endpoint="POSTapi-v1-workers"
               value="2026-05-07T09:16:52"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-07T09:16:52</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>induction_valid_until</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="induction_valid_until"                data-endpoint="POSTapi-v1-workers"
               value="2052-05-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after or equal to <code>induction_date</code>. Example: <code>2052-05-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>metadata</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="metadata"                data-endpoint="POSTapi-v1-workers"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="workers-GETapi-v1-workers--id-">Get a single worker.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-workers--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/workers/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-workers--id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 64e72e94-f68f-4c0a-9167-7a3de27cbaa1
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;64e72e94-f68f-4c0a-9167-7a3de27cbaa1&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-workers--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-workers--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-workers--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-workers--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-workers--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-workers--id-" data-method="GET"
      data-path="api/v1/workers/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-workers--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-workers--id-"
                    onclick="tryItOut('GETapi-v1-workers--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-workers--id-"
                    onclick="cancelTryOut('GETapi-v1-workers--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-workers--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/workers/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-workers--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-workers--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-workers--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-v1-workers--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="workers-PATCHapi-v1-workers--id-">Update a worker.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PATCHapi-v1-workers--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/workers/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"employee_id\": \"b\",
    \"national_id\": \"n\",
    \"iqama_number\": \"g\",
    \"passport_number\": \"z\",
    \"first_name_en\": \"m\",
    \"last_name_en\": \"i\",
    \"first_name_ar\": \"y\",
    \"last_name_ar\": \"v\",
    \"nationality\": \"dlj\",
    \"date_of_birth\": \"2022-05-31\",
    \"phone\": \"n\",
    \"email\": \"ashly64@example.com\",
    \"trade\": \"v\",
    \"induction_status\": \"not_inducted\",
    \"induction_date\": \"2026-05-07T09:16:52\",
    \"induction_valid_until\": \"2026-05-07T09:16:52\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "employee_id": "b",
    "national_id": "n",
    "iqama_number": "g",
    "passport_number": "z",
    "first_name_en": "m",
    "last_name_en": "i",
    "first_name_ar": "y",
    "last_name_ar": "v",
    "nationality": "dlj",
    "date_of_birth": "2022-05-31",
    "phone": "n",
    "email": "ashly64@example.com",
    "trade": "v",
    "induction_status": "not_inducted",
    "induction_date": "2026-05-07T09:16:52",
    "induction_valid_until": "2026-05-07T09:16:52"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-workers--id-">
</span>
<span id="execution-results-PATCHapi-v1-workers--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-workers--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-workers--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-workers--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-workers--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-workers--id-" data-method="PATCH"
      data-path="api/v1/workers/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-workers--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-workers--id-"
                    onclick="tryItOut('PATCHapi-v1-workers--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-workers--id-"
                    onclick="cancelTryOut('PATCHapi-v1-workers--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-workers--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/workers/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PATCHapi-v1-workers--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-workers--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-workers--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PATCHapi-v1-workers--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>employee_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="employee_id"                data-endpoint="PATCHapi-v1-workers--id-"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 64 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>national_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="national_id"                data-endpoint="PATCHapi-v1-workers--id-"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 32 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>iqama_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="iqama_number"                data-endpoint="PATCHapi-v1-workers--id-"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 32 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>passport_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="passport_number"                data-endpoint="PATCHapi-v1-workers--id-"
               value="z"
               data-component="body">
    <br>
<p>Must not be greater than 32 characters. Example: <code>z</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="first_name_en"                data-endpoint="PATCHapi-v1-workers--id-"
               value="m"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>m</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name_en</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="last_name_en"                data-endpoint="PATCHapi-v1-workers--id-"
               value="i"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>i</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="first_name_ar"                data-endpoint="PATCHapi-v1-workers--id-"
               value="y"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>y</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name_ar</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="last_name_ar"                data-endpoint="PATCHapi-v1-workers--id-"
               value="v"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>v</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>nationality</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="nationality"                data-endpoint="PATCHapi-v1-workers--id-"
               value="dlj"
               data-component="body">
    <br>
<p>Must be 3 characters. Example: <code>dlj</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>date_of_birth</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="date_of_birth"                data-endpoint="PATCHapi-v1-workers--id-"
               value="2022-05-31"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date before <code>today</code>. Example: <code>2022-05-31</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="PATCHapi-v1-workers--id-"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 32 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="PATCHapi-v1-workers--id-"
               value="ashly64@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Must not be greater than 255 characters. Example: <code>ashly64@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>trade</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="trade"                data-endpoint="PATCHapi-v1-workers--id-"
               value="v"
               data-component="body">
    <br>
<p>Must not be greater than 80 characters. Example: <code>v</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>induction_status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="induction_status"                data-endpoint="PATCHapi-v1-workers--id-"
               value="not_inducted"
               data-component="body">
    <br>
<p>Example: <code>not_inducted</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>not_inducted</code></li> <li><code>inducted</code></li> <li><code>expired</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>induction_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="induction_date"                data-endpoint="PATCHapi-v1-workers--id-"
               value="2026-05-07T09:16:52"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-07T09:16:52</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>induction_valid_until</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="induction_valid_until"                data-endpoint="PATCHapi-v1-workers--id-"
               value="2026-05-07T09:16:52"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-07T09:16:52</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>metadata</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="metadata"                data-endpoint="PATCHapi-v1-workers--id-"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="workers-DELETEapi-v1-workers--id-">Soft-delete a worker.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-workers--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/workers/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-workers--id-">
</span>
<span id="execution-results-DELETEapi-v1-workers--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-workers--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-workers--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-workers--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-workers--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-workers--id-" data-method="DELETE"
      data-path="api/v1/workers/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-workers--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-workers--id-"
                    onclick="tryItOut('DELETEapi-v1-workers--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-workers--id-"
                    onclick="cancelTryOut('DELETEapi-v1-workers--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-workers--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/workers/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-v1-workers--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-workers--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-workers--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="DELETEapi-v1-workers--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="workers-GETapi-v1-workers--worker_id--passport">Get the consolidated e-Passport view: identity, employer, all valid certs,
medical fitness, induction status. Drives the worker detail screen and
is the read model the gate-scan flow projects from.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-workers--worker_id--passport">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/workers/architecto/passport" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto/passport"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-workers--worker_id--passport">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 98b2567e-6b7f-45f2-9d6d-f62c853b5479
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;98b2567e-6b7f-45f2-9d6d-f62c853b5479&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-workers--worker_id--passport" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-workers--worker_id--passport"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-workers--worker_id--passport"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-workers--worker_id--passport" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-workers--worker_id--passport">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-workers--worker_id--passport" data-method="GET"
      data-path="api/v1/workers/{worker_id}/passport"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-workers--worker_id--passport', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-workers--worker_id--passport"
                    onclick="tryItOut('GETapi-v1-workers--worker_id--passport');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-workers--worker_id--passport"
                    onclick="cancelTryOut('GETapi-v1-workers--worker_id--passport');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-workers--worker_id--passport"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/workers/{worker_id}/passport</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-workers--worker_id--passport"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-workers--worker_id--passport"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-workers--worker_id--passport"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="GETapi-v1-workers--worker_id--passport"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="workers-GETapi-v1-workers--worker_id--qr-helmet">Generate the helmet QR PNG for a worker.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns image/png. Used by the print flow on the web admin and by mobile
if a worker needs a fresh sticker. Requires authenticated caller — token
never leaves the server in plain text.</p>

<span id="example-requests-GETapi-v1-workers--worker_id--qr-helmet">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/workers/architecto/qr/helmet" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto/qr/helmet"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-workers--worker_id--qr-helmet">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 49ae1495-043d-40c8-a3a0-d550b82ff3fc
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;49ae1495-043d-40c8-a3a0-d550b82ff3fc&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-workers--worker_id--qr-helmet" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-workers--worker_id--qr-helmet"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-workers--worker_id--qr-helmet"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-workers--worker_id--qr-helmet" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-workers--worker_id--qr-helmet">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-workers--worker_id--qr-helmet" data-method="GET"
      data-path="api/v1/workers/{worker_id}/qr/helmet"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-workers--worker_id--qr-helmet', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-workers--worker_id--qr-helmet"
                    onclick="tryItOut('GETapi-v1-workers--worker_id--qr-helmet');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-workers--worker_id--qr-helmet"
                    onclick="cancelTryOut('GETapi-v1-workers--worker_id--qr-helmet');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-workers--worker_id--qr-helmet"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/workers/{worker_id}/qr/helmet</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-workers--worker_id--qr-helmet"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-workers--worker_id--qr-helmet"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-workers--worker_id--qr-helmet"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="GETapi-v1-workers--worker_id--qr-helmet"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="workers-GETapi-v1-workers--worker_id--qr-coverall">Generate the coverall QR PNG for a worker.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-workers--worker_id--qr-coverall">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/workers/architecto/qr/coverall" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/workers/architecto/qr/coverall"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-workers--worker_id--qr-coverall">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-request-id: 03280b12-93f0-45a3-a77a-94d858a1c155
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: {
        &quot;code&quot;: &quot;UNAUTHENTICATED&quot;,
        &quot;message&quot;: &quot;Authentication required.&quot;,
        &quot;details&quot;: null,
        &quot;request_id&quot;: &quot;03280b12-93f0-45a3-a77a-94d858a1c155&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-workers--worker_id--qr-coverall" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-workers--worker_id--qr-coverall"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-workers--worker_id--qr-coverall"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-workers--worker_id--qr-coverall" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-workers--worker_id--qr-coverall">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-workers--worker_id--qr-coverall" data-method="GET"
      data-path="api/v1/workers/{worker_id}/qr/coverall"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-workers--worker_id--qr-coverall', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-workers--worker_id--qr-coverall"
                    onclick="tryItOut('GETapi-v1-workers--worker_id--qr-coverall');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-workers--worker_id--qr-coverall"
                    onclick="cancelTryOut('GETapi-v1-workers--worker_id--qr-coverall');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-workers--worker_id--qr-coverall"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/workers/{worker_id}/qr/coverall</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-workers--worker_id--qr-coverall"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-workers--worker_id--qr-coverall"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-workers--worker_id--qr-coverall"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>worker_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="worker_id"                data-endpoint="GETapi-v1-workers--worker_id--qr-coverall"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the worker. Example: <code>architecto</code></p>
            </div>
                    </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
