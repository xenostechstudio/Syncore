<?php

namespace App\Livewire\Concerns;

/**
 * WithFormValidation Trait
 * 
 * Provides standardized form validation patterns for Livewire components.
 * Includes common validation rules, item validation, and data preparation.
 * 
 * Usage:
 * ```php
 * class MyForm extends Component
 * {
 *     use WithFormValidation;
 *     
 *     protected function formRules(): array
 *     {
 *         return [
 *             'name' => 'required|string|max:255',
 *             'email' => $this->emailRule(true),
 *             'phone' => $this->phoneRule(),
 *             'amount' => $this->moneyRule(true),
 *         ];
 *     }
 * }
 * ```
 */
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
     * Validate items with quantity check.
     */
    protected function validateItemsWithQuantity(
        string $itemsProperty = 'items',
        string $requiredField = 'product_id',
        string $quantityField = 'quantity'
    ): bool {
        if (!$this->validateItems($itemsProperty, $requiredField)) {
            return false;
        }

        $items = $this->{$itemsProperty};
        
        foreach ($items as $index => $item) {
            if (empty($item[$quantityField]) || $item[$quantityField] <= 0) {
                $this->addError("{$itemsProperty}.{$index}.{$quantityField}", 'Quantity must be greater than 0.');
                return false;
            }
        }

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
     * Extract form data from component properties.
     */
    protected function extractFormData(array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            if (property_exists($this, $field)) {
                $data[$field] = $this->{$field};
            }
        }
        return $data;
    }

    /**
     * Fill component properties from model.
     */
    protected function fillFromModel($model, array $fields): void
    {
        foreach ($fields as $field) {
            if (property_exists($this, $field) && isset($model->{$field})) {
                $this->{$field} = $model->{$field};
            }
        }
    }

    /**
     * Reset form fields to default values.
     */
    protected function resetFormFields(array $fields, array $defaults = []): void
    {
        foreach ($fields as $field) {
            if (property_exists($this, $field)) {
                $this->{$field} = $defaults[$field] ?? null;
            }
        }
    }

    // ==========================================
    // Common Validation Rules
    // ==========================================

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
    protected function moneyRule(bool $required = false, float $min = 0): string
    {
        return ($required ? 'required|' : 'nullable|') . "numeric|min:{$min}";
    }

    /**
     * Common validation rules for quantity fields.
     */
    protected function quantityRule(bool $required = false, int $min = 1): string
    {
        return ($required ? 'required|' : 'nullable|') . "integer|min:{$min}";
    }

    /**
     * Common validation rules for percentage fields.
     */
    protected function percentageRule(bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|') . 'numeric|min:0|max:100';
    }

    /**
     * Common validation rules for date fields.
     */
    protected function dateRule(bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|') . 'date';
    }

    /**
     * Common validation rules for future date fields.
     */
    protected function futureDateRule(bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|') . 'date|after_or_equal:today';
    }

    /**
     * Common validation rules for URL fields.
     */
    protected function urlRule(bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|') . 'url|max:500';
    }

    /**
     * Common validation rules for text area fields.
     */
    protected function textAreaRule(bool $required = false, int $max = 5000): string
    {
        return ($required ? 'required|' : 'nullable|') . "string|max:{$max}";
    }

    /**
     * Common validation rules for select/dropdown fields.
     */
    protected function selectRule(array $options, bool $required = false): string
    {
        $optionsList = implode(',', $options);
        return ($required ? 'required|' : 'nullable|') . "in:{$optionsList}";
    }

    /**
     * Common validation rules for foreign key fields.
     */
    protected function foreignKeyRule(string $table, bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|') . "exists:{$table},id";
    }

    /**
     * Common validation rules for unique fields.
     */
    protected function uniqueRule(string $table, string $column, ?int $ignoreId = null): string
    {
        $rule = "unique:{$table},{$column}";
        if ($ignoreId) {
            $rule .= ",{$ignoreId}";
        }
        return $rule;
    }

    /**
     * Common validation rules for file upload fields.
     */
    protected function fileRule(array $mimes = ['pdf', 'doc', 'docx', 'xls', 'xlsx'], int $maxKb = 10240): string
    {
        $mimesList = implode(',', $mimes);
        return "nullable|file|mimes:{$mimesList}|max:{$maxKb}";
    }

    /**
     * Common validation rules for image upload fields.
     */
    protected function imageRule(int $maxKb = 5120): string
    {
        return "nullable|image|mimes:jpeg,png,jpg,gif,webp|max:{$maxKb}";
    }
}
