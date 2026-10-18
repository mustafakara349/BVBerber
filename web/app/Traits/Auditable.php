<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::logAudit($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (!empty($dirty)) {
                $old = array_intersect_key($model->getOriginal(), $dirty);
                self::logAudit($model, 'updated', $old, $dirty);
            }
        });

        static::deleted(function ($model) {
            self::logAudit($model, 'deleted', $model->getOriginal(), null);
        });
    }

    protected static function logAudit($model, string $action, ?array $oldValues, ?array $newValues): void
    {
        $modelType = get_class($model);
        $modelId = $model->getKey();
        $actionName = strtolower(class_basename($model)) . '.' . $action;

        AuditLog::record(
            action: $actionName,
            modelType: $modelType,
            modelId: is_numeric($modelId) ? (int)$modelId : 0,
            oldValues: $oldValues,
            newValues: $newValues,
            userId: Auth::id(),
            status: 'success',
            details: "Model {$action} event for {$modelType} #{$modelId}"
        );
    }
}
