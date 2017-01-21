<?php

/**
 * Class Helper, a static class to implement helper functions.
 */
class Helper
{
    /**
     * Function to "calculate" the upcoming semester.
     * The registration opens always at the beginning of the semester.
     *
     * @return string
     */
    static public function get_semester ()
    {
        $current_month = (int)date( "m" );
        // between January and June we want to prepare for the summer, otherwise for the winter semester
        if ( $current_month > 0 && $current_month < 7 )
        {
            $semester = "SS";
        }
        else
        {
            $semester = "WS";
        }
        $semester .= date( "y" ); // adds the current year in '##' format

        return $semester;
    }
}