<?php

namespace app\service;

use app\repository\MenuRepository;
use app\repository\RoleRepository;
use DI\Attribute\Inject;
use Illuminate\Support\Collection;

/**
 * @extends IService<RoleRepository>
 */
final class RoleService extends IService
{
    #[Inject]
    public RoleRepository $repository;

    #[Inject]
    protected MenuRepository $menuRepository;

    public function getRolePermission(int $id): Collection
    {
        // @phpstan-ignore-next-line
        return $this->repository->findById($id)->menus()->get();
    }

    public function batchGrantPermissionsForRole(int $id, array $permissionsCode): void
    {
        if (\count($permissionsCode) === 0) {
            // @phpstan-ignore-next-line
            $this->repository->findById($id)->menus()->detach();
            return;
        }
        // @phpstan-ignore-next-line
        $this->repository->findById($id)
            ->menus()
            ->sync(
                $this->menuRepository
                    ->list([
                        'code' => $permissionsCode,
                    ])
                    ->map(static fn($item) => $item->id)
                    ->toArray()
            );
    }
}
