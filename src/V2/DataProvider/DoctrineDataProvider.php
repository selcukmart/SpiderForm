<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FormGenerator\V2\Contracts\DataProviderInterface;

/**
 * Doctrine ORM Data Provider
 *
 * @author selcukmart
 * @since 2.0.0
 */
class DoctrineDataProvider implements DataProviderInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $entityClass
    ) {
        $this->repository = $this->entityManager->getRepository($this->entityClass);
    }

    /**
     * Fetch single row by identifier
     */
    public function findById(mixed $id): ?array
    {
        $entity = $this->repository->find($id);

        if ($entity === null) {
            return null;
        }

        return $this->entityToArray($entity);
    }

    /**
     * Fetch multiple rows with optional criteria
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $entities = $this->repository->findBy($criteria, $orderBy, $limit, $offset);

        return array_map(fn($entity) => $this->entityToArray($entity), $entities);
    }

    /**
     * Fetch all rows
     */
    public function findAll(): array
    {
        $entities = $this->repository->findAll();

        return array_map(fn($entity) => $this->entityToArray($entity), $entities);
    }

    /**
     * Execute custom query (DQL)
     */
    public function query(string $query, array $parameters = []): array
    {
        $queryBuilder = $this->entityManager->createQuery($query);

        foreach ($parameters as $key => $value) {
            $queryBuilder->setParameter($key, $value);
        }

        $results = $queryBuilder->getResult();

        return array_map(function ($result) {
            if (is_object($result)) {
                return $this->entityToArray($result);
            }
            return $result;
        }, $results);
    }

    /**
     * Get options for select/radio/checkbox
     */
    public function getOptions(
        string $keyColumn,
        string $labelColumn,
        array $criteria = []
    ): array {
        $entities = $this->repository->findBy($criteria);
        $options = [];

        $keyGetter = 'get' . ucfirst($keyColumn);
        $labelGetter = 'get' . ucfirst($labelColumn);

        foreach ($entities as $entity) {
            if (method_exists($entity, $keyGetter) && method_exists($entity, $labelGetter)) {
                $key = $entity->$keyGetter();
                $label = $entity->$labelGetter();
                $options[$key] = $label;
            }
        }

        return $options;
    }

    /**
     * Check if data provider is connected/ready
     */
    public function isReady(): bool
    {
        try {
            return $this->entityManager->isOpen();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get provider type identifier
     */
    public function getType(): string
    {
        return 'doctrine';
    }

    /**
     * Convert entity to array
     */
    private function entityToArray(object $entity): array
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));
        $data = [];

        // Get field values
        foreach ($metadata->getFieldNames() as $fieldName) {
            $getter = 'get' . ucfirst($fieldName);
            if (method_exists($entity, $getter)) {
                $value = $entity->$getter();

                // Handle DateTime objects
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                $data[$fieldName] = $value;
            }
        }

        // Get association values (simple ones)
        foreach ($metadata->getAssociationNames() as $assocName) {
            if ($metadata->isSingleValuedAssociation($assocName)) {
                $getter = 'get' . ucfirst($assocName);
                if (method_exists($entity, $getter)) {
                    $assocEntity = $entity->$getter();
                    if ($assocEntity !== null) {
                        // Store just the ID for associated entities
                        $assocMetadata = $this->entityManager->getClassMetadata(get_class($assocEntity));
                        $idGetter = 'get' . ucfirst($assocMetadata->getSingleIdentifierFieldName());
                        if (method_exists($assocEntity, $idGetter)) {
                            $data[$assocName . '_id'] = $assocEntity->$idGetter();
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get Entity Manager
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Get Repository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }
}
