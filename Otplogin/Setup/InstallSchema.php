<?php

/**
 * Cinovic Technologies LLP.
 *
 * @category  Cinovic
 * @package   Cinovic_Otplogin
 * @author    Cinovic Technologies LLP
 * @copyright Copyright (c) Cinovic Technologies LLP (https://cinovic.com)
 * @license   https://store.cinovic.com/license.html
 */


namespace Cinovic\Otplogin\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('mobile_otp');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'customer',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'customer'
                )
                ->addColumn(
                    'otp',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'otp'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    'status'
                )->addColumn(
                    'created_at',
        						\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        						null,
        						['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
        						'Created At'
                );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
