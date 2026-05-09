<?php

/**
 * Smoke for WithImport's structured error pathway. Before the rewrite, an
 * XLSX with a row failing WithValidation surfaced as a single stringified
 * exception message in the modal — every row failure mashed into one wall
 * of text, no field, no row number you could click. Now: a Row|Field|Message
 * table, plus a downloadable CSV with the original row values.
 */

use App\Livewire\Sales\Configuration\Taxes\Index;
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
