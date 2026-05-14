<?php

/**
 * Smoke for WithImport's structured error pathway. Before the rewrite, an
 * XLSX with a row failing WithValidation surfaced as a single stringified
 * exception message in the modal — every row failure mashed into one wall
 * of text, no field, no row number you could click. Now: a Row|Field|Message
 * table, plus a downloadable CSV with the original row values.
 */

use App\Livewire\Sales\Configuration\Taxes\Index;
use App\Models\Sales\Tax;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create()->assignRole('super-admin');
    $this->actingAs($this->user);
});

it('surfaces validation failures as structured Row/Field/Message entries', function () {
    // Two rows: one valid, one missing required `name`. The empty-name row
    // would be silently skipped by the import's collection() (it short-circuits
    // when name is empty), but rate=999.99 with a non-empty code still trips
    // WithValidation's `required|string` rule on name when we leave it blank.
    $csv = "name,code,rate,type,scope\n,BAD,999.99,percentage,sales\nValid Tax,GOOD,5,percentage,sales\n";
    $file = UploadedFile::fake()->createWithContent('taxes.csv', $csv);

    Livewire::test(Index::class)
        ->call('openImportModal')
        ->set('importFile', $file)
        ->call('import')
        ->assertSet('showImportModal', true) // stays open when there are errors
        ->tap(function ($component) {
            $errors = $component->get('importErrors');
            expect($errors)->not->toBeEmpty();

            // First entry is the structured failure for the empty `name`.
            $first = $errors[0];
            expect($first)->toBeArray()
                ->and($first['row'])->toBe(2)
                ->and($first['attribute'])->toBe('name')
                ->and($first['message'])->toContain('name'); // "The name field is required."
        });
});

it('streams a CSV of failed rows with original values for re-upload', function () {
    $csv = "name,code,rate,type,scope\n,BAD,999.99,percentage,sales\n";
    $file = UploadedFile::fake()->createWithContent('taxes.csv', $csv);

    $component = Livewire::test(Index::class)
        ->call('openImportModal')
        ->set('importFile', $file)
        ->call('import');

    // Livewire 3 forwards Testable->__call to the response, so we can't pull
    // a StreamedResponse out via property access. Call the trait method
    // directly on the component instance — same code path the wire:click
    // would hit, just without the request envelope.
    $response = $component->instance()->downloadImportErrors();

    expect($response->headers->get('Content-Type'))->toContain('text/csv');

    ob_start();
    $response->sendContent();
    $body = ob_get_clean();

    // Header includes the standard Row/Field/Error columns + the original-row keys.
    expect($body)->toContain('Row,Field,Error');
    // The CSV row should carry the original `code` value (BAD) so the user can
    // open the file, fix the failing column, and re-upload.
    expect($body)->toContain('BAD');
    expect($body)->toContain('name');
});

it('with SkipsOnFailure, valid rows import alongside invalid ones (collect-and-continue)', function () {
    // 3 rows: 2 valid, 1 with empty name (fails the required|string rule).
    // With SkipsOnFailure on the import class, the valid rows must land in
    // the DB and the bad row's failure must show up in the errors array —
    // both behaviors at once.
    $csv = "name,code,rate,type,scope\n"
        . "ValidA,SOF-A,11,percentage,sales\n"
        . ",BAD,5,percentage,sales\n"
        . "ValidB,SOF-B,12,percentage,sales\n";

    $file = UploadedFile::fake()->createWithContent('taxes.csv', $csv);

    Livewire::test(Index::class)
        ->call('openImportModal')
        ->set('importFile', $file)
        ->call('import')
        ->tap(function ($component) {
            $errors = $component->get('importErrors');

            // Exactly one structured error for the bad row.
            expect($errors)->toHaveCount(1)
                ->and($errors[0]['attribute'])->toBe('name');

            // Both valid rows landed despite the failure in between.
            expect(Tax::where('code', 'SOF-A')->exists())->toBeTrue();
            expect(Tax::where('code', 'SOF-B')->exists())->toBeTrue();
        });
});

it('emits one entry per failing rule (not one joined entry per attribute)', function () {
    // The `name` field has TWO rules: required|string|max:255. An empty value
    // fails `required` only (one entry). A 300-character value fails `max:255`
    // only (one entry). Both behaviors must be uniform across trait-using and
    // inline-onFailure imports so the error CSV count is stable.
    //
    // We can verify the per-message expansion via the modal's $importErrors
    // array — every entry has a single message string, not a "; "-joined blob.
    $longName = str_repeat('A', 300); // exceeds max:255
    $csv = "name,code,rate,type,scope\n"
        . $longName . ",TOOLONG,10,percentage,sales\n";

    $file = UploadedFile::fake()->createWithContent('taxes.csv', $csv);

    Livewire::test(Index::class)
        ->call('openImportModal')
        ->set('importFile', $file)
        ->call('import')
        ->tap(function ($component) {
            $errors = $component->get('importErrors');
            expect($errors)->not->toBeEmpty();
            // Per-message shape: the message field is a single rule's text,
            // never a "; "-joined blob from multiple rules.
            foreach ($errors as $err) {
                expect($err['message'])->not->toContain('; ');
            }
        });
});

it('keeps closing the modal cleanly when there are no errors', function () {
    $csv = "name,code,rate,type,scope\nValid Tax,GOOD,5,percentage,sales\n";
    $file = UploadedFile::fake()->createWithContent('taxes.csv', $csv);

    Livewire::test(Index::class)
        ->call('openImportModal')
        ->set('importFile', $file)
        ->call('import')
        ->assertSet('showImportModal', false)
        ->assertSet('importErrors', []);
});
