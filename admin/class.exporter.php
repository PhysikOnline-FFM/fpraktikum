<?php

/**
 * @brief  Class Exporter, exports an array to a file using different formats.
 *
 * @author Lars Gröber
 * @date   22.01.2017
 */
class Exporter
{
    private $data;
    private $head;

    /**
     * @brief Sets the data array.
     *        Has to be called before exporting anything.
     * @param $data array   Data array. [ [$entries_on_same_line],
     *                                    [$entries_on_next_line],...]
     */
    public function init ( $data )
    {
        $this->data = $data;
    }

    /**
     * @brief Sets the header of the columns (each entry corresponds to a column).
     *        Optional.
     * @param $head array
     */
    public function setHead ( $head )
    {
        $this->head = $head;
    }

    /**
     * @brief Creates a plain text file using the data and head array, tab delimited.
     *
     * @param $path string  The path where the file should be created.
     *
     * @return int          Return code, 0 on success, other on failure.
     */
    public function create_plain_file ( $path )
    {
        if ( ! $this->data )
        {
            echo "<p>Error: You have to set the data array before exporting to a file!</p>";
            return 1;
        }

        $file = fopen( $path, "w" );

        if ( ! $file )
        {
            echo "<p>Could not open file '" . $path . "''!</p>";
            return 1;
        }

        if ( isset( $this->head ) )
        {
            // write the header
            fwrite( $file, "# " );
            foreach ( $this->head as $item )
            {
                fwrite( $file, $item . "\t" );
            }
            fwrite( $file, "\n" );
        }

        // write data
        foreach ( $this->data as $line )
        {
            foreach ( $line as $item )
            {
                fwrite( $file, $item . "\t" );
            }
            fwrite( $file, "\n" );
        }

        fclose( $file );

        return 0;
    }
}