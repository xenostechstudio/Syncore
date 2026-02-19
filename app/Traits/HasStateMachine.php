<?php

namespace App\Traits;

use BackedEnum;

/**
 * HasStateMachine Trait
 * 
 * Provides consistent state machine functionality across models with status/state fields.
 * Automatically logs status changes when used with LogsActivity trait.
 * 
 * Usage:
 * ```php
 * class Invoice extends Model
 * {
 *     use HasStateMachine, LogsActivity;
 *     
 *     // Required: Define the state enum class
 *     protected string $stateEnum = InvoiceState::class;
 *     
 *     // Optional: Define the status column (defaults to 'status')
 *     protected string $statusColumn = 'status';
 * }
 * ```
 */
trait HasStateMachine
{
    /**
     * Get the status column name.
     */
    public function getStatusColumn(): string
    {
        return $this->statusColumn ?? 'status';
    }

    /**
     * Get the state enum class.
     */
    public function getStateEnumClass(): string
    {
        return $this->stateEnum ?? throw new \RuntimeException(
            'State enum class not defined. Set protected string $stateEnum in your model.'
        );
    }

    /**
     * Get the current state as an enum instance.
     */
    public function getStateAttribute(): BackedEnum
    {
        $enumClass = $this->getStateEnumClass();
        $column = $this->getStatusColumn();
        $value = $this->{$column};

        return $enumClass::tryFrom($value) ?? $enumClass::cases()[0];
    }

    /**
     * Transition to a new state with automatic logging.
     *
     * @param BackedEnum $state The target state
     * @param bool $withLogging Whether to log the status change (default: true)
     * @return bool Whether the transition was successful
     */
    public function transitionTo(BackedEnum $state, bool $withLogging = true): bool
    {
        $column = $this->getStatusColumn();
        $oldStatus = $this->{$column};
        $newStatus = $state->value;

        // Skip if already in this state
        if ($oldStatus === $newStatus) {
            return true;
        }

        $this->{$column} = $newStatus;
        $saved = $this->save();

        // Log status change if LogsActivity trait is used
        if ($saved && $withLogging && method_exists($this, 'logStatusChange')) {
            $this->logStatusChange($oldStatus ?? 'none', $newStatus);
        }

        return $saved;
    }

    /**
     * Check if the model can transition to a given state.
     * Override this method in your model for custom validation.
     *
     * @param BackedEnum $state The target state
     * @return bool
     */
    public function canTransitionTo(BackedEnum $state): bool
    {
        // By default, check if current state is not terminal
        if (method_exists($this->state, 'isTerminal') && $this->state->isTerminal()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the current state is terminal (no further transitions allowed).
     */
    public function isTerminalState(): bool
    {
        return method_exists($this->state, 'isTerminal') && $this->state->isTerminal();
    }

    /**
     * Check if the model can be edited based on current state.
     */
    public function canEdit(): bool
    {
        return method_exists($this->state, 'canEdit') && $this->state->canEdit();
    }

    /**
     * Check if the model can be cancelled based on current state.
     */
    public function canCancel(): bool
    {
        return method_exists($this->state, 'canCancel') && $this->state->canCancel();
    }
}
