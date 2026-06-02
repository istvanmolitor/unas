<?php

namespace Molitor\Unas\database\seeders;

use Illuminate\Database\Seeder;
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
        $permissionGroupName = 'UNAS';

        try {
            /** @var AclManagementService $aclService */
            $aclService = app(AclManagementService::class);
            $aclService->createPermission('unas', 'Unas áruházak kezelése', 'admin', $permissionGroupName);
        } catch (PermissionException $e) {
            $this->command->error($e->getMessage());
        }
    }
}
