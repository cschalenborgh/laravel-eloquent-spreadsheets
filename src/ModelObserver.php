<?php

namespace Rhinodontypicus\EloquentSpreadsheets;

use Rhinodontypicus\EloquentSpreadsheets\Jobs\CreateModelInSpreadsheet;
use Rhinodontypicus\EloquentSpreadsheets\Jobs\DeleteModelInSpreadsheet;
use Rhinodontypicus\EloquentSpreadsheets\Jobs\UpdateModelInSpreadsheet;

class ModelObserver
{
    public function created($model)
    {
        $config = $this->getConfig($model);
        $job = (new CreateModelInSpreadsheet($config['model'], $config))->onQueue($config['queue_name']);
        dispatch($job);
    }

    public function updated($model)
    {
        $config = $this->getConfig($model);

        if (! $this->shouldBeUpdated($config['model'], $config)) {
            return;
        }

        $job = (new UpdateModelInSpreadsheet($config['model'], $config))->onQueue($config['queue_name']);
        dispatch($job);
    }

    public function deleted($model)
    {
        $config = $this->getConfig($model);
        $job = (new DeleteModelInSpreadsheet($config['model']->id, $config))->onQueue($config['queue_name']);
        dispatch($job);
    }

    /**
     * @param $model
     * @return mixed
     */
    public function getConfig($model)
    {
        return config('laravel-eloquent-spreadsheets')['sync_models'][$model]['model'];
    }

    /**
     * @param $model
     * @param $config
     * @return bool
     */
    private function shouldBeUpdated($model, $config)
    {

        $touchedKeys = array_intersect(array_keys($config['sync_attributes']), array_keys($config['model']->getDirty()));

        return count($touchedKeys) >= 1;
    }
}
