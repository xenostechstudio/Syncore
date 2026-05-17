<?php

use Spatie\Permission\Models\Role;

/**
 * Real DOM-walk regression for header-slot gear dropdowns.
 *
 * The prior static-string HeaderDropdownAlpineDispatchTest lied 68
 * times — it asserted the expected attribute existed in source but
 * couldn't prove the click handler bound at runtime. The button
 * actually lived OUTSIDE every wire:id AND every x-data, so every
 * pattern (wire:click, Livewire.dispatch, bare x-on:click) silently
 * did nothing.
 *
 * This test parses each form's rendered HTML and asserts two
 * structural conditions that have to hold for the gear dropdown to
 * function at all:
 *
 *   (1) Each gear menu item that uses x-on:click="$dispatch(...)" has
 *       an <div x-data=...> ancestor — without it Alpine never binds.
 *   (2) <ui-menu> is a direct child of <ui-dropdown> — Flux's popover
 *       anchoring breaks if any wrapper sits between them (this is
 *       how we discovered the gear itself stopped opening when an
 *       earlier patch wrapped the menu in <div x-data> incorrectly).
 *
 * Cases are added as each form is swept to the new modal pattern.
 * Forms in the OLD Alpine-dispatch-without-x-data state stay in the
 * legacy HeaderDropdownAlpineDispatchTest until they're swept; that
 * test still passes for them because they statically contain the
 * dispatch strings — it just doesn't prove the strings work, which
 * is exactly the trap this test exists to close.
 */

/** @return string Rendered HTML of the form's edit page. */
function renderFormEditHtml(Tests\TestCase $test, callable $factory, string $routeName): string
{
    $admin = \App\Models\User::factory()->create();
    Role::firstOrCreate(['name' => 'super-admin']);
    $admin->assignRole('super-admin');
    $test->actingAs($admin);

    $modelId = $factory();

    return $test->get(route($routeName, $modelId))->getContent();
}

/** @return DOMDocument */
function parseHtml(string $html): DOMDocument
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    return $doc;
}

/**
 * Find every <button> with x-on:click whose value mentions $dispatch
 * inside the page's <header> — these are the gear menu items.
 * @return array<DOMElement>
 */
function findDispatchButtonsInHeader(DOMDocument $doc): array
{
    $xpath = new DOMXPath($doc);
    $headers = $xpath->query('//header');
    $found = [];
    foreach ($headers as $header) {
        $buttons = $xpath->query('.//button', $header);
        foreach ($buttons as $b) {
            foreach ($b->attributes as $attr) {
                if (in_array($attr->name, ['x-on:click', '@click'], true) && str_contains($attr->value, '$dispatch')) {
                    $found[] = $b;
                    break;
                }
            }
        }
    }
    return $found;
}

/** @return ?string Tag+x-data of the nearest x-data ancestor, or null. */
function nearestXDataAncestor(DOMElement $el): ?string
{
    $node = $el;
    while ($node = $node->parentNode) {
        if ($node->nodeType === XML_ELEMENT_NODE && $node->hasAttribute('x-data')) {
            return $node->tagName.'[x-data="'.substr($node->getAttribute('x-data'), 0, 40).'"]';
        }
    }
    return null;
}

/** All ui-dropdown elements and their direct element children's tag names. */
function dropdownDirectChildren(DOMDocument $doc): array
{
    $xpath = new DOMXPath($doc);
    $dropdowns = $xpath->query('//*[local-name()="ui-dropdown"]');
    $out = [];
    foreach ($dropdowns as $d) {
        $kids = [];
        foreach ($d->childNodes as $kid) {
            if ($kid->nodeType === XML_ELEMENT_NODE) {
                $kids[] = $kid->tagName;
            }
        }
        $out[] = $kids;
    }
    return $out;
}

it('renders a working gear dropdown on a form edit page', function (string $routeName, callable $factory) {
    $html = renderFormEditHtml($this, $factory, $routeName);
    $doc = parseHtml($html);

    // (1) Every dispatching gear button has an x-data ancestor.
    $buttons = findDispatchButtonsInHeader($doc);
    expect($buttons)->not->toBeEmpty("No \$dispatch buttons found in <header> for $routeName — has the gear menu been added?");

    foreach ($buttons as $i => $b) {
        $ancestor = nearestXDataAncestor($b);
        expect($ancestor)->not->toBeNull(
            "Gear button #$i in $routeName has no <... x-data> ancestor. Alpine cannot bind x-on:click — the click is silently dead. Add <div x-data=\"{}\"> wrapping the <flux:dropdown> inside <x-slot:header>."
        );
    }

    // (2) Every <ui-dropdown> in the rendered page has <ui-menu> as a
    //     direct child (Flux popover anchoring requirement). A div or
    //     other wrapper between them breaks the dropdown trigger.
    foreach (dropdownDirectChildren($doc) as $i => $kids) {
        $hasMenu = in_array('ui-menu', $kids, true);
        expect($hasMenu)->toBeTrue(
            "<ui-dropdown> #$i on $routeName has children [".implode(', ', $kids)."] but no direct <ui-menu>. Flux's popover anchor requires <ui-menu> as a direct child of <ui-dropdown>. Wrap OUTSIDE <flux:dropdown>, not between it and <flux:menu>."
        );
    }
})->with([
    'sales-orders' => [
        'sales.orders.edit',
        function () {
            $customer = \App\Models\Sales\Customer::factory()->create();
            $order = \App\Models\Sales\SalesOrder::create([
                'order_number' => 'STRUCT-'.uniqid(),
                'customer_id' => $customer->id,
                'user_id' => \App\Models\User::first()?->id ?? \App\Models\User::factory()->create()->id,
                'order_date' => now(),
                'expected_delivery_date' => now()->addDays(7),
                'status' => 'draft',
            ]);
            return $order->id;
        },
    ],
    'crm-leads' => [
        'crm.leads.edit',
        function () {
            $lead = \App\Models\CRM\Lead::create([
                'name' => 'Struct Test Lead '.uniqid(),
                'email' => 'struct-'.uniqid().'@example.com',
                'status' => 'new',
            ]);
            return $lead->id;
        },
    ],
]);
