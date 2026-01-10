<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateMaintenanceJobsAssetsTable extends AbstractMigration
{
    public function change(): void
    {
        // Create maintenanceJobsAssets junction table for normalized asset tracking
        $this->table('maintenanceJobsAssets', ['id' => false, 'primary_key' => 'maintenanceJobsAssets_id'])
            ->addColumn('maintenanceJobsAssets_id', 'integer', [
                'autoIncrement' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('maintenanceJobs_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Foreign key to maintenanceJobs',
            ])
            ->addColumn('assets_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Foreign key to assets',
            ])
            ->addColumn('maintenanceJobsAssets_quantity', 'integer', [
                'null' => false,
                'default' => 1,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Total quantity of this asset in maintenance job',
            ])
            ->addColumn('maintenanceJobsAssets_quantityInMaintenance', 'integer', [
                'null' => false,
                'default' => 1,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Quantity currently blocked from project assignment (in maintenance)',
            ])
            ->addColumn('maintenanceJobsAssets_deleted', 'boolean', [
                'null' => false,
                'default' => false,
                'comment' => 'Soft delete flag',
            ])
            ->addIndex(['maintenanceJobs_id'], ['name' => 'maintenanceJobsAssets_jobs_id_fk_idx'])
            ->addIndex(['assets_id'], ['name' => 'maintenanceJobsAssets_assets_id_fk_idx'])
            ->addIndex(['maintenanceJobs_id', 'assets_id'], ['name' => 'uk_maintenanceJobsAssets_job_asset', 'unique' => true])
            ->addForeignKey('maintenanceJobs_id', 'maintenanceJobs', 'maintenanceJobs_id', [
                'constraint' => 'maintenanceJobsAssets_maintenanceJobs_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('assets_id', 'assets', 'assets_id', [
                'constraint' => 'maintenanceJobsAssets_assets_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
    }
}
