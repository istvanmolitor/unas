<?php

namespace Molitor\Unas\Filament\Resources\UnasProductResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Unas\Filament\Resources\UnasProductResource;
use Molitor\Unas\Models\UnasProductAttribute;

class CreateUnasProduct extends CreateRecord
{
    protected static string $resource = UnasProductResource::class;

    public function getBreadcrumb(): string
    {
        return 'Új';
    }

    public function getTitle(): string
    {
        return 'UNAS termék létrehozása';
    }

    protected function afterCreate(): void
    {
        $rows = $this->data['unas_product_attributes_form'] ?? [];
        if (!is_array($rows)) {
            return;
        }

        $seen = [];
        foreach ($rows as $row) {
            $optionId = $row['product_field_option_id'] ?? null;
            if (!empty($optionId) && !isset($seen[$optionId])) {
                $seen[$optionId] = true;
                UnasProductAttribute::create([
                    'unas_product_id' => $this->record->id,
                    'product_field_option_id' => $optionId,
                    'sort' => $row['sort'] ?? 0,
                ]);
            }
        }
    }
}
