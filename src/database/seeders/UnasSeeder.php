<?php

namespace Molitor\Unas\database\seeders;

use Illuminate\Database\Seeder;
use Molitor\Setting\Repositories\SettingGroupRepositoryInterface;
use Molitor\Setting\Repositories\SettingRepositoryInterface;
use Molitor\Unas\Models\UnasShop;
use Molitor\User\Exceptions\PermissionException;
use Molitor\User\Services\AclManagementService;

class UnasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            /** @var AclManagementService $aclService */
            $aclService = app(AclManagementService::class);
            $aclService->createPermission('unas', 'Unas áruházak kezelése', 'admin');
        } catch (PermissionException $e) {
            $this->command->error($e->getMessage());
        }

        /** @var SettingGroupRepositoryInterface $settingRepository */
        $settingGroupRepository = app(SettingGroupRepositoryInterface::class);
        $settingGroup = $settingGroupRepository->create('UNAS', 'unas', 'heroicon-o-building-storefront');

        /** @var SettingRepositoryInterface $settingRepository */
        $settingRepository = app(SettingRepositoryInterface::class);
        $settingRepository->create($settingGroup, 'unas_product_create',false, 'Termék létrehozása', 'checkbox', 'Ha engedélyezve van, akkor a rendszer létrehozza a termékeket az Unas termék alapján.');
    }
}
