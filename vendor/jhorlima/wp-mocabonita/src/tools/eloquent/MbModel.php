<?php

namespace MocaBonita\tools\eloquent;

use InvalidArgumentException;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use MocaBonita\tools\MbMigration;
use MocaBonita\tools\MbPivot;
use MocaBonita\tools\validation\MbValidation;
use Illuminate\Support\Arr;

/**
 * Main class of the MocaBonita Model
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools\eloquent
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbModel extends Model
{

    /**
     * Add wordpressPrefix table name
     *
     * @var bool
     */
    protected $wordpressPrefix = false;

    /**
     * Define whether you need to validate the data
     *
     * @var bool
     */
    protected $validation = true;

    /**
     * Wordpress DB Manager
     *
     * @return \wpdb
     */
    protected function getWpdb()
    {
        global $wpdb;

        return $wpdb;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->table;
        }

        $wpPrefix = $this->getWpdb()->prefix;

        $table = str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));

        return $this->wordpressPrefix ? $wpPrefix . $table : $table;
    }

    /**
     * New base query builder
     *
     * @return MbDatabaseQueryBuilder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new MbDatabaseQueryBuilder($conn, $grammar, $conn->getPostProcessor());
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|MbDatabaseEloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new MbDatabaseEloquentBuilder($query);
    }

    /**
     *  Implement method to create schemas
     *
     * @use https://laravel.com/docs/5.2/migrations#creating-tables
     *
     * @param Blueprint $table
     *
     * @throws \Exception
     */
    public function createSchema(Blueprint $table)
    {
        throw new \Exception("The createSchema method was not implemented");
    }

    /**
     * Call createSchema when activating, deactivating or uninstalling plugin
     *
     * @param bool $deleteIfExists To recreate scheme
     *
     */
    public final static function createSchemaModel($deleteIfExists = false)
    {
        $model = new static();

        if (!MbMigration::schema()->hasTable($model->getTable())) {
            MbMigration::schema()->create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->engine = 'InnoDB';
                $model->createSchema($table);
            });
        } elseif ($deleteIfExists) {
            self::dropSchemaModel();
            MbMigration::schema()->create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->engine = 'InnoDB';
                $model->createSchema($table);
            });
        }
    }

    /**
     *  Implement method to update schemas
     *
     * @use https://laravel.com/docs/5.2/migrations#creating-tables
     *
     * @param Blueprint $table
     *
     * @throws \Exception
     */
    public function updateSchema(Blueprint $table)
    {
        throw new \Exception("The updateSchema method was not implemented");
    }

    /**
     * Call updateSchema when activating, deactivating or uninstalling plugin
     *
     * @throws \Exception
     */
    public final static function updateSchemaModel()
    {

        $model = new static();

        if (MbMigration::schema()->hasTable($model->getTable())) {
            MbMigration::schema()->table($model->getTable(), function (Blueprint $table) use ($model) {
                $table->engine = 'InnoDB';
                $model->updateSchema($table);
            });
        } else {
            throw new \Exception("Schema {$model->getTable()} was not found");
        }
    }

    /**
     * Call delete scheme
     *
     */
    public final static function dropSchemaModel()
    {
        $model = new static();
        MbMigration::schema()->dropIfExists($model->getTable());
    }

    /**
     * Save the model to the database.
     *
     * @param  array $options
     *
     * @return bool
     * @throws \MocaBonita\tools\MbException
     */
    public function save(array $options = [])
    {
        $this->validation = Arr::get($options, 'validation', $this->validation);

        if ($this->validation) {

            $attributes = $this->getAttributes();

            $validation = $this->validation($attributes);

            if ($validation instanceof MbValidation) {
                $validation->check(true);
                $attributes = $validation->getData();
            } elseif (is_array($validation)) {
                $attributes = $validation;
            }

            $this->fill($attributes);
        }

        return parent::save($options);
    }

    /**
     * Implement method to validation model
     *
     * @param array $attributes
     *
     * @return array|MbValidation|null
     */
    protected function validation(array $attributes)
    {
        return null;
    }

    /**
     * Get TableSchema Builder
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public final function getSchema()
    {
        return MbMigration::schema();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed $value
     *
     * @return \Carbon\Carbon
     */
    protected function asDateTime($value)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return new Carbon(
                $value->format('Y-m-d H:i:s.u'), $value->getTimeZone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        try {
            return Carbon::createFromFormat($this->getDateFormat(), $value);
        } catch (InvalidArgumentException $e) {
            return Carbon::parse($value);
        }
    }

    /**
     * Create a new pivot model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model $parent
     * @param  array                               $attributes
     * @param  string                              $table
     * @param  bool                                $exists
     *
     * @param null                                 $using
     *
     * @return MbPivot
     */
    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        return new MbPivot($parent, $attributes, $table, $exists);
    }
}