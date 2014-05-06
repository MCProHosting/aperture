<?php

namespace Mcprohosting\Aperture;

use Illuminate\Database\DatabaseManager;

class Snapshot
{
    protected $db;

    public $handle;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function hasRows($database, $table, $chunk)
    {
        return $this->db->connection($database)->table($table)->count() > 0 ? true : false;
    }

    /**
     * Loads the given table into the file.
     *
     * @param string $database
     * @param string $table
     * @param integer $chunk
     */
    public function take($database, $table, $chunk)
    {
        $this->writeHeader($database, $table);

        return $this->db
            ->connection($database)
            ->table($table)
            ->chunk($chunk, array($this, 'chunkOutToCSV'));
    }

    /**
     * Writes the column names at the head of the csv file.
     *
     * @param string $database
     * @param string $table
     * @return bool|int
     */
    public function writeHeader($database, $table)
    {
        $result = $this->db
            ->connection($database)
            ->table($table)
            ->first();

        if (!$result) {
            return true;
        }

        $keys = array_keys(get_object_vars($result));

        return fputcsv($this->handle, $keys);
    }

    /**
     * Writes the given data out to a CSV file.
     * @param \Illuminate\Database\Eloquent\Collection $data
     */
    public function chunkOutToCSV($data)
    {
        foreach ($data as $item) {
            fputcsv($this->handle, get_object_vars($item));
        }
    }

    /**
     * Restores the file to the given table on the database.
     *
     * @param string $database
     * @param string $table
     * @param integer $chunk
     */
    public function restore($database, $table, $chunk)
    {
        $this->db->connection($database)->table($table)->truncate();

        $columns = fgetcsv($this->handle);

        $spool = array();

        while ($data = fgetcsv($this->handle)) {
            $spool[] = array_combine($columns, $data);

            if (count($spool) >= $chunk) {
                $this->db->connection($database)->table($table)->insert($spool);
                $spool = array();
            }
        }

        if (count($spool)) {
            $this->db->connection($database)->table($table)->insert($spool);
        }
    }
}
