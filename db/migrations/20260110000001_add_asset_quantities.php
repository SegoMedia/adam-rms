<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddAssetQuantities extends AbstractMigration
{
    public function change(): void
    {
        // Add quantity tracking to assets table
        $this->table('assets')
            ->addColumn('assets_isQuantityBased', 'boolean', [
                'null' => false,
                'default' => false,
                'after' => 'assets_deleted',
                'comment' => 'Whether this asset tracks quantity (bulk) vs individual serialized items',
            ])
            ->addColumn('assets_quantity', 'integer', [
                'null' => false,
                'default' => 1,
                'after' => 'assets_isQuantityBased',
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Total quantity of this asset if bulk; 1 if serialized',
            ])
            ->update();

        // Add quantity tracking to assetsAssignments table
        $this->table('assetsAssignments')
            ->addColumn('assetsAssignments_quantity', 'integer', [
                'null' => false,
                'default' => 1,
                'after' => 'assetsAssignments_linked',
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Quantity of this asset assigned to the project',
            ])
            ->update();
    }
}
