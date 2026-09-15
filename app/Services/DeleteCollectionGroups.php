<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class DeleteCollectionGroups
{
    public function handle(EloquentCollection $groups): void
    {
        DB::transaction(function () use ($groups): void {
            foreach ($groups as $group) {
                $collections = $group->collections()->get();

                foreach ($collections as $collection) {
                    $collection->clearMediaCollection();
                    $collection->products()->detach();
                    $collection->channels()->detach();
                    $collection->customerGroups()->detach();
                    $collection->discounts()->detach();
                    $collection->brands()->detach();
                    $collection->urls()->delete();
                }

                if ($collections->isNotEmpty()) {
                    $group->collections()->whereKey($collections->modelKeys())->delete();
                }

                $group->delete();
            }
        });
    }
}
