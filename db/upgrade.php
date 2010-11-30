<?php 

function xmldb_checklist_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager();
    $result = true;

    if ($result && $oldversion < 2010022500) {
        // Adjust (currently unused) 'teachermark' fields to be 0 when unmarked, not 2
        $sql = 'UPDATE {checklist_check} ';
        $sql .= 'SET teachermark=0 ';
        $sql .= 'WHERE teachermark=2';
        $DB->execute($sql);

        upgrade_mod_savepoint($result, 2010022500, 'checklist');
    }

    if ($result && $oldversion < 2010022800) {
        // All checklists created before this point were 'student only' checklists
        // Update the default & previously created checklists to reflect this
        
        $sql = 'UPDATE {checklist} ';
        $sql .= 'SET teacheredit=0 ';
        $sql .= 'WHERE teacheredit=2';
        $DB->execute($sql);
        
        $table = new xmldb_table('checklist');
        $field = new xmldb_field('teacheredit', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, '0', 'useritemsallowed');
        $dbman->change_field_type($table, $field);
    
        /// checklist savepoint reached
        upgrade_mod_savepoint($result, 2010022800, 'checklist');
    }

    if ($result && $oldversion < 2010031600) {
        notify('Processing checklist grades, this may take a while if there are many checklists...', 'notifysuccess');

        require_once(dirname(dirname(__FILE__)).'/lib.php');

        // too much debug output
        $olddebug = $DB->get_debug();
        $DB->set_debug(false);
        checklist_update_all_grades();
        $DB->set_debug($olddebug);

        /// checklist savepoint reached
        upgrade_mod_savepoint($result, 2010031600, 'checklist');
    }

    if ($result && $oldversion < 2010041800) {
        $table = new xmldb_table('checklist_item');
        $field = new xmldb_field('duetime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0', 'itemoptional');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        /// checklist savepoint reached
        upgrade_mod_savepoint($result, 2010041800, 'checklist');
    }

    if ($result && $oldversion < 2010041801) {
        $table = new xmldb_table('checklist');
        $field = new xmldb_field('duedatesoncalendar', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, '0', 'theme');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        /// checklist savepoint reached
        upgrade_mod_savepoint($result, 2010041801, 'checklist');
    }

    if ($result && $oldversion < 2010041900) {

        /// Define field eventid to be added to checklist_item
        $table = new xmldb_table('checklist_item');
        $field = new xmldb_field('eventid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0', 'duetime');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        /// checklist savepoint reached
        upgrade_mod_savepoint($result, 2010041900, 'checklist');
    }

    if ($result && $oldversion < 2010050100) {

        /// Define field teachercomments to be added to checklist
        $table = new xmldb_table('checklist');
        $field = new xmldb_field('teachercomments', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, '1', 'duedatesoncalendar');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        /// Define table checklist_comment to be created
        $table = new xmldb_table('checklist_comment');

        /// Adding fields to table checklist_comment
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('commentby', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0');
        $table->add_field('text', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);

        /// Adding keys to table checklist_comment
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        /// Adding indexes to table checklist_comment
        $table->add_index('checklist_item_user', XMLDB_INDEX_UNIQUE, array('itemid', 'userid'));

        /// Conditionally launch create table for checklist_comment
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        /// checklist savepoint reached
        upgrade_mod_savepoint($result, 2010050100, 'checklist');
    }

    if ($result && $oldversion < 2010091003) {
        $table = new xmldb_table('checklist_item');
        $field = new xmldb_field('colour', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, 'black');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint($result, 2010091003, 'checklist');
    }

    if ($result && $oldversion < 2010102703) {
        $table = new xmldb_table('checklist');
        $field = new xmldb_field('maxgrade', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '100');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint($result, 2010102703, 'checklist');
    }
        
    if ($result && $oldversion < 2010112000) {
        $table = new xmldb_table('checklist');
        $field = new xmldb_field('autopopulate', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('autoupdate', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, '1');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('checklist_item');
        $field = new xmldb_field('moduleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table->add_index('item_module', XMLDB_INDEX_NOTUNIQUE, array('moduleid'));

        upgrade_mod_savepoint($result, 2010112000, 'checklist');
    }

    if ($result && $oldversion < 2010113000) {
        $table = new xmldb_table('checklist');
        $field = new xmldb_field('completionpercent', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint($result, 2010113000, 'checklist');
    }

    return $result;

}

?>
