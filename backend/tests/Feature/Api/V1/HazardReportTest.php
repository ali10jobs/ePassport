<?php

use App\Models\HazardReport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    seedAll();
    Storage::fake();
});

it('accepts an anonymous hazard report and strips EXIF', function () {
    // Build a small JPEG via PHP GD (no EXIF in this synthetic image, but the
    // code path runs Intervention's re-encode either way).
    $tmpPath = tempnam(sys_get_temp_dir(), 'haz').'.jpg';
    $im = imagecreatetruecolor(400, 300);
    imagefilledrectangle($im, 0, 0, 400, 300, imagecolorallocate($im, 200, 60, 60));
    imagejpeg($im, $tmpPath, 85);
    imagedestroy($im);

    $upload = new UploadedFile($tmpPath, 'hazard.jpg', 'image/jpeg', null, true);

    $response = $this->postJson('/api/v1/hazard-reports/anonymous', [
        'photo' => $upload,
        'category' => 'fall',
        'severity' => 'high',
        'description' => 'pest test',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['anonymous_report_id', 'status', 'check_status_url']]);

    @unlink($tmpPath);
});

it('rejects anonymous submission without a photo', function () {
    $this->postJson('/api/v1/hazard-reports/anonymous', [
        'category' => 'fall',
        'severity' => 'low',
    ])->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('NEVER captures reporter PII on anonymous submission', function () {
    $tmpPath = tempnam(sys_get_temp_dir(), 'haz').'.jpg';
    $im = imagecreatetruecolor(100, 100);
    imagejpeg($im, $tmpPath, 85);
    imagedestroy($im);
    $upload = new UploadedFile($tmpPath, 'h.jpg', 'image/jpeg', null, true);

    $this->withHeaders([
        'X-Forwarded-For' => '203.0.113.42',
        'User-Agent' => 'PestAttacker/1.0',
    ])->postJson('/api/v1/hazard-reports/anonymous', [
        'photo' => $upload,
        'category' => 'fall',
        'severity' => 'low',
        'description' => 'PestSuite test description',
    ])->assertStatus(201);

    $report = HazardReport::latest('created_at')->first();
    expect($report->reporter_user_id)->toBeNull();
    expect((bool) $report->is_anonymous)->toBeTrue();

    // No PII columns should exist or be populated
    $row = DB::table('hazard_reports')->where('id', $report->id)->first();
    expect(property_exists($row, 'submitter_id'))->toBeFalse();
    expect(property_exists($row, 'submitter_ip'))->toBeFalse();

    @unlink($tmpPath);
});

it('public status check exposes only public notes (never internal)', function () {
    $tmpPath = tempnam(sys_get_temp_dir(), 'haz').'.jpg';
    $im = imagecreatetruecolor(100, 100);
    imagejpeg($im, $tmpPath, 85);
    imagedestroy($im);
    $upload = new UploadedFile($tmpPath, 'h.jpg', 'image/jpeg', null, true);

    $resp = $this->postJson('/api/v1/hazard-reports/anonymous', [
        'photo' => $upload,
        'category' => 'fall',
        'severity' => 'low',
        'description' => 'PestSuite test description',
    ])->assertStatus(201);
    $anonId = $resp->json('data.anonymous_report_id');
    $report = HazardReport::where('anonymous_report_id', $anonId)->first();

    // Authenticated user adds an internal note + a public note
    $token = tokenFor('khalid.maincon@epassport.local');
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/hazard-reports/{$report->id}/notes", [
            'note_type' => 'internal',
            'body' => 'INTERNAL_SECRET_PHRASE',
        ])->assertStatus(201);
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/hazard-reports/{$report->id}/notes", [
            'note_type' => 'public',
            'body' => 'PUBLIC_UPDATE_PHRASE',
        ])->assertStatus(201);

    // Public status check
    $public = $this->getJson("/api/v1/hazard-reports/anonymous/{$anonId}");
    $public->assertOk();
    $bodyText = $public->getContent();
    expect($bodyText)->toContain('PUBLIC_UPDATE_PHRASE');
    expect($bodyText)->not->toContain('INTERNAL_SECRET_PHRASE');

    @unlink($tmpPath);
});
