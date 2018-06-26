<?php
namespace TotalExpert\BernardScheduler\Driver\Doctrine;

use Doctrine\DBAL\Schema\Schema;

class ScheduleSchema
{
    /**
     * Create the schema needed for the Doctrine Driver.
     *
     * @param Schema $schema
     */
    public static function create(Schema $schema)
    {
        $table = $schema->createTable('bernard_schedule');

        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
            'unsigned' => true,
            'notnull' => true,
        ]);
        $table->addColumn('visible', 'boolean', ['default' => true]);
        $table->addColumn('job', 'text');
        $table->addColumn('enqueue_at', 'integer');
        $table->addColumn('created_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['enqueue_at', 'created_at', 'visible']);
    }
}
