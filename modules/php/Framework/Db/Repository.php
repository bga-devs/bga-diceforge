<?php

namespace Bga\Games\diceforge\Framework\Db;

use Bga\Games\diceforge\Framework\Orm\Column;
use Bga\Games\diceforge\Framework\Orm\Entity;
use Bga\Games\diceforge\Framework\Orm\Id;

/**
 * Generic repository providing find / findAll / save for any entity class
 * annotated with #[Entity], #[Column] and #[Id].
 *
 * @template T of object
 *
 * Usage:
 *   $playerRepo = new Repository(Player::class, $db);
 *   $player = $playerRepo->find($id);          // T
 *   $playerRepo->save($player);                 // void
 *   $players = $playerRepo->findAll();          // list<T>
 *
 * For better IDE type hints, optionally create a typed subclass:
 *   class PlayerRepository extends Repository {
 *       public function __construct(Db $db) { parent::__construct(Player::class, $db); }
 *       public function find(int $id): ?Player { return parent::find($id); }
 *       public function findAll(): array { return parent::findAll(); }  // IDE infers Player[]
 *   }
 */
class Repository
{
    /**
     * Cache for entity metadata to avoid redundant reflection.
     * @var array<class-string, array{table: string, idProp: string, idCol: string, cols: array<string, string>}>
     */
    private static array $metaCache = [];

    /**
     * @param class-string<T> $entityClass The entity class to manage (e.g., Player::class)
     */
    public function __construct(
        private readonly string $entityClass,
        protected readonly Db $db
    ) {
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * @return T|null
     */
    public function find(int $id): ?object
    {
        $meta = $this->meta();
        $row  = $this->db->getObjectFromDB(
            "SELECT * FROM `{$meta['table']}` WHERE `{$meta['idCol']}` = " . (int) $id
        );
        return empty($row) ? null : $this->hydrate($row, $meta);
    }

    /**
     * Find an entity by multiple criteria (useful for composite keys or unique constraints).
     * Usage: $stat = $repo->findBy(['stats_type' => 'nb_vp_die', 'stats_player_id' => 42]);
     *
     * @param array<string, mixed> $criteria Column name => value pairs
     * @return T|null
     */
    public function findBy(array $criteria): ?object
    {
        if (empty($criteria)) {
            return null;
        }

        $meta = $this->meta();
        $whereParts = [];
        foreach ($criteria as $colName => $value) {
            if ($value === null) {
                $whereParts[] = "`$colName` IS NULL";
            } else {
                $escapedValue = "'" . $this->db->escape((string) $value) . "'";
                $whereParts[] = "`$colName` = $escapedValue";
            }
        }

        $row = $this->db->getObjectFromDB(
            "SELECT * FROM `{$meta['table']}` WHERE " . implode(' AND ', $whereParts)
        );
        return empty($row) ? null : $this->hydrate($row, $meta);
    }

    /**
     * @return list<T>
     */
    public function findAll(): array
    {
        $meta = $this->meta();
        $rows = $this->db->getObjectListFromDB("SELECT * FROM `{$meta['table']}` ORDER BY `{$meta['idCol']}`");
        return array_map(fn (array $row) => $this->hydrate($row, $meta), $rows);
    }

    /**
     * INSERT or UPDATE the entity (upsert via ON DUPLICATE KEY UPDATE).
     * The entity must have all #[Column] properties set before calling save().
     *
     * @param T $entity
     */
    public function save(object $entity): void
    {
        $meta = $this->meta();
        $ref  = new \ReflectionClass($this->entityClass);

        /** @var array<string, mixed> $allValues colName => value */
        $allValues = [];
        foreach ($meta['cols'] as $propName => $colName) {
            $prop = $ref->getProperty($propName);
            $allValues[$colName] = $prop->getValue($entity);
        }

        $escape  = fn (mixed $v): string => $v === null ? 'NULL' : "'" . $this->db->escape((string) $v) . "'";
        $colList = implode(', ', array_map(fn ($c) => "`$c`", array_keys($allValues)));
        $valList = implode(', ', array_map($escape, array_values($allValues)));

        $updateParts = [];
        foreach ($allValues as $colName => $value) {
            if ($colName === $meta['idCol']) {
                continue;
            }
            $updateParts[] = "`$colName` = " . $escape($value);
        }

        $this->db->DbQuery(
            "INSERT INTO `{$meta['table']}` ($colList) VALUES ($valList)"
            . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updateParts)
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Reads the ORM attributes of the entity class and returns the mapping.
     * Results are cached to avoid redundant reflection on repeated calls.
     *
     * @return array{table: string, idProp: string, idCol: string, cols: array<string, string>}
     */
    private function meta(): array
    {
        $class = $this->entityClass;

        // Return cached metadata if available
        if (isset(self::$metaCache[$class])) {
            return self::$metaCache[$class];
        }

        $ref   = new \ReflectionClass($class);

        $entityAttrs = $ref->getAttributes(Entity::class);
        if (empty($entityAttrs)) {
            throw new \LogicException("$class is missing #[Entity] attribute");
        }
        $table = $entityAttrs[0]->newInstance()->table;

        $idProp = null;
        $idCol  = null;
        /** @var array<string, string> $cols propName => colName */
        $cols = [];

        foreach ($ref->getProperties() as $prop) {
            $colAttrs = $prop->getAttributes(Column::class);
            if (empty($colAttrs)) {
                continue;
            }
            $colName  = $colAttrs[0]->newInstance()->name;
            $propName = $prop->getName();
            $cols[$propName] = $colName;

            if (!empty($prop->getAttributes(Id::class))) {
                $idProp = $propName;
                $idCol  = $colName;
            }
        }

        if ($idProp === null) {
            throw new \LogicException("$class has no property marked with #[Id]");
        }

        // Cache and return the metadata
        return self::$metaCache[$class] = compact('table', 'idProp', 'idCol', 'cols');
    }

    /**
     * Builds an entity instance from a raw DB row without calling the constructor,
     * so that the ORM works regardless of constructor signature.
     */
    private function hydrate(array $row, array $meta): object
    {
        $class  = $this->entityClass;
        $ref    = new \ReflectionClass($class);
        $entity = $ref->newInstanceWithoutConstructor();

        foreach ($meta['cols'] as $propName => $colName) {
            if (!array_key_exists($colName, $row)) {
                continue;
            }
            $prop  = $ref->getProperty($propName);
            $value = $row[$colName];
            $type  = $prop->getType();
            if ($value !== null && $type instanceof \ReflectionNamedType && $type->isBuiltin()) {
                settype($value, $type->getName());
            }
            $prop->setValue($entity, $value);
        }

        return $entity;
    }
}
