<?php
/**
 * Created by PhpStorm.
 * User: Christian
 * Date: 28.12.2016
 * Time: 21:09
 *
 * General Play ground for tests
 */
foreach ($graduation_array as $key => $graduation) {
    $result[$graduation] = [];

    // loop through semesterhälfte
    for ($semester_half=0; $semester_half <= 1; $semester_half++) {

        // loop through institut
        $stmt_courses->execute();
        $stmt_courses->store_result();
        $stmt_courses->bind_result($institute, $max_slots);
        while ($stmt_courses->fetch()) {

            $stmt_angebote_remaining->execute();
            $stmt_angebote_remaining->bind_result($slots_remaining);
            $stmt_angebote_remaining->fetch();

            $slots_remaining = ($slots_remaining == NULL) ? $max_slots : $slots_remaining;

            $result[$graduation][$institute][$semester_half] = $slots_remaining;

            $stmt_angebote_remaining->store_result();


            /