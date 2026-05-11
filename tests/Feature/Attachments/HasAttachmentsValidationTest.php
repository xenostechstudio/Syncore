<?php

/**
 * Coverage for HasAttachments::validateAttachmentFile — defense against
 * arbitrary file upload. Before this validation, the trait accepted any
 * UploadedFile and stored it on the public disk, world-readable if the
 * path leaked. Now: MIME whitelist + 10MB cap by default, both
 * overridable per consuming model.
 */

use App\Models\Sales\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->customer = Customer::factory()->create();
});

it('stores a file when MIME is allowed and size is under the cap', function () {
    $file = UploadedFile::fake()->create('contract.pdf', 200, 'application/pdf'); // 200 KB

    $attachment = $this->customer->addAttachment($file, 'Signed contract');

    expect($attachment)->not->toBeNull();
    expect($attachment->filename)->toBe('contract.pdf');
    expect($attachment->mime_type)->toBe('application/pdf');
    expect($this->customer->attachmentCount())->toBe(1);
});

it('rejects files larger than the 10MB default cap', function () {
    // 11 MB — over the 10 MB default.
    $file = UploadedFile::fake()->create('huge.pdf', 11 * 1024, 'application/pdf');

    expect(fn () => $this->customer->addAttachment($file))
        ->toThrow(InvalidArgumentException::class, 'size limit');

    expect($this->customer->attachmentCount())->toBe(0);
});

it('rejects files with a MIME type outside the whitelist', function () {
    // A malicious .exe masquerading as PDF would still be detected by
    // Symfony's MIME guess (which sniffs content, not extension). Here
    // we simulate by giving the fake file an exe MIME directly.
    $file = UploadedFile::fake()->create('payload.exe', 100, 'application/x-msdownload');

    expect(fn () => $this->customer->addAttachment($file))
        ->toThrow(InvalidArgumentException::class, 'MIME type');

    expect($this->customer->attachmentCount())->toBe(0);
});

it('still rejects files that pass extension check but fail MIME whitelist (defense in depth)', function () {
    // Same idea — extension says "pdf" but real MIME is something else.
    $file = UploadedFile::fake()->create('looks-like.pdf', 100, 'application/x-php');

    expect(fn () => $this->customer->addAttachment($file))
        ->toThrow(InvalidArgumentException::class, 'MIME type');
});

it('accepts common image types out of the box', function () {
    $jpg = UploadedFile::fake()->image('photo.jpg');

    $attachment = $this->customer->addAttachment($jpg);

    expect($attachment->mime_type)->toStartWith('image/');
    expect($this->customer->attachmentCount())->toBe(1);
});

it('rejects invalid uploads (server-side upload errors)', function () {
    // Construct a file that reports an upload error. We simulate by using
    // a real UploadedFile with `$test = false` and a non-OK error code,
    // which mirrors a failed multipart upload.
    $tmpPath = tempnam(sys_get_temp_dir(), 'attach-test-');
    file_put_contents($tmpPath, 'x');

    $file = new UploadedFile(
        path: $tmpPath,
        originalName: 'broken.pdf',
        mimeType: 'application/pdf',
        error: UPLOAD_ERR_PARTIAL,
        test: false,
    );

    expect(fn () => $this->customer->addAttachment($file))
        ->toThrow(InvalidArgumentException::class, 'not valid');

    @unlink($tmpPath);
});
