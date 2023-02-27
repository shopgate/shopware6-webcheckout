<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\System\Db\Installers;

use Shopgate\WebcheckoutSW6\System\Db\ClassCastInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class EntityInstaller
{
    protected array $entityInstallList = [];
    protected string $entityName;
    /** @var EntityRepositoryInterface */
    protected $entityRepo;

    public function __construct(ContainerInterface $container)
    {
        $this->entityRepo = $container->get($this->entityName . '.repository');
    }

    public function install(InstallContext $context): void
    {
        foreach ($this->getEntities() as $method) {
            $this->upsertEntity($method, $context->getContext());
        }
    }

    /**
     * @return ClassCastInterface[]
     */
    protected function getEntities(): array
    {
        return array_map(static function (string $method) {
            return new $method();
        }, $this->entityInstallList);
    }

    protected function upsertEntity(ClassCastInterface $entity, Context $context): void
    {
        $data = $entity->toArray();
        $existingEntity = $this->findEntity($entity->getId(), $context);
        if (null !== $existingEntity) {
            $this->updateEntity($data, $context);
        } else {
            $this->installEntity($data, $context);
        }
    }

    /**
     * @return object|null
     */
    protected function findEntity(string $id, Context $context): ?object
    {
        return $this->entityRepo->search(new Criteria([$id]), $context)->first();
    }

    protected function updateEntity(array $data, Context $context): void
    {
        $this->entityRepo->update([$data], $context);
    }

    protected function installEntity(array $info, Context $context): void
    {
        $this->entityRepo->create([$info], $context);
    }
}
