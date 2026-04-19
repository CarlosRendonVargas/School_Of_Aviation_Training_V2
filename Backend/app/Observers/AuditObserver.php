<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Services\AuditService;

class AuditObserver
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function created(Model $model): void
    {
        $this->auditService->insercion($model);
    }

    public function updated(Model $model): void
    {
        $this->auditService->actualizacion($model);
    }

    public function deleted(Model $model): void
    {
        $this->auditService->eliminacion($model);
    }
}
