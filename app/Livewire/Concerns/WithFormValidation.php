<?php

namespace App\Livewire\Concerns;

trait WithFormValidation
{
    /**
     * Get validation rules for the form.
     * Override this method in your component.
     */
    protected function formRules(): array
    {
        return [];
    }

    /**
     * Get validation messages for the form.
     * Override this method in your component.
     */
    protected function formMessages(): array
    {
        return [];
    }

    /**
     * Get validation attributes for the form.
     * Override this method in your component.
     */
    protected function formAttributes(): array
    {
        return [];
    }

    /**
     * Validate the form using the defined rules.
     */
    protected function validateForm(): array
    {
        return $this->validate(
            $this->formRules(),
            $this->formMessages(),
            $this->formAttributes()
        );
    }

    /**
     * Validate specific fields only.
     */
    protected function validateFields(array $fields): array
    {
        $rules = array_intersect_key($this->formRules(), array_flip($fields));
        $messages = $this->formMessages();
        $attributes = $this->formAttributes();

        return $this->validate($rules, $messages, $attributes);
    }

    /**
     * Check if form has items and filter empty ones.
     * Useful for forms with line items (orders, invoices, etc.)
     */
    protected function validateItems(string $itemsProperty = 'items', string $requiredField = 'product_id'): bool
    {
        if (!property_exists($this, $itemsProperty)) {
            return true;
        }

        $items = $this->{$itemsProperty};
        
        // Filter out empty items
        $validItems = collect($items)
            ->filter(fn($item) => !empty($item[$requiredField]))
            ->values()
            ->toArray();

        if (empty($validItems)) {
            $this->addError($itemsProperty, 'Please add at least one item.');
            return false;
        }

        $this->{$itemsProperty} = $validItems;
        return true;
    }

    /**
     * Prepare data for saving, converting empty strings to null.
     */
    protected function prepareDataForSave(array $data, array $nullableFields = []): array
    {
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    /**
     * Common validation rules for email fields.
     */
    protected function emailRule(bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|') . 'email|max:255';
    }

    /**
     * Common validation rules for phone fields.
     */
    protected function phoneRule(bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|') . 'string|max:50';
    }

    /**
     * Common validation rules for money/price fields.
     */
    protected function moneyRule(bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|') . 'numeric|min:0';
    }

    /**
     * Common validation rules for quantity fields.
     */
    protected function quantityRule(bool $required = false, int $min = 1): string
    {
        return ($required ? 'required|' : 'nullable|') . "integer|min:{$min}";
    }
}
