<?php

namespace App\Traits;

use App\Services\ActivityLogService;

/**
 * LogsActivity Trait
 * 
 * Automatically logs model lifecycle events (created, updated, deleted) to the activity_logs table.
 * Uses the ActivityLogService for centralized logging functionality.
 * 
 * Usage:
 * ```php
 * class MyModel extends Model
 * {
 *     use LogsActivity;
 *     
 *     // Optional: customize which actions to log (defaults to all)
 *     protected array $logActions = ['created', 'updated', 'deleted'];
 * }
 * ```
 * 
 * @package App\Traits
 */
trait LogsActivity
{
    /**
     * Temporary storage for original values during update.
     * Using a static array keyed by model class and ID to avoid attribute conflicts.
     */
    protected static array $originalValuesCache = [];

    /**
     * Boot the LogsActivity trait.
     * Registers model event listeners for automatic activity logging.
     *
     * @return void
     */
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            if ($model->shouldLogActivity('created')) {
                ActivityLogService::logCreated($model);
            }
        });

        static::updating(function ($model) {
            // Store original values before update for change tracking
            // Use a static cache to avoid polluting model attributes
            $cacheKey = get_class($model) . ':' . $model->getKey();
            static::$originalValuesCache[$cacheKey] = $model->getOriginal();
        });

        static::updated(function ($model) {
            if ($model->shouldLogActivity('updated')) {
                $cacheKey = get_class($model) . ':' . $model->getKey();
                $oldValues = static::$originalValuesCache[$cacheKey] ?? [];
                unset(static::$originalValuesCache[$cacheKey]); // Clean up
                ActivityLogService::logUpdated($model, $oldValues);
            }
        });

        static::deleted(function ($model) {
            if ($model->shouldLogActivity('deleted')) {
                ActivityLogService::logDeleted($model);
            }
        });
    }

    /**
     * Determine if the given action should be logged.
     * Override the $logActions property in your model to customize.
     *
     * @param string $action The action to check ('created', 'updated', 'deleted')
     * @return bool Whether the action should be logged
     */
    protected function shouldLogActivity(string $action): bool
    {
        $logActions = $this->logActions ?? ['created', 'updated', 'deleted'];
        
        return in_array($action, $logActions);
    }

    /**
     * Log a custom activity for this model.
     *
     * @param string $action The custom action name
     * @param string|null $description Custom description (optional)
     * @param array $properties Additional properties to store (optional)
     * @return void
     */
    public function logActivity(string $action, ?string $description = null, array $properties = []): void
    {
        ActivityLogService::logAction($action, $this, $description, $properties);
    }

    /**
     * Log a status change for this model.
     *
     * @param string $oldStatus The previous status value
     * @param string $newStatus The new status value
     * @param string|null $description Custom description (optional)
     * @return void
     */
    public function logStatusChange(string $oldStatus, string $newStatus, ?string $description = null): void
    {
        ActivityLogService::logStatusChanged($this, $oldStatus, $newStatus, $description);
    }

    /**
     * Get all activities for this model instance.
     *
     * @param int $limit Maximum number of activities to return
     * @return \Illuminate\Support\Collection
     */
    public function getActivities(int $limit = 20): \Illuminate\Support\Collection
    {
        return ActivityLogService::getActivitiesFor($this, $limit);
    }
}
