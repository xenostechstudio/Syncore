<?php

/**
 * Pins the visibility logic of the three "after-delivery" action buttons
 * (Record POD, Record Feedback, Return) on the Delivery Order form. The
 * buttons are gated by status (only DELIVERED) and by whether the
 * underlying capture has already happened. Regression coverage for the
 * gap where all three sat side-by-side regardless of whether each was
 * already recorded.
 */

use App\Livewire\Delivery\Orders\Form as DeliveryOrderForm;
use Livewire\Livewire;

beforeEach(function () {
    // Reuses the scenario + advancement helpers from the returns flow test
    // file. Both files are loaded by Pest's autodiscovery, so the helpers
    // are in scope here without a manual require.
});

it('hides the Record POD button once POD data is captured', function () {
    $data = makeDeliveryOrderScenario();
    advanceDeliveryOrderStatus($data['deliveryOrder'], $data['user'], 3); // → DELIVERED

    $html = Livewire::actingAs($data['user'])
        ->test(DeliveryOrderForm::class, ['id' => $data['deliveryOrder']->id])
        ->html();
    expect($html)->toContain('Record Proof of Delivery'); // visible before capture

    // Record the POD
    $data['deliveryOrder']->update([
        'received_by' => 'Jane Doe',
        'signature_image' => null,
        'delivery_photo' => null,
    ]);

    $html = Livewire::actingAs($data['user'])
        ->test(DeliveryOrderForm::class, ['id' => $data['deliveryOrder']->id])
        ->html();

    expect($html)->not->toContain('Record Proof of Delivery');
    expect($html)->toContain('Proof of delivery captured');
});

it('hides the Record Feedback button once a rating is saved', function () {
    $data = makeDeliveryOrderScenario();
    advanceDeliveryOrderStatus($data['deliveryOrder'], $data['user'], 3); // → DELIVERED

    $html = Livewire::actingAs($data['user'])
        ->test(DeliveryOrderForm::class, ['id' => $data['deliveryOrder']->id])
        ->html();
    expect($html)->toContain('Record Feedback');

    $data['deliveryOrder']->update([
        'customer_rating' => 4,
        'customer_feedback' => 'Quick and tidy',
    ]);

    $html = Livewire::actingAs($data['user'])
        ->test(DeliveryOrderForm::class, ['id' => $data['deliveryOrder']->id])
        ->html();

    expect($html)->not->toContain('Record Feedback');
    expect($html)->toContain('Feedback captured');
    expect($html)->toContain('(4/5)');
});

it('keeps the Return button visible after a delivery is captured', function () {
    $data = makeDeliveryOrderScenario();
    advanceDeliveryOrderStatus($data['deliveryOrder'], $data['user'], 3); // → DELIVERED

    // Capture both POD and feedback to verify Return survives the cleanup
    $data['deliveryOrder']->update([
        'received_by' => 'Jane Doe',
        'customer_rating' => 5,
    ]);

    $html = Livewire::actingAs($data['user'])
        ->test(DeliveryOrderForm::class, ['id' => $data['deliveryOrder']->id])
        ->html();

    expect($html)->toContain('Return');
});

it('hides all three buttons on non-delivered orders regardless of capture state', function () {
    $data = makeDeliveryOrderScenario();
    // Order stays in PENDING — no advance() calls.

    $html = Livewire::actingAs($data['user'])
        ->test(DeliveryOrderForm::class, ['id' => $data['deliveryOrder']->id])
        ->html();

    expect($html)->not->toContain('Record Proof of Delivery');
    expect($html)->not->toContain('Record Feedback');
    // "Return" can appear in unrelated copy (Returns tab header on
    // delivered orders), but on a PENDING delivery there should be no
    // action button by that exact label-with-button-styles.
    expect($html)->not->toContain('wire:click="openReturnModal"');
});
