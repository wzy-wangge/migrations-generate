<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 17:23
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\MigrationMethod\IndexType;

class IndexGenerator
{
    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    /**
     * @param  Table  $table
     * @param  bool  $ignoreIndexNames
     * @return Collection[]
     */
    public function generate(Table $table, bool $ignoreIndexNames): array
    {
        $tableName = $table->getName();

        $singleColIndexes = collect([]);
        $multiColIndexes = collect([]);

        foreach ($table->getIndexes() as $index) {
            $indexField = [
                'field' => array_map([$this->decorator, 'addSlash'], $index->getColumns()),
                'type' => IndexType::INDEX,
                'args' => [],
            ];

            if ($index->isPrimary()) {
                $indexField['type'] = IndexType::PRIMARY;
            } elseif ($index->isUnique()) {
                $indexField['type'] = IndexType::UNIQUE;
            } elseif (count($index->getFlags()) > 0 &&
                in_array('spatial', $index->getFlags())) {
                $indexField['type'] = IndexType::SPATIAL_INDEX;
            }

            if (!$ignoreIndexNames && !$this->useLaravelStyleDefaultName($tableName, $index, $indexField['type'])) {
                $indexField['args'][] = $this->decorateName($index->getName());
            }

            if (count($index->getColumns()) === 1) {
                $singleColIndexes->put($this->decorator->addSlash($index->getColumns()[0]), $indexField);
            } else {
                $multiColIndexes->push($indexField);
            }
        }

        return ['single' => $singleColIndexes, 'multi' => $multiColIndexes];
    }

    private function getLaravelStyleDefaultName(string $table, array $columns, string $type): string
    {
        if ($type === IndexType::PRIMARY) {
            return 'PRIMARY';
        }

        $index = strtolower($table.'_'.implode('_', $columns).'_'.$type);
        return str_replace(['-', '.'], '_', $index);
    }

    private function useLaravelStyleDefaultName(string $table, Index $index, string $type): bool
    {
        return $this->getLaravelStyleDefaultName($table, $index->getColumns(), $type) === $index->getName();
    }

    private function decorateName(string $name): string
    {
        return "'".$this->decorator->addSlash($name)."'";
    }
}
