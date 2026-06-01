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
        try {
            /** @var AclManagementService $aclService */
            $aclService = app(AclManagementService::class);
            $aclService->createPermission('UNAS', 'Unas áruházak kezelése', 'admin', 'UNAS');
        } catch (PermissionException $e) {
            $this->command->error($e->getMessage());
        }
    }
}
