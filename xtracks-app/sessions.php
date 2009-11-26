<?php

class SessionManager
{
   var $life_time;
   
   private $dbn;

   public function __construct()
   {
   	  global $mode;
      $this->dbn = $mode == 'single' ? $dbname : 'prosper_master';
	   //$session_maxlifetime = get_cfg_var("session.gc_maxlifetime");
	   $session_maxlifetime = 43200;

	  // Read the maxlifetime setting from PHP
	  $this->life_time = $session_maxlifetime;

	  // Register this object as the session handler
	  session_set_save_handler(
		array( &$this, "open" ),
		array( &$this, "close" ),
		array( &$this, "read" ),
		array( &$this, "write"),
		array( &$this, "destroy"),
		array( &$this, "gc" )
	  );

   }

   function open( $save_path, $session_name ) {

	  global $sess_save_path;

	  $sess_save_path = $save_path;

	  // Don't need to do anything. Just return TRUE.
	  return true;
   }

   function close() {
	  return true;

   }

   function read( $id )
   {
	  // Set empty result
	  $data = '';

	  // Fetch session data from the selected database
	  $time = time();
      
	  $newid = db::escape($id);
	  $sql = "SELECT `session_data` FROM {$this->dbn}.`202_sessions` WHERE `session_id` = '$newid' AND `expires` > $time";
	  $row = db::getRow($sql);

	  if($row) {
		$data = $row['session_data'];
	  }

	  return $data;
   }

   function write( $id, $data )
   {
	  // Build query
	  $time = time() + $this->life_time;

	  $newid = db::escape($id);
	  $newdata = db::escape($data);

	  $sql = "REPLACE {$this->dbn}.`202_sessions` (`session_id`,`session_data`,`expires`) VALUES('$newid', '$newdata', $time)";

	  $rs = db::execute($sql);

	  return TRUE;
   }

   function destroy( $id )
   {
	  // Build query
	  $newid = db::escape($id);
	  $sql = "DELETE FROM {$this->dbn}.`202_sessions` WHERE `session_id` = '$newid'";
	  db::execute($sql);
	  return TRUE;
   }

   function gc()
   {
	  // Build DELETE query.  Delete all records who have passedthe expiration time
	  $sql = 'DELETE FROM {$this->dbn}.`202_sessions` WHERE `expires` < UNIX_TIMESTAMP();';

	  db::execute($sql);

	  // Always return TRUE
	  return true;
   }
}
