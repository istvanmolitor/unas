<?php

namespace Molitor\Unas\Filament\Resources\UnasProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Molitor\Unas\Filament\Actions\UnasProductActions;
use Molitor\Unas\Filament\Resources\UnasProductResource;
use Molitor\Unas\Models\UnasProductAttribute;

class EditUnasProduct extends EditRecord
{
    protected static string $resource = UnasProductResource::class;

    public function getBreadcrumb(): string
    {
        return 'Szerkesztés';
    }

    public function getTitle(): string
    {
        return 'UNAS termék szerkesztése';
    }

    protected function getHeaderActions(): array
    {
        return [
            UnasProductActions::make(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $attributes = UnasProductAttribute::query()
            ->where('unas_product_id', $this->record->id)
            ->with('productFieldOption')
            ->get();

        $data['unas_product_attributes_form'] = $attributes->map(function (UnasProductAttribute $value) {
            return [
                'product_field_id' => optional($value->productFieldOption)->product_field_id,
                'product_field_option_id' => $value->product_field_option_id,
                'sort' => $value->sort,
            ];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        UnasProductAttribute::query()->where('unas_product_id', $this->record->id)->delete();

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
